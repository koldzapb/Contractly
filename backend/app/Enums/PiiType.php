<?php

declare(strict_types=1);

namespace App\Enums;

enum PiiType: string
{
    case SSN = 'ssn';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case CREDIT_CARD = 'credit_card';
    case BANK_ROUTING = 'bank_routing';
    case BANK_ACCOUNT = 'bank_account';

    public function label(): string
    {
        return match ($this) {
            self::SSN => 'Social Security Number',
            self::EMAIL => 'Email Address',
            self::PHONE => 'Phone Number',
            self::CREDIT_CARD => 'Credit Card Number',
            self::BANK_ROUTING => 'Bank Routing Number',
            self::BANK_ACCOUNT => 'Bank Account Number',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::SSN => 'SSN',
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::CREDIT_CARD => 'Credit Card',
            self::BANK_ROUTING => 'Bank Routing',
            self::BANK_ACCOUNT => 'Bank Account',
        };
    }

    public function redactedPlaceholder(): string
    {
        return match ($this) {
            self::SSN => '[REDACTED_SSN]',
            self::EMAIL => '[REDACTED_EMAIL]',
            self::PHONE => '[REDACTED_PHONE]',
            self::CREDIT_CARD => '[REDACTED_CC]',
            self::BANK_ROUTING => '[REDACTED_ROUTING]',
            self::BANK_ACCOUNT => '[REDACTED_ACCOUNT]',
        };
    }

    public function sensitivityLevel(): int
    {
        return match ($this) {
            self::SSN => 5,
            self::CREDIT_CARD => 5,
            self::BANK_ACCOUNT => 4,
            self::BANK_ROUTING => 3,
            self::PHONE => 2,
            self::EMAIL => 1,
        };
    }

    /**
     * Get regex pattern for detecting this PII type.
     */
    public function pattern(): string
    {
        return match ($this) {
            // SSN: XXX-XX-XXXX or XXXXXXXXX
            self::SSN => '/\b(?:\d{3}-\d{2}-\d{4}|\d{9})\b/',
            // Email: standard email pattern
            self::EMAIL => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            // Phone: various US formats
            self::PHONE => '/\b(?:\+?1[-.\s]?)?(?:\(?\d{3}\)?[-.\s]?)?\d{3}[-.\s]?\d{4}\b/',
            // Credit Card: 13-19 digits with optional separators
            self::CREDIT_CARD => '/\b(?:\d{4}[-\s]?){3,4}\d{1,4}\b/',
            // Bank Routing: 9 digits
            self::BANK_ROUTING => '/\b\d{9}\b/',
            // Bank Account: 8-17 digits
            self::BANK_ACCOUNT => '/\b\d{8,17}\b/',
        };
    }

    /**
     * PII types ordered by sensitivity (highest first).
     */
    public static function bySensitivity(): array
    {
        $types = self::cases();
        usort($types, fn ($a, $b) => $b->sensitivityLevel() <=> $a->sensitivityLevel());

        return $types;
    }
}
