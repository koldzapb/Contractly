<?php

declare(strict_types=1);

use App\Enums\ClauseType;
use App\Enums\DeadlineType;
use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('compare contracts', function () {
    it('compares two completed contracts successfully', function () {
        // Create first contract with analysis
        $contractA = Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Contract A',
            'overall_risk_level' => RiskLevel::MEDIUM,
        ]);
        $analysisA = ContractAnalysis::factory()->for($contractA)->create();
        ContractClause::factory()->for($analysisA, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 30 days of invoice.',
            'risk_level' => RiskLevel::LOW,
        ]);
        ContractDeadline::factory()->for($analysisA, 'analysis')->create([
            'deadline_type' => DeadlineType::PAYMENT,
            'title' => 'Monthly Payment Due',
        ]);

        // Create second contract with analysis
        $contractB = Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Contract B',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        $analysisB = ContractAnalysis::factory()->for($contractB)->create();
        ContractClause::factory()->for($analysisB, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 45 days of invoice.',
            'risk_level' => RiskLevel::MEDIUM,
        ]);
        ContractDeadline::factory()->for($analysisB, 'analysis')->create([
            'deadline_type' => DeadlineType::PAYMENT,
            'title' => 'Monthly Payment Due',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'contract_a' => ['id', 'title', 'overall_risk_level'],
                    'contract_b' => ['id', 'title', 'overall_risk_level'],
                    'similarity_score',
                    'risk_comparison' => ['contract_a', 'contract_b', 'changed'],
                    'clauses' => ['matched', 'only_in_a', 'only_in_b'],
                    'deadlines' => ['matched', 'only_in_a', 'only_in_b'],
                    'stats',
                ],
            ])
            ->assertJsonPath('data.contract_a.id', $contractA->id)
            ->assertJsonPath('data.contract_b.id', $contractB->id)
            ->assertJsonPath('data.risk_comparison.changed', true);
    });

    it('requires both contract IDs', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contract_id_a', 'contract_id_b']);
    });

    it('requires valid UUIDs', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => 'not-a-uuid',
                'contract_id_b' => 'also-not-a-uuid',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contract_id_a', 'contract_id_b']);
    });

    it('prevents comparing a contract with itself', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        ContractAnalysis::factory()->for($contract)->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contract->id,
                'contract_id_b' => $contract->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contract_id_b']);
    });

    it('returns 422 if contracts are not found', function () {
        $contractA = Contract::factory()->for($this->user)->completed()->create();
        ContractAnalysis::factory()->for($contractA)->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => fake()->uuid(),
            ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'One or both contracts not found.',
            ]);
    });

    it('returns 422 if contracts belong to different users', function () {
        $otherUser = User::factory()->create();

        $contractA = Contract::factory()->for($this->user)->completed()->create();
        ContractAnalysis::factory()->for($contractA)->create();

        $contractB = Contract::factory()->for($otherUser)->completed()->create();
        ContractAnalysis::factory()->for($contractB)->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'One or both contracts not found.',
            ]);
    });

    it('returns 422 if contracts are not completed', function () {
        $contractA = Contract::factory()->for($this->user)->completed()->create();
        ContractAnalysis::factory()->for($contractA)->create();

        $contractB = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Both contracts must be fully analyzed.',
            ]);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/contracts/compare', [
            'contract_id_a' => fake()->uuid(),
            'contract_id_b' => fake()->uuid(),
        ]);

        $response->assertStatus(401);
    });

    it('correctly categorizes matched and unmatched clauses', function () {
        // Contract A: Payment + Termination clauses
        $contractA = Contract::factory()->for($this->user)->completed()->create();
        $analysisA = ContractAnalysis::factory()->for($contractA)->create();
        ContractClause::factory()->for($analysisA, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment terms: Net 30 days.',
        ]);
        ContractClause::factory()->for($analysisA, 'analysis')->create([
            'clause_type' => ClauseType::TERMINATION,
            'original_text' => 'Either party may terminate with 30 days notice.',
        ]);

        // Contract B: Payment + Liability clauses (different type)
        $contractB = Contract::factory()->for($this->user)->completed()->create();
        $analysisB = ContractAnalysis::factory()->for($contractB)->create();
        ContractClause::factory()->for($analysisB, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment terms: Net 30 days.',
        ]);
        ContractClause::factory()->for($analysisB, 'analysis')->create([
            'clause_type' => ClauseType::LIABILITY,
            'original_text' => 'Liability is limited to the contract value.',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertOk();

        $data = $response->json('data');

        // Payment clause should match
        expect($data['clauses']['matched'])->toHaveCount(1);
        expect($data['clauses']['matched'][0]['clause_a']['clause_type'])->toBe('payment');

        // Termination only in A
        expect($data['clauses']['only_in_a'])->toHaveCount(1);
        expect($data['clauses']['only_in_a'][0]['clause_a']['clause_type'])->toBe('termination');

        // Liability only in B
        expect($data['clauses']['only_in_b'])->toHaveCount(1);
        expect($data['clauses']['only_in_b'][0]['clause_b']['clause_type'])->toBe('liability');

        // Verify stats
        expect($data['stats']['total_clauses_a'])->toBe(2);
        expect($data['stats']['total_clauses_b'])->toBe(2);
        expect($data['stats']['matched_clauses'])->toBe(1);
        expect($data['stats']['clauses_only_in_a'])->toBe(1);
        expect($data['stats']['clauses_only_in_b'])->toBe(1);
    });

    it('detects risk level differences in matched clauses', function () {
        $contractA = Contract::factory()->for($this->user)->completed()->create();
        $analysisA = ContractAnalysis::factory()->for($contractA)->create();
        ContractClause::factory()->for($analysisA, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 30 days.',
            'risk_level' => RiskLevel::LOW,
        ]);

        $contractB = Contract::factory()->for($this->user)->completed()->create();
        $analysisB = ContractAnalysis::factory()->for($contractB)->create();
        ContractClause::factory()->for($analysisB, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 30 days.',
            'risk_level' => RiskLevel::HIGH,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertOk();

        $matched = $response->json('data.clauses.matched.0');
        expect($matched['has_risk_difference'])->toBeTrue();
        expect($matched['clause_a']['risk_level'])->toBe('low');
        expect($matched['clause_b']['risk_level'])->toBe('high');
    });

    it('calculates similarity score', function () {
        // Create two identical contracts
        $contractA = Contract::factory()->for($this->user)->completed()->create();
        $analysisA = ContractAnalysis::factory()->for($contractA)->create();
        ContractClause::factory()->for($analysisA, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 30 days of invoice.',
        ]);

        $contractB = Contract::factory()->for($this->user)->completed()->create();
        $analysisB = ContractAnalysis::factory()->for($contractB)->create();
        ContractClause::factory()->for($analysisB, 'analysis')->create([
            'clause_type' => ClauseType::PAYMENT,
            'original_text' => 'Payment shall be made within 30 days of invoice.',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts/compare', [
                'contract_id_a' => $contractA->id,
                'contract_id_b' => $contractB->id,
            ]);

        $response->assertOk();

        // Identical clauses should have high similarity
        expect($response->json('data.similarity_score'))->toBeGreaterThan(0.9);
    });
});
