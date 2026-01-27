<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PdfParsingException;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfParserService
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser;
    }

    /**
     * Extract text content from a PDF file.
     *
     * @throws PdfParsingException
     */
    public function extractText(string $filePath): PdfContent
    {
        if (! file_exists($filePath)) {
            throw new PdfParsingException("PDF file not found: {$filePath}");
        }

        try {
            $document = $this->parser->parseFile($filePath);
            $pages = $document->getPages();
            $pageCount = count($pages);
            $textByPage = [];
            $fullText = '';

            foreach ($pages as $index => $page) {
                $pageNumber = $index + 1;
                $pageText = $page->getText();

                // Clean up the text
                $pageText = $this->cleanText($pageText);

                $textByPage[$pageNumber] = $pageText;
                $fullText .= "\n--- Page {$pageNumber} ---\n\n{$pageText}\n";
            }

            // Get document metadata
            $metadata = $this->extractMetadata($document);

            return new PdfContent(
                fullText: trim($fullText),
                textByPage: $textByPage,
                pageCount: $pageCount,
                metadata: $metadata,
            );
        } catch (\Exception $e) {
            Log::error('PDF parsing failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw new PdfParsingException(
                "Failed to parse PDF: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    /**
     * Clean extracted text.
     */
    private function cleanText(string $text): string
    {
        // Replace multiple whitespace with single space
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;

        // Replace multiple newlines with double newline
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        // Trim lines
        $lines = array_map('trim', explode("\n", $text));
        $text = implode("\n", $lines);

        return trim($text);
    }

    /**
     * Extract metadata from the PDF document.
     *
     * @return array<string, mixed>
     */
    private function extractMetadata(\Smalot\PdfParser\Document $document): array
    {
        try {
            $details = $document->getDetails();

            return [
                'title' => $details['Title'] ?? null,
                'author' => $details['Author'] ?? null,
                'creator' => $details['Creator'] ?? null,
                'producer' => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null,
                'modification_date' => $details['ModDate'] ?? null,
            ];
        } catch (\Exception) {
            return [];
        }
    }
}
