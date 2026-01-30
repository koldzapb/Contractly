<?php

declare(strict_types=1);

use App\Services\ContractComparisonService;
use App\Repositories\Contracts\ContractRepositoryInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(ContractRepositoryInterface::class);
    $this->service = new ContractComparisonService($this->repository);
});

describe('text similarity calculation', function () {
    it('returns 1.0 for identical short texts', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment shall be made within 30 days.',
            'Payment shall be made within 30 days.',
        );

        expect($similarity)->toBe(1.0);
    });

    it('returns 1.0 for identical texts with different case', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment shall be made within 30 days.',
            'PAYMENT SHALL BE MADE WITHIN 30 DAYS.',
        );

        expect($similarity)->toBe(1.0);
    });

    it('returns high similarity for very similar texts', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment shall be made within 30 days of invoice.',
            'Payment shall be made within 45 days of invoice.',
        );

        // Only "30" differs from "45", so similarity should be high
        expect($similarity)->toBeGreaterThan(0.8);
    });

    it('returns low similarity for very different texts', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment shall be made within 30 days.',
            'The party agrees to confidentiality terms.',
        );

        expect($similarity)->toBeLessThan(0.5);
    });

    it('returns 0.0 when one text is empty', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Some text here.',
            '',
        );

        expect($similarity)->toBe(0.0);
    });

    it('returns 1.0 when both texts are empty', function () {
        $similarity = $this->service->calculateTextSimilarity('', '');

        expect($similarity)->toBe(1.0);
    });

    it('normalizes whitespace before comparison', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment   shall   be   made.',
            'Payment shall be made.',
        );

        expect($similarity)->toBe(1.0);
    });

    it('uses Jaccard similarity for long texts', function () {
        // Create texts longer than 500 characters (with some unique variations)
        $textA = str_repeat('This is a long contract clause about payment terms and conditions for the vendor. ', 10);
        $textB = str_repeat('This is a long contract clause about payment terms and conditions for the vendor. ', 10);

        $similarity = $this->service->calculateTextSimilarity($textA, $textB);

        // Identical long texts should have very high similarity
        expect($similarity)->toBeGreaterThan(0.99);
    });

    it('calculates Jaccard similarity correctly for different long texts', function () {
        // Create different long texts
        $textA = str_repeat('Payment terms require immediate settlement upon delivery. ', 15);
        $textB = str_repeat('Confidentiality requirements mandate strict data protection. ', 15);

        $similarity = $this->service->calculateTextSimilarity($textA, $textB);

        // Very different texts should have low similarity
        expect($similarity)->toBeLessThan(0.3);
    });

    it('handles texts with special characters', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Payment: $1,000.00 (one thousand dollars)',
            'Payment: $1,000.00 (one thousand dollars)',
        );

        expect($similarity)->toBe(1.0);
    });

    it('handles unicode characters', function () {
        $similarity = $this->service->calculateTextSimilarity(
            'Paiement de 1 000 € sera effectué.',
            'Paiement de 1 000 € sera effectué.',
        );

        expect($similarity)->toBe(1.0);
    });
});

describe('edge cases', function () {
    it('handles very short texts', function () {
        $similarity = $this->service->calculateTextSimilarity('A', 'B');

        expect($similarity)->toBe(0.0);
    });

    it('handles texts with only numbers', function () {
        $similarity = $this->service->calculateTextSimilarity('12345', '12345');

        expect($similarity)->toBe(1.0);
    });

    it('handles texts with only whitespace', function () {
        $similarity = $this->service->calculateTextSimilarity('   ', '   ');

        // After normalization, both are empty
        expect($similarity)->toBe(1.0);
    });
});
