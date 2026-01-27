<?php

declare(strict_types=1);

use App\Exceptions\PdfParsingException;
use App\Services\PdfContent;
use App\Services\PdfParserService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->service = new PdfParserService;
});

describe('extractText', function () {
    it('throws exception for non-existent file', function () {
        expect(fn () => $this->service->extractText('/non/existent/file.pdf'))
            ->toThrow(PdfParsingException::class, 'PDF file not found');
    });

    it('throws exception for corrupt PDF', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'corrupt_pdf_');
        file_put_contents($tempFile, 'This is not a valid PDF content');

        try {
            expect(fn () => $this->service->extractText($tempFile))
                ->toThrow(PdfParsingException::class, 'Failed to parse PDF');
        } finally {
            unlink($tempFile);
        }
    });
});

describe('PdfContent', function () {
    it('provides access to full text', function () {
        $content = new PdfContent(
            fullText: 'This is the full text',
            textByPage: [1 => 'Page 1 text', 2 => 'Page 2 text'],
            pageCount: 2,
            metadata: ['title' => 'Test PDF'],
        );

        expect($content->fullText)->toBe('This is the full text');
        expect($content->pageCount)->toBe(2);
    });

    it('provides access to individual pages', function () {
        $content = new PdfContent(
            fullText: 'Full text',
            textByPage: [1 => 'Page 1', 2 => 'Page 2', 3 => 'Page 3'],
            pageCount: 3,
        );

        expect($content->getPage(1))->toBe('Page 1');
        expect($content->getPage(2))->toBe('Page 2');
        expect($content->getPage(3))->toBe('Page 3');
        expect($content->getPage(4))->toBeNull();
    });

    it('detects if PDF has text', function () {
        $withText = new PdfContent(
            fullText: 'Some content',
            textByPage: [1 => 'Some content'],
            pageCount: 1,
        );

        $withoutText = new PdfContent(
            fullText: '   ',
            textByPage: [],
            pageCount: 1,
        );

        expect($withText->hasText())->toBeTrue();
        expect($withoutText->hasText())->toBeFalse();
    });

    it('calculates word count', function () {
        $content = new PdfContent(
            fullText: 'One two three four five',
            textByPage: [1 => 'One two three four five'],
            pageCount: 1,
        );

        expect($content->getWordCount())->toBe(5);
    });

    it('calculates character count', function () {
        $content = new PdfContent(
            fullText: 'Hello World',
            textByPage: [1 => 'Hello World'],
            pageCount: 1,
        );

        expect($content->getCharacterCount())->toBe(11);
    });

    it('stores metadata', function () {
        $content = new PdfContent(
            fullText: 'Content',
            textByPage: [1 => 'Content'],
            pageCount: 1,
            metadata: [
                'title' => 'My Document',
                'author' => 'John Doe',
            ],
        );

        expect($content->metadata['title'])->toBe('My Document');
        expect($content->metadata['author'])->toBe('John Doe');
    });
});
