<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PiiType;

/**
 * Data transfer object for a single detected PII item.
 */
readonly class DetectedPii
{
    /**
     * @param string  $id            Unique identifier for this detection
     * @param PiiType $type          Type of PII detected
     * @param string  $value         The detected value
     * @param string  $redactedValue The replacement value when redacted
     * @param int     $startPosition Start position in the text
     * @param int     $endPosition   End position in the text
     * @param string  $context       Surrounding text for context
     * @param bool    $selected      Whether this item is selected for redaction
     */
    public function __construct(
        public string $id,
        public PiiType $type,
        public string $value,
        public string $redactedValue,
        public int $startPosition,
        public int $endPosition,
        public string $context,
        public bool $selected = true,
    ) {}

    /**
     * Create a new DetectedPii with selection state changed.
     */
    public function withSelected(bool $selected): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            value: $this->value,
            redactedValue: $this->redactedValue,
            startPosition: $this->startPosition,
            endPosition: $this->endPosition,
            context: $this->context,
            selected: $selected,
        );
    }

    /**
     * Convert to array for JSON storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'value' => $this->value,
            'redacted_value' => $this->redactedValue,
            'start_position' => $this->startPosition,
            'end_position' => $this->endPosition,
            'context' => $this->context,
            'selected' => $this->selected,
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
            id: $data['id'] ?? '',
            type: PiiType::tryFrom($data['type'] ?? '') ?? PiiType::EMAIL,
            value: $data['value'] ?? '',
            redactedValue: $data['redacted_value'] ?? '',
            startPosition: (int) ($data['start_position'] ?? 0),
            endPosition: (int) ($data['end_position'] ?? 0),
            context: $data['context'] ?? '',
            selected: $data['selected'] ?? true,
        );
    }
}
