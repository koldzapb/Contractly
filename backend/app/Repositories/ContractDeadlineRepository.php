<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\DeadlineType;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\User;
use App\Repositories\Contracts\ContractDeadlineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ContractDeadline>
 */
class ContractDeadlineRepository extends BaseRepository implements ContractDeadlineRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ContractDeadline::class);
    }

    public function find(string $id): ?ContractDeadline
    {
        return $this->query()->find($id);
    }

    public function getForAnalysis(ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->orderBy('deadline_date')
            ->get();
    }

    public function getByTypeForAnalysis(DeadlineType $type, ContractAnalysis|string $analysis): Collection
    {
        return $this->query()
            ->where('contract_analysis_id', $this->resolveId($analysis))
            ->where('deadline_type', $type)
            ->orderBy('deadline_date')
            ->get();
    }

    public function getUpcoming(int $days = 30): Collection
    {
        return $this->query()
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '>=', now())
            ->where('deadline_date', '<=', now()->addDays($days))
            ->orderBy('deadline_date')
            ->get();
    }

    public function getUpcomingForUser(User|int $user, int $days = 30): Collection
    {
        return $this->query()
            ->whereHas('analysis.contract', function ($query) use ($user) {
                $query->where('user_id', $this->resolveUserId($user));
            })
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '>=', now())
            ->where('deadline_date', '<=', now()->addDays($days))
            ->orderBy('deadline_date')
            ->get();
    }

    public function getPast(): Collection
    {
        return $this->query()
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now())
            ->orderByDesc('deadline_date')
            ->get();
    }

    public function getPastForUser(User|int $user): Collection
    {
        return $this->query()
            ->whereHas('analysis.contract', function ($query) use ($user) {
                $query->where('user_id', $this->resolveUserId($user));
            })
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now())
            ->orderByDesc('deadline_date')
            ->get();
    }

    public function getOverdue(int $daysBack = 30): Collection
    {
        return $this->query()
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now())
            ->where('deadline_date', '>=', now()->subDays($daysBack))
            ->orderByDesc('deadline_date')
            ->get();
    }

    public function getOverdueForUser(User|int $user, int $daysBack = 30): Collection
    {
        return $this->query()
            ->whereHas('analysis.contract', function ($query) use ($user) {
                $query->where('user_id', $this->resolveUserId($user));
            })
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now())
            ->where('deadline_date', '>=', now()->subDays($daysBack))
            ->orderByDesc('deadline_date')
            ->get();
    }

    public function create(array $data): ContractDeadline
    {
        return $this->query()->create($data);
    }

    public function createMany(array $deadlines): Collection
    {
        $created = new Collection();

        foreach ($deadlines as $deadlineData) {
            $created->push($this->create($deadlineData));
        }

        return $created;
    }

    public function delete(ContractDeadline $deadline): bool
    {
        return (bool) $deadline->delete();
    }
}
