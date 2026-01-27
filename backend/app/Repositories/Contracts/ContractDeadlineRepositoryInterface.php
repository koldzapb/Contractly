<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\DeadlineType;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ContractDeadlineRepositoryInterface
{
    /**
     * Find a deadline by ID.
     */
    public function find(string $id): ?ContractDeadline;

    /**
     * Get all deadlines for an analysis.
     */
    public function getForAnalysis(ContractAnalysis|string $analysis): Collection;

    /**
     * Get deadlines by type for an analysis.
     */
    public function getByTypeForAnalysis(DeadlineType $type, ContractAnalysis|string $analysis): Collection;

    /**
     * Get upcoming deadlines (within specified days).
     */
    public function getUpcoming(int $days = 30): Collection;

    /**
     * Get upcoming deadlines for a user.
     */
    public function getUpcomingForUser(User|int $user, int $days = 30): Collection;

    /**
     * Get past deadlines.
     */
    public function getPast(): Collection;

    /**
     * Get past deadlines for a user.
     */
    public function getPastForUser(User|int $user): Collection;

    /**
     * Get overdue deadlines (past but within specified days).
     */
    public function getOverdue(int $daysBack = 30): Collection;

    /**
     * Get overdue deadlines for a user.
     */
    public function getOverdueForUser(User|int $user, int $daysBack = 30): Collection;

    /**
     * Create a new deadline.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ContractDeadline;

    /**
     * Create multiple deadlines.
     *
     * @param array<int, array<string, mixed>> $deadlines
     *
     * @return Collection<int, ContractDeadline>
     */
    public function createMany(array $deadlines): Collection;

    /**
     * Delete a deadline.
     */
    public function delete(ContractDeadline $deadline): bool;
}
