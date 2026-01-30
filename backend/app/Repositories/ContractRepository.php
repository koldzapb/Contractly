<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataTransferObjects\ContractSearchFilters;
use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function searchAndFilterForUser(
        User|int $user,
        ContractSearchFilters $filters,
    ): LengthAwarePaginator {
        $query = $this->query()
            ->where('user_id', $this->resolveUserId($user));

        // Apply full-text search if query provided
        if ($filters->query !== null && $filters->query !== '') {
            $query = $this->applyFullTextSearch($query, $filters->query);
        }

        // Filter by statuses
        if ($filters->statuses !== null && count($filters->statuses) > 0) {
            $query->whereIn('status', array_map(fn ($s) => $s->value, $filters->statuses));
        }

        // Filter by risk levels
        if ($filters->riskLevels !== null && count($filters->riskLevels) > 0) {
            $query->whereIn('overall_risk_level', array_map(fn ($r) => $r->value, $filters->riskLevels));
        }

        // Filter by file types
        if ($filters->fileTypes !== null && count($filters->fileTypes) > 0) {
            $query->whereIn('file_type', array_map(fn ($f) => $f->value, $filters->fileTypes));
        }

        // Filter by date range
        if ($filters->dateFrom !== null) {
            $query->where('created_at', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo !== null) {
            $query->where('created_at', '<=', $filters->dateTo);
        }

        // Filter by has_deadlines
        if ($filters->hasDeadlines !== null) {
            $query->whereHas('analysis', function (Builder $q) use ($filters) {
                if ($filters->hasDeadlines) {
                    $q->whereHas('deadlines');
                } else {
                    $q->whereDoesntHave('deadlines');
                }
            });
        }

        // Apply sorting
        $sortColumn = $filters->sortBy;
        $sortDirection = $filters->sortOrder;

        // Handle special sort cases
        if ($sortColumn === 'overall_risk_level') {
            // Sort by risk severity (high > medium > low > none > null)
            $query->orderByRaw("
                CASE overall_risk_level
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'low' THEN 3
                    WHEN 'none' THEN 4
                    ELSE 5
                END " . ($sortDirection === 'desc' ? 'ASC' : 'DESC')
            );
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Secondary sort by created_at for consistency
        if ($sortColumn !== 'created_at') {
            $query->orderByDesc('created_at');
        }

        return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }

    /**
     * Apply search to query (PostgreSQL full-text or LIKE fallback for SQLite).
     *
     * @param  Builder<Contract>  $query
     * @return Builder<Contract>
     */
    private function applyFullTextSearch(Builder $query, string $searchQuery): Builder
    {
        $isPostgres = DB::getDriverName() === 'pgsql';

        if ($isPostgres) {
            return $this->applyPostgresFullTextSearch($query, $searchQuery);
        }

        // Fallback to LIKE search for SQLite/other databases
        return $this->applyLikeSearch($query, $searchQuery);
    }

    /**
     * Apply PostgreSQL full-text search.
     *
     * @param  Builder<Contract>  $query
     * @return Builder<Contract>
     */
    private function applyPostgresFullTextSearch(Builder $query, string $searchQuery): Builder
    {
        // Sanitize and prepare the search query for PostgreSQL
        $searchTerms = preg_split('/\s+/', trim($searchQuery)) ?: [];
        $searchTerms = array_filter($searchTerms, fn ($term) => strlen($term) >= 2);

        if (empty($searchTerms)) {
            return $this->applyLikeSearch($query, $searchQuery);
        }

        // Convert terms to tsquery format (prefix matching with :*)
        $tsQueryParts = array_map(
            fn ($term) => preg_replace('/[^\w]/', '', $term) . ':*',
            $searchTerms
        );
        $tsQuery = implode(' & ', $tsQueryParts);

        // Use full-text search with the search_vector column
        return $query->where(function ($q) use ($tsQuery, $searchQuery) {
            $q->whereRaw(
                'search_vector @@ to_tsquery(\'english\', ?)',
                [$tsQuery]
            )
                // Also search in analysis summary with a join
                ->orWhereHas('analysis', function ($analysisQuery) use ($searchQuery) {
                    $analysisQuery->where('summary', 'ilike', '%' . $searchQuery . '%');
                });
        })
            // Add relevance scoring for ordering when searching
            ->when($tsQuery, function ($q) use ($tsQuery) {
                $q->addSelect([
                    '*',
                    DB::raw("ts_rank(search_vector, to_tsquery('english', " . DB::getPdo()->quote($tsQuery) . ")) as search_rank"),
                ]);
            });
    }

    /**
     * Apply LIKE-based search (for SQLite and fallback).
     *
     * @param  Builder<Contract>  $query
     * @return Builder<Contract>
     */
    private function applyLikeSearch(Builder $query, string $searchQuery): Builder
    {
        $searchPattern = '%' . $searchQuery . '%';

        return $query->where(function ($q) use ($searchPattern) {
            $q->where('title', 'like', $searchPattern)
                ->orWhere('original_filename', 'like', $searchPattern)
                ->orWhereHas('analysis', function ($analysisQuery) use ($searchPattern) {
                    $analysisQuery->where('summary', 'like', $searchPattern);
                });
        });
    }
}
