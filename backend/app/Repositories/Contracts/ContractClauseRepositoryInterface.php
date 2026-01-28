<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\ClauseType;
use App\Enums\RiskLevel;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use Illuminate\Database\Eloquent\Collection;

interface ContractClauseRepositoryInterface
{
    /**
     * Find a clause by ID.
     */
    public function find(string $id): ?ContractClause;

    /**
     * Get all clauses for an analysis.
     */
    public function getForAnalysis(ContractAnalysis|string $analysis): Collection;

    /**
     * Get clauses by type for an analysis.
     */
    public function getByTypeForAnalysis(ClauseType $type, ContractAnalysis|string $analysis): Collection;

    /**
     * Get clauses by risk level for an analysis.
     */
    public function getByRiskLevelForAnalysis(RiskLevel $riskLevel, ContractAnalysis|string $analysis): Collection;

    /**
     * Get all clauses with risk (not NONE) for an analysis.
     */
    public function getWithRiskForAnalysis(ContractAnalysis|string $analysis): Collection;

    /**
     * Get high-risk clauses for an analysis.
     */
    public function getHighRiskForAnalysis(ContractAnalysis|string $analysis): Collection;

    /**
     * Create a new clause.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ContractClause;

    /**
     * Create multiple clauses.
     *
     * @param array<int, array<string, mixed>> $clauses
     *
     * @return Collection<int, ContractClause>
     */
    public function createMany(array $clauses): Collection;

    /**
     * Delete a clause.
     */
    public function delete(ContractClause $clause): bool;

    /**
     * Count clauses by risk level for an analysis.
     *
     * @return array<string, int>
     */
    public function countByRiskLevelForAnalysis(ContractAnalysis|string $analysis): array;

    /**
     * Count high-risk clauses across all contracts for a user.
     */
    public function countHighRiskClausesForUser(\App\Models\User|int $user): int;
}
