<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReminderStatus;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Support\Carbon;

class ReminderService
{
    public function __construct(
        private ReminderRepositoryInterface $reminders,
    ) {}

    /**
     * Create a reminder for a deadline.
     */
    public function createForDeadline(
        ContractDeadline $deadline,
        User $user,
        int $daysBefore,
        ?string $title = null,
        ?string $deadlineDate = null,
    ): Reminder {
        // If a deadline date is provided and the deadline doesn't have one, update it
        if ($deadlineDate !== null && $deadline->deadline_date === null) {
            $deadline->update(['deadline_date' => $deadlineDate]);
            $deadline->refresh();
        }

        // Load the contract through the analysis
        $deadline->loadMissing('analysis.contract');

        /** @var \App\Models\ContractAnalysis $analysis */
        $analysis = $deadline->analysis;

        /** @var \App\Models\Contract $contract */
        $contract = $analysis->contract;

        // Calculate remind_at date
        $remindAt = $this->calculateRemindAt($deadline, $daysBefore);

        return $this->reminders->create([
            'user_id' => $user->id,
            'contract_id' => $contract->id,
            'contract_deadline_id' => $deadline->id,
            'title' => $title ?? "Reminder: {$deadline->title}",
            'remind_at' => $remindAt,
            'days_before' => $daysBefore,
            'channel' => 'email',
            'status' => ReminderStatus::PENDING,
        ]);
    }

    /**
     * Update a reminder.
     *
     * @param array<string, mixed> $data
     */
    public function update(Reminder $reminder, array $data): Reminder
    {
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['days_before'])) {
            $updateData['days_before'] = $data['days_before'];

            // Recalculate remind_at if days_before changed
            /** @var ContractDeadline|null $deadline */
            $deadline = $reminder->deadline;
            if ($deadline !== null) {
                $updateData['remind_at'] = $this->calculateRemindAt(
                    $deadline,
                    $data['days_before'],
                );
            }
        }

        if (! empty($updateData)) {
            $this->reminders->update($reminder, $updateData);
        }

        return $reminder->fresh();
    }

    /**
     * Cancel a pending reminder.
     */
    public function cancel(Reminder $reminder): bool
    {
        if (! $reminder->isPending()) {
            return false;
        }

        return $this->reminders->cancel($reminder);
    }

    /**
     * Delete a reminder.
     */
    public function delete(Reminder $reminder): bool
    {
        return $this->reminders->delete($reminder);
    }

    /**
     * Calculate the remind_at date based on deadline and days before.
     */
    private function calculateRemindAt(ContractDeadline $deadline, int $daysBefore): Carbon
    {
        if ($deadline->deadline_date === null) {
            // If no specific deadline date, set reminder for now + days_before
            // This is a fallback; ideally deadlines should have dates
            return now()->addDays($daysBefore);
        }

        // Set reminder time to 8:00 AM on the calculated date
        return $deadline->deadline_date
            ->subDays($daysBefore)
            ->setTime(8, 0, 0);
    }
}
