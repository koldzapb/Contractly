<?php

declare(strict_types=1);

use App\Enums\ClauseType;
use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\User;
use App\Repositories\Contracts\ContractClauseRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ContractClauseRepositoryInterface::class);
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
    $this->analysis = ContractAnalysis::factory()->for($this->contract)->create();
});

describe('find', function () {
    it('finds a clause by id', function () {
        $clause = ContractClause::factory()->for($this->analysis, 'analysis')->create();

        $found = $this->repository->find($clause->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($clause->id);
    });
});

describe('getForAnalysis', function () {
    it('returns all clauses for an analysis', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->count(5)->create();

        $clauses = $this->repository->getForAnalysis($this->analysis);

        expect($clauses)->toHaveCount(5);
    });

    it('accepts analysis id as string', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->count(3)->create();

        $clauses = $this->repository->getForAnalysis($this->analysis->id);

        expect($clauses)->toHaveCount(3);
    });

    it('does not return clauses from other analyses', function () {
        $otherAnalysis = ContractAnalysis::factory()
            ->for(Contract::factory()->for($this->user))
            ->create();

        ContractClause::factory()->for($this->analysis, 'analysis')->count(2)->create();
        ContractClause::factory()->for($otherAnalysis, 'analysis')->count(3)->create();

        $clauses = $this->repository->getForAnalysis($this->analysis);

        expect($clauses)->toHaveCount(2);
    });
});

describe('getByTypeForAnalysis', function () {
    it('returns clauses filtered by type', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->payment()->count(2)->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->termination()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->liability()->create();

        $payment = $this->repository->getByTypeForAnalysis(ClauseType::PAYMENT, $this->analysis);
        $termination = $this->repository->getByTypeForAnalysis(ClauseType::TERMINATION, $this->analysis);

        expect($payment)->toHaveCount(2)
            ->and($termination)->toHaveCount(1);
    });
});

describe('getByRiskLevelForAnalysis', function () {
    it('returns clauses filtered by risk level', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->highRisk()->count(2)->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->mediumRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->noRisk()->create();

        $high = $this->repository->getByRiskLevelForAnalysis(RiskLevel::HIGH, $this->analysis);
        $medium = $this->repository->getByRiskLevelForAnalysis(RiskLevel::MEDIUM, $this->analysis);

        expect($high)->toHaveCount(2)
            ->and($medium)->toHaveCount(1);
    });
});

describe('getWithRiskForAnalysis', function () {
    it('returns clauses with any risk level except NONE', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->highRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->mediumRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->lowRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->noRisk()->count(2)->create();

        $withRisk = $this->repository->getWithRiskForAnalysis($this->analysis);

        expect($withRisk)->toHaveCount(3);
    });
});

describe('getHighRiskForAnalysis', function () {
    it('returns only high risk clauses', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->highRisk()->count(2)->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->mediumRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->lowRisk()->create();

        $highRisk = $this->repository->getHighRiskForAnalysis($this->analysis);

        expect($highRisk)->toHaveCount(2)
            ->and($highRisk->every(fn ($c) => $c->risk_level === RiskLevel::HIGH))->toBeTrue();
    });
});

describe('createMany', function () {
    it('creates multiple clauses', function () {
        $clauses = [
            [
                'contract_analysis_id' => $this->analysis->id,
                'clause_type' => ClauseType::PAYMENT,
                'original_text' => 'Payment clause text',
                'plain_explanation' => 'Simple explanation',
                'risk_level' => RiskLevel::LOW,
                'position_index' => 1,
            ],
            [
                'contract_analysis_id' => $this->analysis->id,
                'clause_type' => ClauseType::TERMINATION,
                'original_text' => 'Termination clause text',
                'plain_explanation' => 'Simple explanation',
                'risk_level' => RiskLevel::HIGH,
                'risk_reason' => 'Unfavorable terms',
                'position_index' => 2,
            ],
        ];

        $result = $this->repository->createMany($clauses);

        expect($result)->toHaveCount(2)
            ->and(ContractClause::count())->toBe(2);
    });
});

describe('countByRiskLevelForAnalysis', function () {
    it('returns count of clauses grouped by risk level', function () {
        ContractClause::factory()->for($this->analysis, 'analysis')->highRisk()->count(3)->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->mediumRisk()->count(2)->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->lowRisk()->create();
        ContractClause::factory()->for($this->analysis, 'analysis')->noRisk()->count(4)->create();

        $counts = $this->repository->countByRiskLevelForAnalysis($this->analysis);

        expect($counts)->toBeArray()
            ->and($counts['high'])->toBe(3)
            ->and($counts['medium'])->toBe(2)
            ->and($counts['low'])->toBe(1)
            ->and($counts['none'])->toBe(4);
    });
});

describe('delete', function () {
    it('deletes a clause', function () {
        $clause = ContractClause::factory()->for($this->analysis, 'analysis')->create();

        $result = $this->repository->delete($clause);

        expect($result)->toBeTrue()
            ->and(ContractClause::find($clause->id))->toBeNull();
    });
});
