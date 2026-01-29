<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentCategory: string
{
    case LEGAL = 'legal';
    case PRE_CONTRACTUAL = 'pre_contractual';
    case NON_LEGAL = 'non_legal';

    public function label(): string
    {
        return match ($this) {
            self::LEGAL => 'Legal Document',
            self::PRE_CONTRACTUAL => 'Pre-Contractual',
            self::NON_LEGAL => 'Non-Legal Document',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LEGAL => 'green',
            self::PRE_CONTRACTUAL => 'yellow',
            self::NON_LEGAL => 'red',
        };
    }

    public function isAnalyzable(): bool
    {
        return match ($this) {
            self::LEGAL, self::PRE_CONTRACTUAL => true,
            self::NON_LEGAL => false,
        };
    }

    /**
     * Get warnings for pre-contractual documents.
     */
    public function getWarnings(DocumentType $documentType): array
    {
        if ($this !== self::PRE_CONTRACTUAL) {
            return [];
        }

        return match ($documentType) {
            DocumentType::MOU => [
                'This document is typically non-binding and represents preliminary discussions.',
                'Terms outlined may not be legally enforceable.',
            ],
            DocumentType::LOI => [
                'Letters of Intent often contain non-binding provisions.',
                'Final terms may differ significantly from what is outlined here.',
            ],
            DocumentType::TERM_SHEET => [
                'Term sheets summarize proposed terms but are usually non-binding.',
                'Actual contract terms may vary from these initial proposals.',
            ],
            default => [],
        };
    }
}
