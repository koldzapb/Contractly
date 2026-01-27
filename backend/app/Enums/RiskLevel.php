<?php

declare(strict_types=1);

namespace App\Enums;

enum RiskLevel: string
{
    case NONE = 'none';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'No Risk',
            self::LOW => 'Low Risk',
            self::MEDIUM => 'Medium Risk',
            self::HIGH => 'High Risk',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NONE => 'gray',
            self::LOW => 'green',
            self::MEDIUM => 'yellow',
            self::HIGH => 'red',
        };
    }

    public function severity(): int
    {
        return match ($this) {
            self::NONE => 0,
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
        };
    }

    public static function fromSeverity(int $severity): self
    {
        return match (true) {
            $severity <= 0 => self::NONE,
            $severity === 1 => self::LOW,
            $severity === 2 => self::MEDIUM,
            default => self::HIGH,
        };
    }
}
