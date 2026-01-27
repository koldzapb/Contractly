<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ClauseType;
use App\Enums\RiskLevel;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Repositories\Contracts\ContractClauseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ContractClause>
 */
class ContractClauseRepository extends BaseRepository implements ContractClauseRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ContractClause::class);
    }

    public function find(string $id): ?ContractClause
    {
        return $this->query()->find($id);
    }

    public function getForAnalysis(ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->orderBy('position_index')
            ->get();
    }

    public function getByTypeForAnalysis(ClauseType $type, ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->where('clause_type', $type)
            ->orderBy('position_index')
            ->get();
    }

    public function getByRiskLevelForAnalysis(RiskLevel $riskLevel, ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->where('risk_level', $riskLevel)
            ->orderBy('position_index')
            ->get();
    }

    public function getWithRiskForAnalysis(ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->where('risk_level', '!=', RiskLevel::NONE)
            ->orderBy('position_index')
            ->get();
    }

    public function getHighRiskForAnalysis(ContractAnalysis|string $analysis): Collection
    {
        return $this->getByRiskLevelForAnalysis(RiskLevel::HIGH, $analysis);
    }

    public function create(array $data): ContractClause
    {
        return $this->query()->create($data);
    }

    public function createMany(array $clauses): Collection
    {
        $created = new Collection();

        foreach ($clauses as $clauseData) {
            $created->push($this->create($clauseData));
        }

        return $created;
    }

    public function delete(ContractClause $clause): bool
    {
        return (bool) $clause->delete();
    }

    public function countByRiskLevelForAnalysis(ContractAnalysis|string $analysis): array
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->selectRaw('risk_level, count(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level')
            ->toArray();
    }
}
