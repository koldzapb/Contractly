<?php

declare(strict_types=1);

use App\Exceptions\TextExtractionException;
use App\Services\TextExtraction\PlainTextExtractor;

describe('PlainTextExtractor', function () {
    beforeEach(function () {
        $this->extractor = new PlainTextExtractor();
        $this->tempDir = sys_get_temp_dir().'/contractly_tests';
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    });

    afterEach(function () {
        // Clean up temp files
        array_map('unlink', glob($this->tempDir.'/*'));
    });

    it('extracts text from a plain text file', function () {
        $content = "This is a sample contract.\nWith multiple lines.\n\nAnd paragraphs.";
        $filePath = $this->tempDir.'/test.txt';
        file_put_contents($filePath, $content);

        $result = $this->extractor->extract($filePath);

        expect($result->hasText())->toBeTrue();
        expect($result->fullText)->toContain('sample contract');
        expect($result->pageCount)->toBe(1);
    });

    it('handles empty files', function () {
        $filePath = $this->tempDir.'/empty.txt';
        file_put_contents($filePath, '');

        $result = $this->extractor->extract($filePath);

        expect($result->hasText())->toBeFalse();
        expect($result->fullText)->toBe('');
    });

    it('normalizes line endings', function () {
        $content = "Line 1\r\nLine 2\rLine 3\nLine 4";
        $filePath = $this->tempDir.'/mixed.txt';
        file_put_contents($filePath, $content);

        $result = $this->extractor->extract($filePath);

        expect($result->fullText)->not->toContain("\r\n");
        expect($result->fullText)->not->toContain("\r");
        expect($result->fullText)->toContain("Line 1\nLine 2\nLine 3\nLine 4");
    });

    it('throws exception for non-existent file', function () {
        $this->extractor->extract('/nonexistent/file.txt');
    })->throws(TextExtractionException::class);

    it('supports txt extension', function () {
        expect($this->extractor->supports('txt'))->toBeTrue();
        expect($this->extractor->supports('TXT'))->toBeTrue();
        expect($this->extractor->supports('pdf'))->toBeFalse();
        expect($this->extractor->supports('jpg'))->toBeFalse();
    });

    it('includes metadata', function () {
        $content = 'Test content';
        $filePath = $this->tempDir.'/meta.txt';
        file_put_contents($filePath, $content);

        $result = $this->extractor->extract($filePath);

        expect($result->metadata)->toHaveKey('file_size');
        expect($result->metadata['file_size'])->toBeGreaterThan(0);
    });
});
