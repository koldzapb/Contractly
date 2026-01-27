<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReminderRepositoryInterface
{
    /**
     * Find a reminder by ID.
     */
    public function find(string $id): ?Reminder;

    /**
     * Find a reminder by ID for a specific user.
     */
    public function findForUser(string $id, User|int $user): ?Reminder;

    /**
     * Get all reminders for a user.
     */
    public function getAllForUser(User|int $user): Collection;

    /**
     * Get reminders by status for a user.
     */
    public function getByStatusForUser(ReminderStatus $status, User|int $user): Collection;

    /**
     * Get pending reminders for a user.
     */
    public function getPendingForUser(User|int $user): Collection;

    /**
     * Get upcoming reminders for a user (pending, within specified days).
     */
    public function getUpcomingForUser(User|int $user, int $days = 7): Collection;

    /**
     * Get reminders for a contract.
     */
    public function getForContract(Contract|string $contract): Collection;

    /**
     * Get reminders for a deadline.
     */
    public function getForDeadline(ContractDeadline|string $deadline): Collection;

    /**
     * Get all due reminders (pending and remind_at <= now).
     */
    public function getDue(): Collection;

    /**
     * Paginate reminders for a user.
     */
    public function paginateForUser(User|int $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new reminder.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Reminder;

    /**
     * Update a reminder.
     *
     * @param array<string, mixed> $data
     */
    public function update(Reminder $reminder, array $data): bool;

    /**
     * Delete a reminder.
     */
    public function delete(Reminder $reminder): bool;

    /**
     * Mark a reminder as sent.
     */
    public function markAsSent(Reminder $reminder): bool;

    /**
     * Mark a reminder as failed.
     */
    public function markAsFailed(Reminder $reminder): bool;

    /**
     * Cancel a reminder.
     */
    public function cancel(Reminder $reminder): bool;
}
