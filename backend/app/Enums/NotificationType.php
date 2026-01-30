<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case ANALYSIS_COMPLETE = 'analysis_complete';
    case DEADLINE_REMINDER = 'deadline_reminder';
    case WEEKLY_DIGEST = 'weekly_digest';
    case CONTRACT_EXPIRING = 'contract_expiring';

    public function label(): string
    {
        return match ($this) {
            self::ANALYSIS_COMPLETE => 'Analysis Complete',
            self::DEADLINE_REMINDER => 'Deadline Reminder',
            self::WEEKLY_DIGEST => 'Weekly Digest',
            self::CONTRACT_EXPIRING => 'Contract Expiring',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ANALYSIS_COMPLETE => 'Receive an email when your contract analysis is complete',
            self::DEADLINE_REMINDER => 'Receive reminders before important deadlines',
            self::WEEKLY_DIGEST => 'Receive a weekly summary of your contracts and deadlines',
            self::CONTRACT_EXPIRING => 'Receive notifications when contracts are about to expire',
        };
    }

    public function defaultEnabled(): bool
    {
        return match ($this) {
            self::ANALYSIS_COMPLETE => true,
            self::DEADLINE_REMINDER => true,
            self::WEEKLY_DIGEST => false,
            self::CONTRACT_EXPIRING => true,
        };
    }
}
