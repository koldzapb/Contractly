<?php

declare(strict_types=1);

namespace App\Enums;

enum FileType: string
{
    case PDF = 'pdf';
    case IMAGE = 'image';
    case TEXT = 'text';

    public function label(): string
    {
        return match ($this) {
            self::PDF => 'PDF Document',
            self::IMAGE => 'Image',
            self::TEXT => 'Plain Text',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PDF => 'document',
            self::IMAGE => 'photograph',
            self::TEXT => 'document-text',
        };
    }

    /**
     * Get the file type from a file extension.
     */
    public static function fromExtension(string $extension): ?self
    {
        $ext = strtolower($extension);

        return match ($ext) {
            'pdf' => self::PDF,
            'jpg', 'jpeg', 'png', 'webp', 'gif' => self::IMAGE,
            'txt' => self::TEXT,
            default => null,
        };
    }

    /**
     * Get the file type from a MIME type.
     */
    public static function fromMimeType(string $mimeType): ?self
    {
        $mime = strtolower($mimeType);

        return match (true) {
            $mime === 'application/pdf' => self::PDF,
            str_starts_with($mime, 'image/') => self::IMAGE,
            $mime === 'text/plain' => self::TEXT,
            default => null,
        };
    }

    /**
     * Get supported MIME types for this file type.
     *
     * @return array<string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::PDF => ['application/pdf'],
            self::IMAGE => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            self::TEXT => ['text/plain'],
        };
    }

    /**
     * Get supported extensions for this file type.
     *
     * @return array<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::PDF => ['pdf'],
            self::IMAGE => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            self::TEXT => ['txt'],
        };
    }
}
