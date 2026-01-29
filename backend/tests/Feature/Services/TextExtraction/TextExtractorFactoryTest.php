<?php

declare(strict_types=1);

use App\Exceptions\UnsupportedFileTypeException;
use App\Services\TextExtraction\ImageTextExtractor;
use App\Services\TextExtraction\PdfTextExtractor;
use App\Services\TextExtraction\PlainTextExtractor;
use App\Services\TextExtraction\TextExtractorFactory;

describe('TextExtractorFactory', function () {
    beforeEach(function () {
        $this->factory = app(TextExtractorFactory::class);
    });

    it('returns PdfTextExtractor for pdf files', function () {
        $extractor = $this->factory->createForExtension('pdf');
        expect($extractor)->toBeInstanceOf(PdfTextExtractor::class);
    });

    it('returns PlainTextExtractor for txt files', function () {
        $extractor = $this->factory->createForExtension('txt');
        expect($extractor)->toBeInstanceOf(PlainTextExtractor::class);
    });

    it('returns ImageTextExtractor for image files', function () {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            $extractor = $this->factory->createForExtension($ext);
            expect($extractor)->toBeInstanceOf(ImageTextExtractor::class);
        }
    });

    it('handles case insensitive extensions', function () {
        expect($this->factory->createForExtension('PDF'))->toBeInstanceOf(PdfTextExtractor::class);
        expect($this->factory->createForExtension('TXT'))->toBeInstanceOf(PlainTextExtractor::class);
        expect($this->factory->createForExtension('JPG'))->toBeInstanceOf(ImageTextExtractor::class);
    });

    it('throws exception for unsupported extensions', function () {
        $this->factory->createForExtension('docx');
    })->throws(UnsupportedFileTypeException::class);

    it('creates extractor from file path', function () {
        $extractor = $this->factory->createFor('/path/to/file.pdf');
        expect($extractor)->toBeInstanceOf(PdfTextExtractor::class);

        $extractor = $this->factory->createFor('/path/to/file.txt');
        expect($extractor)->toBeInstanceOf(PlainTextExtractor::class);

        $extractor = $this->factory->createFor('/path/to/file.png');
        expect($extractor)->toBeInstanceOf(ImageTextExtractor::class);
    });

    it('checks if extension is supported', function () {
        expect($this->factory->isSupported('pdf'))->toBeTrue();
        expect($this->factory->isSupported('txt'))->toBeTrue();
        expect($this->factory->isSupported('jpg'))->toBeTrue();
        expect($this->factory->isSupported('jpeg'))->toBeTrue();
        expect($this->factory->isSupported('png'))->toBeTrue();
        expect($this->factory->isSupported('webp'))->toBeTrue();
        expect($this->factory->isSupported('gif'))->toBeTrue();
        expect($this->factory->isSupported('docx'))->toBeFalse();
        expect($this->factory->isSupported('xlsx'))->toBeFalse();
    });

    it('returns all supported extensions', function () {
        $extensions = $this->factory->getSupportedExtensions();

        expect($extensions)->toContain('pdf');
        expect($extensions)->toContain('txt');
        expect($extensions)->toContain('jpg');
        expect($extensions)->toContain('jpeg');
        expect($extensions)->toContain('png');
        expect($extensions)->toContain('webp');
        expect($extensions)->toContain('gif');
    });
});
