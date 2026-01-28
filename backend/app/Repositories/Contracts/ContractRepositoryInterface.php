<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ContractRepositoryInterface
{
    /**
     * Find a contract by ID.
     */
    public function find(string $id): ?Contract;

    /**
     * Find a contract by ID or throw exception.
     */
    public function findOrFail(string $id): Contract;

    /**
     * Find a contract by ID for a specific user.
     */
    public function findForUser(string $id, User|int $user): ?Contract;

    /**
     * Find a contract by ID with analysis relationship loaded.
     */
    public function findWithAnalysis(string $id): ?Contract;

    /**
     * Find a contract with all relationships loaded.
     */
    public function findWithAllRelations(string $id): ?Contract;

    /**
     * Get all contracts for a user.
     */
    public function getAllForUser(User|int $user): Collection;

    /**
     * Get contracts for a user filtered by status.
     */
    public function getByStatusForUser(ContractStatus $status, User|int $user): Collection;

    /**
     * Paginate contracts for a user.
     */
    public function paginateForUser(User|int $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new contract.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Contract;

    /**
     * Update a contract.
     *
     * @param array<string, mixed> $data
     */
    public function update(Contract $contract, array $data): bool;

    /**
     * Delete a contract (soft delete).
     */
    public function delete(Contract $contract): bool;

    /**
     * Force delete a contract.
     */
    public function forceDelete(Contract $contract): bool;

    /**
     * Get contracts by status.
     */
    public function getByStatus(ContractStatus $status): Collection;

    /**
     * Count contracts by status for a user.
     *
     * @return array<string, int>
     */
    public function countByStatusForUser(User|int $user): array;

    /**
     * Search contracts for a user by title or filename.
     */
    public function searchForUser(string $query, User|int $user): Collection;

    /**
     * Get recent contracts for a user.
     */
    public function getRecentForUser(User|int $user, int $limit = 5): Collection;
}
