<?php

declare(strict_types=1);

namespace App\Enums;

enum DeadlineType: string
{
    case PAYMENT = 'payment';
    case RENEWAL = 'renewal';
    case TERMINATION_NOTICE = 'termination_notice';
    case DELIVERY = 'delivery';
    case REVIEW_PERIOD = 'review_period';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'Payment Due',
            self::RENEWAL => 'Renewal Date',
            self::TERMINATION_NOTICE => 'Termination Notice',
            self::DELIVERY => 'Delivery Date',
            self::REVIEW_PERIOD => 'Review Period',
            self::OTHER => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYMENT => 'banknotes',
            self::RENEWAL => 'arrow-path',
            self::TERMINATION_NOTICE => 'clock',
            self::DELIVERY => 'truck',
            self::REVIEW_PERIOD => 'eye',
            self::OTHER => 'calendar',
        };
    }

    public function defaultReminderDays(): int
    {
        return match ($this) {
            self::PAYMENT => 7,
            self::RENEWAL => 30,
            self::TERMINATION_NOTICE => 14,
            self::DELIVERY => 3,
            self::REVIEW_PERIOD => 7,
            self::OTHER => 7,
        };
    }
}
