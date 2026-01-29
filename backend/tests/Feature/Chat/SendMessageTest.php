<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\User;
use App\Services\ContractChatService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()
        ->for($this->user)
        ->create(['status' => ContractStatus::COMPLETED]);
    $this->analysis = ContractAnalysis::factory()
        ->for($this->contract)
        ->create();
});

describe('send message', function () {
    it('sends a message and returns assistant response', function () {
        // Mock the chat service
        $mockService = Mockery::mock(ContractChatService::class);
        $mockService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(
                App\Models\ChatMessage::factory()
                    ->for($this->contract)
                    ->for($this->user)
                    ->assistant()
                    ->create([
                        'content' => 'The payment terms specify monthly payments.',
                        'tokens_used' => 150,
                    ]),
            );

        $this->app->instance(ContractChatService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$this->contract->id}/chat", [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'role',
                    'content',
                    'tokens_used',
                ],
            ])
            ->assertJsonPath('data.role', 'assistant');
    });

    it('validates message is required', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$this->contract->id}/chat", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('validates message is not empty', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$this->contract->id}/chat", [
                'message' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('validates message max length', function () {
        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$this->contract->id}/chat", [
                'message' => str_repeat('a', 5001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('rejects chat for incomplete contract analysis', function () {
        $pendingContract = Contract::factory()
            ->for($this->user)
            ->create(['status' => ContractStatus::PENDING]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$pendingContract->id}/chat", [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'ANALYSIS_NOT_COMPLETE');
    });

    it('rejects chat for processing contract', function () {
        $processingContract = Contract::factory()
            ->for($this->user)
            ->create(['status' => ContractStatus::PROCESSING]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$processingContract->id}/chat", [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'ANALYSIS_NOT_COMPLETE');
    });

    it('rejects chat for failed contract', function () {
        $failedContract = Contract::factory()
            ->for($this->user)
            ->create(['status' => ContractStatus::FAILED]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$failedContract->id}/chat", [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'ANALYSIS_NOT_COMPLETE');
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/non-existent-id/chat', [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertStatus(404);
    });

    it('returns 404 for contract belonging to other user', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()
            ->for($otherUser)
            ->create(['status' => ContractStatus::COMPLETED]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$otherContract->id}/chat", [
                'message' => 'What are the payment terms?',
            ]);

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->postJson("/api/contracts/{$this->contract->id}/chat", [
            'message' => 'What are the payment terms?',
        ]);

        $response->assertStatus(401);
    });
});
