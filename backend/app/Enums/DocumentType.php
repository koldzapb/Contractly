<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case CONTRACT = 'contract';
    case AMENDMENT = 'amendment';
    case NDA = 'nda';
    case MOU = 'mou';
    case LOI = 'loi';
    case TERM_SHEET = 'term_sheet';
    case INVOICE = 'invoice';
    case RECEIPT = 'receipt';
    case LETTER = 'letter';
    case REPORT = 'report';
    case OTHER = 'other';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::CONTRACT => 'Contract',
            self::AMENDMENT => 'Amendment',
            self::NDA => 'Non-Disclosure Agreement',
            self::MOU => 'Memorandum of Understanding',
            self::LOI => 'Letter of Intent',
            self::TERM_SHEET => 'Term Sheet',
            self::INVOICE => 'Invoice',
            self::RECEIPT => 'Receipt',
            self::LETTER => 'Letter',
            self::REPORT => 'Report',
            self::OTHER => 'Other Document',
            self::UNKNOWN => 'Unknown',
        };
    }

    public function category(): DocumentCategory
    {
        return match ($this) {
            self::CONTRACT, self::AMENDMENT, self::NDA => DocumentCategory::LEGAL,
            self::MOU, self::LOI, self::TERM_SHEET => DocumentCategory::PRE_CONTRACTUAL,
            default => DocumentCategory::NON_LEGAL,
        };
    }

    public function isLegalDocument(): bool
    {
        return $this->category() === DocumentCategory::LEGAL;
    }

    public function isPreContractual(): bool
    {
        return $this->category() === DocumentCategory::PRE_CONTRACTUAL;
    }

    /**
     * Document types that are valid for contract analysis.
     */
    public static function analyzableTypes(): array
    {
        return [
            self::CONTRACT,
            self::AMENDMENT,
            self::NDA,
            self::MOU,
            self::LOI,
            self::TERM_SHEET,
        ];
    }
}
