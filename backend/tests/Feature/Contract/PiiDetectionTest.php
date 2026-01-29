<?php

declare(strict_types=1);

use App\Enums\PiiType;
use App\Models\Contract;
use App\Models\User;
use App\Services\PiiDetectionService;

describe('get pii endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('returns detected pii for a contract', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => [
                'has_pii' => true,
                'total_count' => 2,
                'counts_by_type' => [
                    'ssn' => 1,
                    'email' => 1,
                    'phone' => 0,
                    'credit_card' => 0,
                    'bank_routing' => 0,
                    'bank_account' => 0,
                ],
                'items' => [
                    [
                        'id' => 'item-1',
                        'type' => 'ssn',
                        'type_label' => 'Social Security Number',
                        'value' => '123-45-6789',
                        'redacted_value' => '[REDACTED_SSN]',
                        'start_position' => 100,
                        'end_position' => 111,
                        'context' => '...SSN: 123-45-6789...',
                        'selected' => true,
                    ],
                    [
                        'id' => 'item-2',
                        'type' => 'email',
                        'type_label' => 'Email Address',
                        'value' => 'test@example.com',
                        'redacted_value' => '[REDACTED_EMAIL]',
                        'start_position' => 200,
                        'end_position' => 216,
                        'context' => '...email: test@example.com...',
                        'selected' => true,
                    ],
                ],
                'extracted_text' => 'Sample text with PII',
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/pii");

        $response->assertOk()
            ->assertJsonPath('data.has_pii', true)
            ->assertJsonPath('data.total_count', 2)
            ->assertJsonCount(2, 'data.items');
    });

    it('returns empty when no pii detected', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/pii");

        $response->assertOk()
            ->assertJsonPath('data.has_pii', false)
            ->assertJsonPath('data.total_count', 0);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts/non-existent-id/pii');

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->create();

        $response = $this->getJson("/api/contracts/{$contract->id}/pii");

        $response->assertUnauthorized();
    });
});

describe('apply redactions endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('applies redactions to selected items', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => [
                'has_pii' => true,
                'total_count' => 1,
                'counts_by_type' => [
                    'ssn' => 1,
                    'email' => 0,
                    'phone' => 0,
                    'credit_card' => 0,
                    'bank_routing' => 0,
                    'bank_account' => 0,
                ],
                'items' => [
                    [
                        'id' => '550e8400-e29b-41d4-a716-446655440000',
                        'type' => 'ssn',
                        'type_label' => 'Social Security Number',
                        'value' => '123-45-6789',
                        'redacted_value' => '[REDACTED_SSN]',
                        'start_position' => 100,
                        'end_position' => 111,
                        'context' => '...SSN: 123-45-6789...',
                        'selected' => true,
                    ],
                ],
                'extracted_text' => 'Sample text with SSN: 123-45-6789',
            ],
            'has_redactions' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/redact", [
                'item_ids' => ['550e8400-e29b-41d4-a716-446655440000'],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.has_redactions', true);

        $contract->refresh();
        expect($contract->has_redactions)->toBeTrue();
    });

    it('validates item_ids are required', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => ['has_pii' => true, 'total_count' => 1, 'items' => [], 'counts_by_type' => [], 'extracted_text' => ''],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/redact", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_ids']);
    });

    it('returns 422 when no pii detected', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/redact", [
                'item_ids' => ['550e8400-e29b-41d4-a716-446655440000'],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No PII detected in this contract.');
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->create();

        $response = $this->postJson("/api/contracts/{$contract->id}/redact", [
            'item_ids' => ['item-1'],
        ]);

        $response->assertUnauthorized();
    });
});

describe('skip redaction endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('clears pii detection when skipped', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'pii_detection' => [
                'has_pii' => true,
                'total_count' => 1,
                'counts_by_type' => [],
                'items' => [],
                'extracted_text' => 'text',
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/skip-redaction");

        $response->assertOk();

        $contract->refresh();
        expect($contract->pii_detection)->toBeNull();
        expect($contract->has_redactions)->toBeFalse();
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/non-existent-id/skip-redaction');

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->create();

        $response = $this->postJson("/api/contracts/{$contract->id}/skip-redaction");

        $response->assertUnauthorized();
    });
});

describe('pii detection service', function () {
    it('detects ssn in text', function () {
        $service = new PiiDetectionService;

        $result = $service->detectPii('My SSN is 123-45-6789 please process.');

        expect($result->hasPii)->toBeTrue();
        expect($result->totalCount)->toBeGreaterThanOrEqual(1);
        expect($result->countsByType['ssn'])->toBeGreaterThanOrEqual(1);
    });

    it('detects email in text', function () {
        $service = new PiiDetectionService;

        $result = $service->detectPii('Contact me at john.doe@example.com for details.');

        expect($result->hasPii)->toBeTrue();
        expect($result->countsByType['email'])->toBe(1);

        $emailItem = collect($result->items)->first(fn ($item) => $item->type === PiiType::EMAIL);
        expect($emailItem)->not->toBeNull();
        expect($emailItem->value)->toBe('john.doe@example.com');
    });

    it('detects phone numbers', function () {
        $service = new PiiDetectionService;

        $result = $service->detectPii('Call me at (555) 123-4567.');

        expect($result->hasPii)->toBeTrue();
        expect($result->countsByType['phone'])->toBe(1);
    });

    it('validates credit card with luhn algorithm', function () {
        $service = new PiiDetectionService;

        // Valid Visa test number
        $result = $service->detectPii('Card: 4111-1111-1111-1111');

        expect($result->hasPii)->toBeTrue();
        expect($result->countsByType['credit_card'])->toBe(1);
    });

    it('returns empty result for text without pii', function () {
        $service = new PiiDetectionService;

        $result = $service->detectPii('This is a normal contract about services.');

        expect($result->hasPii)->toBeFalse();
        expect($result->totalCount)->toBe(0);
    });

    it('applies redactions correctly', function () {
        $service = new PiiDetectionService;

        $text = 'Email: test@example.com';
        $result = $service->detectPii($text);

        $itemIds = array_map(fn ($item) => $item->id, $result->items);
        $redacted = $service->applyRedactions($result, $itemIds);

        expect($redacted)->toContain('[REDACTED_EMAIL]');
        expect($redacted)->not->toContain('test@example.com');
    });

    it('removes overlapping detections keeping higher sensitivity', function () {
        $service = new PiiDetectionService;

        // A 9-digit number could match both bank routing and SSN patterns
        // The service should keep only the higher sensitivity one
        $result = $service->detectPii('routing number: 123456789');

        // Should have at most one detection for this number
        $detectedPositions = array_map(
            fn ($item) => [$item->startPosition, $item->endPosition],
            $result->items,
        );

        // Check no overlapping ranges
        for ($i = 0; $i < count($detectedPositions); $i++) {
            for ($j = $i + 1; $j < count($detectedPositions); $j++) {
                $overlaps = $detectedPositions[$i][0] < $detectedPositions[$j][1] &&
                           $detectedPositions[$i][1] > $detectedPositions[$j][0];
                expect($overlaps)->toBeFalse();
            }
        }
    });
});
