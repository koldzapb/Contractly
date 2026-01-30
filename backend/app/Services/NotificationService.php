<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationType;
use App\Mail\AnalysisCompleteMail;
use App\Mail\ContractExpiringMail;
use App\Mail\WeeklyDigestMail;
use App\Models\Contract;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Contracts\NotificationPreferenceRepositoryInterface;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(
        private NotificationPreferenceRepositoryInterface $preferences,
        private ContractRepositoryInterface $contracts,
        private ReminderRepositoryInterface $reminders,
    ) {}

    /**
     * Get notification preferences for a user.
     */
    public function getPreferences(User $user): NotificationPreference
    {
        return $this->preferences->getOrCreateForUser($user);
    }

    /**
     * Update notification preferences for a user.
     *
     * @param array<string, mixed> $data
     */
    public function updatePreferences(User $user, array $data): NotificationPreference
    {
        $preference = $this->preferences->getOrCreateForUser($user);
        $this->preferences->update($preference, $data);

        return $preference->fresh();
    }

    /**
     * Check if user wants a specific notification type.
     */
    public function shouldNotify(User $user, NotificationType $type): bool
    {
        return $this->preferences->wantsNotification($user, $type->value);
    }

    /**
     * Send analysis complete notification if user wants it.
     */
    public function sendAnalysisComplete(Contract $contract): void
    {
        /** @var User|null $user */
        $user = $contract->user;

        if ($user === null) {
            return;
        }

        if (! $this->shouldNotify($user, NotificationType::ANALYSIS_COMPLETE)) {
            return;
        }

        Mail::to($user)->queue(new AnalysisCompleteMail($contract));
    }

    /**
     * Send contract expiring notification if user wants it.
     */
    public function sendContractExpiring(Contract $contract, int $daysUntilExpiry): void
    {
        /** @var User|null $user */
        $user = $contract->user;

        if ($user === null) {
            return;
        }

        if (! $this->shouldNotify($user, NotificationType::CONTRACT_EXPIRING)) {
            return;
        }

        Mail::to($user)->queue(new ContractExpiringMail($contract, $daysUntilExpiry));
    }

    /**
     * Send weekly digest to all users who want it.
     *
     * @return int Number of digests sent
     */
    public function sendWeeklyDigests(): int
    {
        $users = $this->preferences->getUsersWantingNotification('weekly_digest');
        $count = 0;

        foreach ($users as $user) {
            $digestData = $this->buildWeeklyDigestData($user);

            // Only send if there's something to report
            if ($digestData['contracts_analyzed'] > 0 ||
                $digestData['upcoming_deadlines'] > 0 ||
                $digestData['high_risk_count'] > 0) {
                Mail::to($user)->queue(new WeeklyDigestMail($user, $digestData));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Build weekly digest data for a user.
     *
     * @return array<string, mixed>
     */
    private function buildWeeklyDigestData(User $user): array
    {
        $oneWeekAgo = now()->subWeek();
        $oneWeekFromNow = now()->addWeek();

        // Get contracts analyzed in the last week
        $recentContracts = $this->contracts->getRecentlyCompletedForUser($user, $oneWeekAgo);

        // Get upcoming deadlines
        $upcomingReminders = $this->reminders->getUpcomingForUser($user, 7);

        // Count high-risk contracts
        $highRiskCount = 0;
        foreach ($recentContracts as $contract) {
            /** @var Contract $contract */
            if ($contract->analysis?->overall_risk_level?->value === 'high') {
                $highRiskCount++;
            }
        }

        return [
            'contracts_analyzed' => $recentContracts->count(),
            'recent_contracts' => $recentContracts->take(5),
            'upcoming_deadlines' => $upcomingReminders->count(),
            'upcoming_reminders' => $upcomingReminders->take(5),
            'high_risk_count' => $highRiskCount,
            'period_start' => $oneWeekAgo,
            'period_end' => now(),
        ];
    }
}
