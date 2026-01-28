<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\User;
use App\Repositories\Contracts\ContractClauseRepositoryInterface;
use App\Repositories\Contracts\ContractDeadlineRepositoryInterface;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
        private ContractClauseRepositoryInterface $clauses,
        private ContractDeadlineRepositoryInterface $deadlines,
    ) {}

    /**
     * Get all dashboard data for a user.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        return [
            'stats' => $this->getStats($user),
            'upcoming_deadlines' => $this->getUpcomingDeadlines($user),
            'recent_contracts' => $this->getRecentContracts($user),
        ];
    }

    /**
     * Get dashboard statistics.
     *
     * @return array<string, int>
     */
    private function getStats(User $user): array
    {
        $statusCounts = $this->contracts->countByStatusForUser($user);

        return [
            'total_contracts' => array_sum($statusCounts),
            'pending_analysis' => $statusCounts[ContractStatus::PENDING->value] ?? 0,
            'processing_analysis' => $statusCounts[ContractStatus::PROCESSING->value] ?? 0,
            'completed_analysis' => $statusCounts[ContractStatus::COMPLETED->value] ?? 0,
            'failed_analysis' => $statusCounts[ContractStatus::FAILED->value] ?? 0,
            'high_risk_clauses' => $this->clauses->countHighRiskClausesForUser($user),
            'overdue_deadlines' => $this->deadlines->countOverdueForUser($user),
        ];
    }

    /**
     * Get upcoming deadlines with contract information.
     */
    private function getUpcomingDeadlines(User $user, int $days = 30, int $limit = 5): Collection
    {
        $deadlines = $this->deadlines->getUpcomingForUser($user, $days);

        // Load the contract relationship and limit results
        $deadlines->load('analysis.contract');

        return $deadlines->take($limit);
    }

    /**
     * Get recent contracts.
     */
    private function getRecentContracts(User $user, int $limit = 5): Collection
    {
        return $this->contracts->getRecentForUser($user, $limit);
    }
}
