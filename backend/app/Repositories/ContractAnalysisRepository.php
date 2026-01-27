<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Repositories\Contracts\ContractAnalysisRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ContractAnalysis>
 */
class ContractAnalysisRepository extends BaseRepository implements ContractAnalysisRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ContractAnalysis::class);
    }

    public function find(string $id): ?ContractAnalysis
    {
        return $this->query()->find($id);
    }

    public function findByContract(Contract|string $contract): ?ContractAnalysis
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->first();
    }

    public function findWithClauses(string $id): ?ContractAnalysis
    {
        return $this->query()
            ->with('clauses')
            ->find($id);
    }

    public function findWithAllRelations(string $id): ?ContractAnalysis
    {
        return $this->query()
            ->with(['contract', 'clauses', 'deadlines'])
            ->find($id);
    }

    public function create(array $data): ContractAnalysis
    {
        return $this->query()->create($data);
    }

    public function update(ContractAnalysis $analysis, array $data): bool
    {
        return $analysis->update($data);
    }

    public function delete(ContractAnalysis $analysis): bool
    {
        return (bool) $analysis->delete();
    }

    public function getByRiskLevel(RiskLevel $riskLevel): Collection
    {
        return $this->query()
            ->where('overall_risk_level', $riskLevel)
            ->orderByDesc('created_at')
            ->get();
    }
}
