<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

use App\Exceptions\TextExtractionException;

/**
 * Interface for text extraction services.
 */
interface TextExtractionInterface
{
    /**
     * Extract text from a file.
     *
     * @param  string                  $filePath Absolute path to the file
     * @throws TextExtractionException If extraction fails
     */
    public function extract(string $filePath): TextExtractionResult;

    /**
     * Check if this extractor supports the given file extension.
     */
    public function supports(string $extension): bool;
}
