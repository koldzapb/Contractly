<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Data transfer object for extracted PDF content.
 */
readonly class PdfContent
{
    /**
     * @param string               $fullText   The complete text content of the PDF
     * @param array<int, string>   $textByPage Text content indexed by page number (1-based)
     * @param int                  $pageCount  Total number of pages
     * @param array<string, mixed> $metadata   PDF metadata (title, author, etc.)
     */
    public function __construct(
        public string $fullText,
        public array $textByPage,
        public int $pageCount,
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
     * Check if the PDF has extractable text.
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
}
