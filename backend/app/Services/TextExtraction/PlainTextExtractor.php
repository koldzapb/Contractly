<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

use App\Exceptions\TextExtractionException;

/**
 * Text extractor for plain text (.txt) files.
 */
class PlainTextExtractor implements TextExtractionInterface
{
    /**
     * @throws TextExtractionException
     */
    public function extract(string $filePath): TextExtractionResult
    {
        if (! file_exists($filePath)) {
            throw new TextExtractionException("Text file not found: {$filePath}");
        }

        if (! is_readable($filePath)) {
            throw new TextExtractionException("Text file is not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new TextExtractionException("Failed to read text file: {$filePath}");
        }

        // Detect and convert encoding if necessary
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Clean up the text
        $content = $this->cleanText($content);

        return TextExtractionResult::fromSinglePage($content, [
            'original_encoding' => $encoding ?: 'unknown',
            'file_size' => filesize($filePath),
        ]);
    }

    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'txt';
    }

    /**
     * Clean extracted text.
     */
    private function cleanText(string $text): string
    {
        // Replace multiple whitespace with single space (preserve newlines)
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;

        // Replace multiple newlines with double newline
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        // Trim lines
        $lines = array_map('trim', explode("\n", $text));
        $text = implode("\n", $lines);

        return trim($text);
    }
}
