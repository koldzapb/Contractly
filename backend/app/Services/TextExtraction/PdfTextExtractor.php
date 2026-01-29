<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

use App\Exceptions\PdfParsingException;
use App\Exceptions\TextExtractionException;
use App\Services\PdfParserService;

/**
 * Text extractor for PDF files.
 * Wraps the existing PdfParserService.
 */
class PdfTextExtractor implements TextExtractionInterface
{
    public function __construct(
        private PdfParserService $pdfParser,
    ) {}

    /**
     * @throws TextExtractionException
     */
    public function extract(string $filePath): TextExtractionResult
    {
        try {
            $pdfContent = $this->pdfParser->extractText($filePath);

            return new TextExtractionResult(
                fullText: $pdfContent->fullText,
                textByPage: $pdfContent->textByPage,
                pageCount: $pdfContent->pageCount,
                metadata: $pdfContent->metadata,
            );
        } catch (PdfParsingException $e) {
            throw new TextExtractionException($e->getMessage(), previous: $e);
        }
    }

    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'pdf';
    }
}
