<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PiiType;

/**
 * Data transfer object for PII detection results.
 */
readonly class PiiDetectionResult
{
    /**
     * @param bool                    $hasPii        Whether any PII was detected
     * @param int                     $totalCount    Total number of PII items detected
     * @param array<string, int>      $countsByType  Count of PII by type
     * @param array<int, DetectedPii> $items         List of detected PII items
     * @param string                  $extractedText The full extracted text
     */
    public function __construct(
        public bool $hasPii,
        public int $totalCount,
        public array $countsByType,
        public array $items,
        public string $extractedText,
    ) {}

    /**
     * Create an empty result with no PII detected.
     */
    public static function empty(string $extractedText): self
    {
        return new self(
            hasPii: false,
            totalCount: 0,
            countsByType: self::initializeCountsByType(),
            items: [],
            extractedText: $extractedText,
        );
    }

    /**
     * Create from detected items.
     *
     * @param array<int, DetectedPii> $items
     */
    public static function fromItems(array $items, string $extractedText): self
    {
        $countsByType = self::initializeCountsByType();

        foreach ($items as $item) {
            $countsByType[$item->type->value]++;
        }

        return new self(
            hasPii: count($items) > 0,
            totalCount: count($items),
            countsByType: $countsByType,
            items: $items,
            extractedText: $extractedText,
        );
    }

    /**
     * Get items by type.
     *
     * @return array<int, DetectedPii>
     */
    public function getItemsByType(PiiType $type): array
    {
        return array_filter(
            $this->items,
            fn (DetectedPii $item) => $item->type === $type,
        );
    }

    /**
     * Get selected items.
     *
     * @return array<int, DetectedPii>
     */
    public function getSelectedItems(): array
    {
        return array_filter(
            $this->items,
            fn (DetectedPii $item) => $item->selected,
        );
    }

    /**
     * Apply redactions to text based on selected items.
     *
     * @param array<int, string> $itemIds IDs of items to redact
     */
    public function applyRedactions(array $itemIds): string
    {
        $text = $this->extractedText;

        // Get items to redact, sorted by position descending (to preserve positions)
        $itemsToRedact = array_filter(
            $this->items,
            fn (DetectedPii $item) => in_array($item->id, $itemIds, true),
        );

        usort($itemsToRedact, fn ($a, $b) => $b->startPosition <=> $a->startPosition);

        foreach ($itemsToRedact as $item) {
            $text = substr_replace(
                $text,
                $item->redactedValue,
                $item->startPosition,
                $item->endPosition - $item->startPosition,
            );
        }

        return $text;
    }

    /**
     * Convert to array for JSON storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'has_pii' => $this->hasPii,
            'total_count' => $this->totalCount,
            'counts_by_type' => $this->countsByType,
            'items' => array_map(fn (DetectedPii $item) => $item->toArray(), $this->items),
            'extracted_text' => $this->extractedText,
        ];
    }

    /**
     * Create from array (from JSON storage).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn (array $item) => DetectedPii::fromArray($item),
            $data['items'] ?? [],
        );

        return new self(
            hasPii: $data['has_pii'] ?? false,
            totalCount: (int) ($data['total_count'] ?? 0),
            countsByType: $data['counts_by_type'] ?? self::initializeCountsByType(),
            items: $items,
            extractedText: $data['extracted_text'] ?? '',
        );
    }

    /**
     * Initialize counts by type with zeros.
     *
     * @return array<string, int>
     */
    private static function initializeCountsByType(): array
    {
        $counts = [];
        foreach (PiiType::cases() as $type) {
            $counts[$type->value] = 0;
        }

        return $counts;
    }
}
