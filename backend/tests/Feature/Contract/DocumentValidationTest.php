<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Enums\DocumentCategory;
use App\Enums\DocumentType;
use App\Jobs\AnalyzeContractJob;
use App\Models\Contract;
use App\Models\User;
use App\Services\DocumentClassificationResult;
use Illuminate\Support\Facades\Queue;

describe('analyze anyway endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        Queue::fake();
    });

    it('allows override for rejected documents', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::FAILED,
            'document_classification' => DocumentClassificationResult::rejected(
                DocumentType::INVOICE,
                'This appears to be an invoice.',
                0.9,
            )->toArray(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/analyze-anyway");

        $response->assertOk()
            ->assertJsonPath('data.document_classification.analyzed_with_override', true);

        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::PENDING);
        expect($contract->document_classification['analyzed_with_override'])->toBeTrue();

        Queue::assertPushed(AnalyzeContractJob::class, function ($job) use ($contract) {
            return $job->contract->id === $contract->id;
        });
    });

    it('returns 422 when document has no classification', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'document_classification' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/analyze-anyway");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Document has not been classified yet.');
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/non-existent-id/analyze-anyway');

        $response->assertNotFound();
    });

    it('returns 404 for contract belonging to other user', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/analyze-anyway");

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->create();

        $response = $this->postJson("/api/contracts/{$contract->id}/analyze-anyway");

        $response->assertUnauthorized();
    });
});

describe('document classification result', function () {
    it('creates legal document classification', function () {
        $result = DocumentClassificationResult::legal(DocumentType::CONTRACT, 0.95);

        expect($result->isLegalDocument)->toBeTrue();
        expect($result->documentType)->toBe(DocumentType::CONTRACT);
        expect($result->category)->toBe(DocumentCategory::LEGAL);
        expect($result->confidence)->toBe(0.95);
        expect($result->canAnalyze())->toBeTrue();
    });

    it('creates rejected document classification', function () {
        $result = DocumentClassificationResult::rejected(
            DocumentType::INVOICE,
            'This is an invoice, not a contract.',
            0.85,
        );

        expect($result->isLegalDocument)->toBeFalse();
        expect($result->documentType)->toBe(DocumentType::INVOICE);
        expect($result->category)->toBe(DocumentCategory::NON_LEGAL);
        expect($result->rejectionReason)->toContain('invoice');
        expect($result->canAnalyze())->toBeFalse();
    });

    it('can be overridden', function () {
        $result = DocumentClassificationResult::rejected(
            DocumentType::INVOICE,
            'This is an invoice.',
            0.85,
        );

        expect($result->canAnalyze())->toBeFalse();

        $overridden = $result->withOverride();

        expect($overridden->analyzedWithOverride)->toBeTrue();
        expect($overridden->canAnalyze())->toBeTrue();
    });

    it('serializes to and from array', function () {
        $original = DocumentClassificationResult::legal(DocumentType::NDA, 0.92);

        $array = $original->toArray();
        $restored = DocumentClassificationResult::fromArray($array);

        expect($restored->isLegalDocument)->toBe($original->isLegalDocument);
        expect($restored->documentType)->toBe($original->documentType);
        expect($restored->category)->toBe($original->category);
        expect($restored->confidence)->toBe($original->confidence);
    });

    it('includes warnings for pre-contractual documents', function () {
        $result = DocumentClassificationResult::legal(DocumentType::MOU, 0.9);

        expect($result->category)->toBe(DocumentCategory::PRE_CONTRACTUAL);
        expect($result->warnings)->not->toBeEmpty();
    });
});
