<?php

declare(strict_types=1);

use App\Enums\FileType;

describe('FileType enum', function () {
    it('has correct values', function () {
        expect(FileType::PDF->value)->toBe('pdf');
        expect(FileType::IMAGE->value)->toBe('image');
        expect(FileType::TEXT->value)->toBe('text');
    });

    it('has correct labels', function () {
        expect(FileType::PDF->label())->toBe('PDF Document');
        expect(FileType::IMAGE->label())->toBe('Image');
        expect(FileType::TEXT->label())->toBe('Plain Text');
    });

    it('creates from extension', function () {
        expect(FileType::fromExtension('pdf'))->toBe(FileType::PDF);
        expect(FileType::fromExtension('jpg'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('jpeg'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('png'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('webp'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('gif'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('txt'))->toBe(FileType::TEXT);
        expect(FileType::fromExtension('docx'))->toBeNull();
    });

    it('handles case insensitive extensions', function () {
        expect(FileType::fromExtension('PDF'))->toBe(FileType::PDF);
        expect(FileType::fromExtension('JPG'))->toBe(FileType::IMAGE);
        expect(FileType::fromExtension('TXT'))->toBe(FileType::TEXT);
    });

    it('creates from mime type', function () {
        expect(FileType::fromMimeType('application/pdf'))->toBe(FileType::PDF);
        expect(FileType::fromMimeType('image/jpeg'))->toBe(FileType::IMAGE);
        expect(FileType::fromMimeType('image/png'))->toBe(FileType::IMAGE);
        expect(FileType::fromMimeType('image/webp'))->toBe(FileType::IMAGE);
        expect(FileType::fromMimeType('image/gif'))->toBe(FileType::IMAGE);
        expect(FileType::fromMimeType('text/plain'))->toBe(FileType::TEXT);
        expect(FileType::fromMimeType('application/msword'))->toBeNull();
    });

    it('returns correct mime types', function () {
        expect(FileType::PDF->mimeTypes())->toBe(['application/pdf']);
        expect(FileType::IMAGE->mimeTypes())->toContain('image/jpeg');
        expect(FileType::IMAGE->mimeTypes())->toContain('image/png');
        expect(FileType::TEXT->mimeTypes())->toBe(['text/plain']);
    });

    it('returns correct extensions', function () {
        expect(FileType::PDF->extensions())->toBe(['pdf']);
        expect(FileType::IMAGE->extensions())->toContain('jpg');
        expect(FileType::IMAGE->extensions())->toContain('jpeg');
        expect(FileType::IMAGE->extensions())->toContain('png');
        expect(FileType::TEXT->extensions())->toBe(['txt']);
    });
});
