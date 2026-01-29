<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

/**
 * Unified DTO for text extraction results across all file types.
 */
readonly class TextExtractionResult
{
    /**
     * @param string               $fullText   The complete extracted text content
     * @param array<int, string>   $textByPage Text content indexed by page number (1-based), if applicable
     * @param int                  $pageCount  Total number of pages (1 for images/text files)
     * @param array<string, mixed> $metadata   Additional metadata (source-dependent)
     */
    public function __construct(
        public string $fullText,
        public array $textByPage = [],
        public int $pageCount = 1,
        public array $metadata = [],
    ) {}

    /**
     * Get the text for a specific page.
     */
    public function getPage(int $pageNumber): ?string
    {
        return $this->textByPage[$pageNumber] ?? null;
    }

    /**
     * Check if the extraction produced any text.
     */
    public function hasText(): bool
    {
        return strlen(trim($this->fullText)) > 0;
    }

    /**
     * Get the word count of the full text.
     */
    public function getWordCount(): int
    {
        return str_word_count($this->fullText);
    }

    /**
     * Get the character count of the full text.
     */
    public function getCharacterCount(): int
    {
        return strlen($this->fullText);
    }

    /**
     * Create from a single page of text.
     */
    public static function fromSinglePage(string $text, array $metadata = []): self
    {
        return new self(
            fullText: $text,
            textByPage: [1 => $text],
            pageCount: 1,
            metadata: $metadata,
        );
    }
}
