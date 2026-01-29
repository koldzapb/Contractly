<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

use App\Exceptions\UnsupportedFileTypeException;

/**
 * Factory for creating text extractors based on file type.
 */
class TextExtractorFactory
{
    public function __construct(
        private PdfTextExtractor $pdfExtractor,
        private ImageTextExtractor $imageExtractor,
        private PlainTextExtractor $textExtractor,
    ) {}

    /**
     * Create an extractor for the given file path.
     *
     * @throws UnsupportedFileTypeException
     */
    public function createFor(string $filePath): TextExtractionInterface
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return $this->createForExtension($extension);
    }

    /**
     * Create an extractor for the given file extension.
     *
     * @throws UnsupportedFileTypeException
     */
    public function createForExtension(string $extension): TextExtractionInterface
    {
        $ext = strtolower($extension);

        return match (true) {
            $this->pdfExtractor->supports($ext) => $this->pdfExtractor,
            $this->imageExtractor->supports($ext) => $this->imageExtractor,
            $this->textExtractor->supports($ext) => $this->textExtractor,
            default => throw new UnsupportedFileTypeException($ext),
        };
    }

    /**
     * Extract text from a file.
     *
     * @throws UnsupportedFileTypeException
     * @throws \App\Exceptions\TextExtractionException
     */
    public function extract(string $filePath): TextExtractionResult
    {
        $extractor = $this->createFor($filePath);

        return $extractor->extract($filePath);
    }

    /**
     * Check if a file extension is supported.
     */
    public function isSupported(string $extension): bool
    {
        $ext = strtolower($extension);

        return $this->pdfExtractor->supports($ext)
            || $this->imageExtractor->supports($ext)
            || $this->textExtractor->supports($ext);
    }

    /**
     * Get all supported extensions.
     *
     * @return array<string>
     */
    public function getSupportedExtensions(): array
    {
        return ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'txt'];
    }
}
