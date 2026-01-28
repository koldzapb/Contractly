<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Contract>
 */
class ContractRepository extends BaseRepository implements ContractRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Contract::class);
    }

    public function find(string $id): ?Contract
    {
        return $this->query()->find($id);
    }

    public function findOrFail(string $id): Contract
    {
        return $this->query()->findOrFail($id);
    }

    public function findForUser(string $id, User|int $user): ?Contract
    {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $this->resolveUserId($user))
            ->first();
    }

    public function findWithAnalysis(string $id): ?Contract
    {
        return $this->query()
            ->with('analysis')
            ->find($id);
    }

    public function findWithAllRelations(string $id): ?Contract
    {
        return $this->query()
            ->with(['analysis.clauses', 'analysis.deadlines', 'reminders'])
            ->find($id);
    }

    public function getAllForUser(User|int $user): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->orderByDesc('created_at')
            ->get();
    }

    public function getByStatusForUser(ContractStatus $status, User|int $user): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();
    }

    public function paginateForUser(User|int $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): Contract
    {
        return $this->query()->create($data);
    }

    public function update(Contract $contract, array $data): bool
    {
        return $contract->update($data);
    }

    public function delete(Contract $contract): bool
    {
        return (bool) $contract->delete();
    }

    public function forceDelete(Contract $contract): bool
    {
        return (bool) $contract->forceDelete();
    }

    public function getByStatus(ContractStatus $status): Collection
    {
        return $this->query()
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();
    }

    public function countByStatusForUser(User|int $user): array
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function searchForUser(string $query, User|int $user): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('original_filename', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function getRecentForUser(User|int $user, int $limit = 5): Collection
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
