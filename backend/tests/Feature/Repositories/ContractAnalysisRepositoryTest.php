<?php

declare(strict_types=1);

use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;
use App\Repositories\Contracts\ContractAnalysisRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ContractAnalysisRepositoryInterface::class);
    $this->user = User::factory()->create();
});

describe('find', function () {
    it('finds an analysis by id', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        $found = $this->repository->find($analysis->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($analysis->id);
    });
});

describe('findByContract', function () {
    it('finds analysis by contract', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        $found = $this->repository->findByContract($contract);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($analysis->id);
    });

    it('accepts contract id as string', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        $found = $this->repository->findByContract($contract->id);

        expect($found)->not->toBeNull();
    });

    it('returns null when no analysis exists', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $found = $this->repository->findByContract($contract);

        expect($found)->toBeNull();
    });
});

describe('findWithClauses', function () {
    it('loads clauses relationship', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(3)->create();

        $found = $this->repository->findWithClauses($analysis->id);

        expect($found)->not->toBeNull()
            ->and($found->relationLoaded('clauses'))->toBeTrue()
            ->and($found->clauses)->toHaveCount(3);
    });
});

describe('findWithAllRelations', function () {
    it('loads all relationships', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(2)->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->count(2)->create();

        $found = $this->repository->findWithAllRelations($analysis->id);

        expect($found)->not->toBeNull()
            ->and($found->relationLoaded('clauses'))->toBeTrue()
            ->and($found->relationLoaded('deadlines'))->toBeTrue()
            ->and($found->relationLoaded('contract'))->toBeTrue();
    });
});

describe('getByRiskLevel', function () {
    it('returns analyses filtered by risk level', function () {
        $contract1 = Contract::factory()->for($this->user)->create();
        $contract2 = Contract::factory()->for($this->user)->create();
        $contract3 = Contract::factory()->for($this->user)->create();

        ContractAnalysis::factory()->for($contract1)->highRisk()->create();
        ContractAnalysis::factory()->for($contract2)->highRisk()->create();
        ContractAnalysis::factory()->for($contract3)->lowRisk()->create();

        $highRisk = $this->repository->getByRiskLevel(RiskLevel::HIGH);
        $lowRisk = $this->repository->getByRiskLevel(RiskLevel::LOW);

        expect($highRisk)->toHaveCount(2)
            ->and($lowRisk)->toHaveCount(1);
    });
});

describe('create', function () {
    it('creates a new analysis', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $data = [
            'contract_id' => $contract->id,
            'summary' => 'Test summary',
            'overall_risk_level' => RiskLevel::MEDIUM,
            'key_findings' => ['finding 1', 'finding 2'],
            'ai_model' => 'claude-3-5-sonnet',
            'tokens_used' => 5000,
            'processing_time_ms' => 10000,
        ];

        $analysis = $this->repository->create($data);

        expect($analysis)->toBeInstanceOf(ContractAnalysis::class)
            ->and($analysis->summary)->toBe('Test summary')
            ->and($analysis->overall_risk_level)->toBe(RiskLevel::MEDIUM);
    });
});

describe('update', function () {
    it('updates an analysis', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create([
            'summary' => 'Original summary',
        ]);

        $result = $this->repository->update($analysis, ['summary' => 'Updated summary']);

        expect($result)->toBeTrue()
            ->and($analysis->fresh()->summary)->toBe('Updated summary');
    });
});

describe('delete', function () {
    it('deletes an analysis', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        $result = $this->repository->delete($analysis);

        expect($result)->toBeTrue()
            ->and(ContractAnalysis::find($analysis->id))->toBeNull();
    });
});
