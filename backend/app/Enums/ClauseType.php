<?php

declare(strict_types=1);

namespace App\Enums;

enum ClauseType: string
{
    case PAYMENT = 'payment';
    case TERMINATION = 'termination';
    case LIABILITY = 'liability';
    case PENALTY = 'penalty';
    case AUTO_RENEWAL = 'auto_renewal';
    case NON_COMPETE = 'non_compete';
    case CONFIDENTIALITY = 'confidentiality';
    case INDEMNIFICATION = 'indemnification';
    case DISPUTE_RESOLUTION = 'dispute_resolution';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => 'Payment Terms',
            self::TERMINATION => 'Termination',
            self::LIABILITY => 'Liability',
            self::PENALTY => 'Penalty',
            self::AUTO_RENEWAL => 'Auto-Renewal',
            self::NON_COMPETE => 'Non-Compete',
            self::CONFIDENTIALITY => 'Confidentiality',
            self::INDEMNIFICATION => 'Indemnification',
            self::DISPUTE_RESOLUTION => 'Dispute Resolution',
            self::OTHER => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYMENT => 'currency-dollar',
            self::TERMINATION => 'x-circle',
            self::LIABILITY => 'shield-exclamation',
            self::PENALTY => 'exclamation-triangle',
            self::AUTO_RENEWAL => 'arrow-path',
            self::NON_COMPETE => 'ban',
            self::CONFIDENTIALITY => 'lock-closed',
            self::INDEMNIFICATION => 'shield-check',
            self::DISPUTE_RESOLUTION => 'scale',
            self::OTHER => 'document-text',
        };
    }

    /**
     * Clauses that typically warrant careful review.
     */
    public static function highAttentionTypes(): array
    {
        return [
            self::LIABILITY,
            self::PENALTY,
            self::INDEMNIFICATION,
            self::NON_COMPETE,
            self::AUTO_RENEWAL,
        ];
    }
}
