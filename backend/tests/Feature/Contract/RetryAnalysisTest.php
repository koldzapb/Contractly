<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Jobs\AnalyzeContractJob;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create();
});

describe('retry analysis', function () {
    it('retries analysis for failed contracts', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::FAILED,
            'error_message' => 'Previous error',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertOk()
            ->assertJson([
                'message' => 'Analysis retry started.',
                'data' => [
                    'id' => $contract->id,
                    'status' => 'pending',
                ],
            ]);

        // Check contract was updated
        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::PENDING);
        expect($contract->error_message)->toBeNull();

        // Check job was dispatched
        Queue::assertPushed(AnalyzeContractJob::class, function ($job) use ($contract) {
            return $job->contract->id === $contract->id;
        });
    });

    it('deletes existing analysis data before retry', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::FAILED,
        ]);

        $analysis = ContractAnalysis::factory()->create([
            'contract_id' => $contract->id,
        ]);

        ContractClause::factory()->count(3)->create([
            'contract_analysis_id' => $analysis->id,
        ]);

        ContractDeadline::factory()->count(2)->create([
            'contract_analysis_id' => $analysis->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertOk();

        // Check old data was deleted
        $this->assertDatabaseMissing('contract_analyses', ['id' => $analysis->id]);
        $this->assertDatabaseCount('contract_clauses', 0);
        $this->assertDatabaseCount('contract_deadlines', 0);
    });

    it('rejects retry for pending contracts', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only failed contracts can be retried.',
            ]);

        Queue::assertNotPushed(AnalyzeContractJob::class);
    });

    it('rejects retry for processing contracts', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PROCESSING,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertStatus(422);
        Queue::assertNotPushed(AnalyzeContractJob::class);
    });

    it('rejects retry for completed contracts', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::COMPLETED,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertStatus(422);
        Queue::assertNotPushed(AnalyzeContractJob::class);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/non-existent-id/retry');

        $response->assertStatus(404);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ContractStatus::FAILED,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::FAILED,
        ]);

        $response = $this->postJson("/api/contracts/{$contract->id}/retry");

        $response->assertStatus(401);
    });
});
