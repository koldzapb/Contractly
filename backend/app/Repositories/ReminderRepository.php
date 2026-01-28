<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Reminder>
 */
class ReminderRepository extends BaseRepository implements ReminderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Reminder::class);
    }

    public function find(string $id): ?Reminder
    {
        return $this->query()->find($id);
    }

    public function findForUser(string $id, User|int $user): ?Reminder
    {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $this->resolveUserId($user))
            ->first();
    }

    public function getAllForUser(User|int $user): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->orderBy('remind_at')
            ->get();
    }

    public function getByStatusForUser(ReminderStatus $status, User|int $user): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->where('status', $status)
            ->orderBy('remind_at')
            ->get();
    }

    public function getPendingForUser(User|int $user): Collection
    {
        return $this->getByStatusForUser(ReminderStatus::PENDING, $user);
    }

    public function getUpcomingForUser(User|int $user, int $days = 7): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->where('status', ReminderStatus::PENDING)
            ->where('remind_at', '>=', now())
            ->where('remind_at', '<=', now()->addDays($days))
            ->orderBy('remind_at')
            ->get();
    }

    public function getForContract(Contract|string $contract): Collection
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->orderBy('remind_at')
            ->get();
    }

    public function getForDeadline(ContractDeadline|string $deadline): Collection
    {
        return $this->query()
            ->where('contract_deadline_id', $this->resolveId($deadline))
            ->orderBy('remind_at')
            ->get();
    }

    public function getDue(): Collection
    {
        return $this->query()
            ->where('status', ReminderStatus::PENDING)
            ->where('remind_at', '<=', now())
            ->orderBy('remind_at')
            ->get();
    }

    public function paginateForUser(User|int $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['contract', 'deadline'])
            ->where('user_id', $this->resolveUserId($user))
            ->orderBy('remind_at')
            ->paginate($perPage);
    }

    public function create(array $data): Reminder
    {
        return $this->query()->create($data);
    }

    public function update(Reminder $reminder, array $data): bool
    {
        return $reminder->update($data);
    }

    public function delete(Reminder $reminder): bool
    {
        return (bool) $reminder->delete();
    }

    public function markAsSent(Reminder $reminder): bool
    {
        return $reminder->update([
            'status' => ReminderStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(Reminder $reminder): bool
    {
        return $reminder->update([
            'status' => ReminderStatus::FAILED,
        ]);
    }

    public function cancel(Reminder $reminder): bool
    {
        return $reminder->update([
            'status' => ReminderStatus::CANCELLED,
        ]);
    }
}
