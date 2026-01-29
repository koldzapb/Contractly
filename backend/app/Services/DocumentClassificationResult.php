<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentCategory;
use App\Enums\DocumentType;

/**
 * Data transfer object for document classification results.
 */
readonly class DocumentClassificationResult
{
    /**
     * @param bool               $isLegalDocument      Whether the document is a legal document
     * @param DocumentType       $documentType         The detected document type
     * @param DocumentCategory   $category             The document category
     * @param float              $confidence           Confidence score (0-1)
     * @param string|null        $rejectionReason      Reason for rejection (if not legal)
     * @param array<int, string> $warnings             Warnings about the document
     * @param bool               $analyzedWithOverride Whether analysis was forced despite rejection
     */
    public function __construct(
        public bool $isLegalDocument,
        public DocumentType $documentType,
        public DocumentCategory $category,
        public float $confidence,
        public ?string $rejectionReason = null,
        public array $warnings = [],
        public bool $analyzedWithOverride = false,
    ) {}

    /**
     * Create a result for a valid legal document.
     */
    public static function legal(DocumentType $type, float $confidence = 0.9): self
    {
        return new self(
            isLegalDocument: true,
            documentType: $type,
            category: $type->category(),
            confidence: $confidence,
            warnings: $type->category()->getWarnings($type),
        );
    }

    /**
     * Create a result for a rejected non-legal document.
     */
    public static function rejected(DocumentType $type, string $reason, float $confidence = 0.8): self
    {
        return new self(
            isLegalDocument: false,
            documentType: $type,
            category: DocumentCategory::NON_LEGAL,
            confidence: $confidence,
            rejectionReason: $reason,
        );
    }

    /**
     * Create a new result with override flag set.
     */
    public function withOverride(): self
    {
        return new self(
            isLegalDocument: $this->isLegalDocument,
            documentType: $this->documentType,
            category: $this->category,
            confidence: $this->confidence,
            rejectionReason: $this->rejectionReason,
            warnings: $this->warnings,
            analyzedWithOverride: true,
        );
    }

    /**
     * Check if the document can be analyzed (either legal or overridden).
     */
    public function canAnalyze(): bool
    {
        return $this->isLegalDocument || $this->analyzedWithOverride;
    }

    /**
     * Convert to array for JSON storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_legal_document' => $this->isLegalDocument,
            'document_type' => $this->documentType->value,
            'document_type_label' => $this->documentType->label(),
            'category' => $this->category->value,
            'confidence' => $this->confidence,
            'rejection_reason' => $this->rejectionReason,
            'warnings' => $this->warnings,
            'analyzed_with_override' => $this->analyzedWithOverride,
        ];
    }

    /**
     * Create from array (from JSON storage).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isLegalDocument: $data['is_legal_document'] ?? false,
            documentType: DocumentType::tryFrom($data['document_type'] ?? '') ?? DocumentType::UNKNOWN,
            category: DocumentCategory::tryFrom($data['category'] ?? '') ?? DocumentCategory::NON_LEGAL,
            confidence: (float) ($data['confidence'] ?? 0),
            rejectionReason: $data['rejection_reason'] ?? null,
            warnings: $data['warnings'] ?? [],
            analyzedWithOverride: $data['analyzed_with_override'] ?? false,
        );
    }
}
