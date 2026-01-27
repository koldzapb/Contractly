<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use Illuminate\Database\Eloquent\Collection;

interface ContractAnalysisRepositoryInterface
{
    /**
     * Find an analysis by ID.
     */
    public function find(string $id): ?ContractAnalysis;

    /**
     * Find an analysis by contract ID.
     */
    public function findByContract(Contract|string $contract): ?ContractAnalysis;

    /**
     * Find an analysis with clauses loaded.
     */
    public function findWithClauses(string $id): ?ContractAnalysis;

    /**
     * Find an analysis with all relationships.
     */
    public function findWithAllRelations(string $id): ?ContractAnalysis;

    /**
     * Create a new analysis.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ContractAnalysis;

    /**
     * Update an analysis.
     *
     * @param array<string, mixed> $data
     */
    public function update(ContractAnalysis $analysis, array $data): bool;

    /**
     * Delete an analysis.
     */
    public function delete(ContractAnalysis $analysis): bool;

    /**
     * Get analyses by risk level.
     */
    public function getByRiskLevel(RiskLevel $riskLevel): Collection;
}
