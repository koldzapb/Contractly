<?php

declare(strict_types=1);

namespace App\Services\TextExtraction;

use App\Exceptions\TextExtractionException;
use App\Services\ClaudeVisionService;

/**
 * Text extractor for image files using Claude Vision API.
 */
class ImageTextExtractor implements TextExtractionInterface
{
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private ClaudeVisionService $visionService,
    ) {}

    /**
     * @throws TextExtractionException
     */
    public function extract(string $filePath): TextExtractionResult
    {
        if (! file_exists($filePath)) {
            throw new TextExtractionException("Image file not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! $this->supports($extension)) {
            throw new TextExtractionException("Unsupported image format: {$extension}");
        }

        $text = $this->visionService->extractText($filePath);

        $mimeType = mime_content_type($filePath);
        $imageInfo = @getimagesize($filePath);

        return TextExtractionResult::fromSinglePage($text, [
            'mime_type' => $mimeType ?: 'unknown',
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'file_size' => filesize($filePath),
            'extraction_method' => 'claude_vision',
        ]);
    }

    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), self::SUPPORTED_EXTENSIONS, true);
    }
}
