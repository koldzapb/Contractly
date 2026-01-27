<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('view contract', function () {
    it('returns a single contract', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'original_filename',
                    'file_size',
                    'file_size_human',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $contract->id);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts/non-existent-id');

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Contract not found.',
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}");

        $response->assertNotFound();
    });

    it('includes analysis when requested', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(2)->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->count(2)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}?with_analysis=true");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'analysis' => [
                        'id',
                        'summary',
                        'overall_risk_level',
                        'clauses' => [
                            '*' => ['id', 'clause_type', 'risk_level'],
                        ],
                        'deadlines' => [
                            '*' => ['id', 'deadline_type', 'title'],
                        ],
                    ],
                ],
            ]);
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->getJson("/api/contracts/{$contract->id}");

        $response->assertStatus(401);
    });
});

describe('contract status', function () {
    it('returns contract status', function () {
        $contract = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/status");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $contract->id,
                    'status' => 'pending',
                ],
            ]);
    });

    it('includes error message for failed contracts', function () {
        $contract = Contract::factory()->for($this->user)->failed()->create([
            'error_message' => 'Analysis failed due to API error',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/status");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'failed',
                    'error_message' => 'Analysis failed due to API error',
                ],
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/status");

        $response->assertNotFound();
    });
});
