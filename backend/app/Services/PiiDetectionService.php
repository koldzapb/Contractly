<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PiiType;
use Illuminate\Support\Str;

class PiiDetectionService
{
    /**
     * Context length around detected PII (characters on each side).
     */
    private const CONTEXT_LENGTH = 30;

    /**
     * Detect PII in the given text.
     */
    public function detectPii(string $text): PiiDetectionResult
    {
        if (empty(trim($text))) {
            return PiiDetectionResult::empty($text);
        }

        $items = [];

        // Detect PII for each type, ordered by sensitivity
        foreach (PiiType::bySensitivity() as $type) {
            $typeItems = $this->detectByType($text, $type);
            $items = array_merge($items, $typeItems);
        }

        // Remove overlapping detections (keep highest sensitivity)
        $items = $this->removeOverlaps($items);

        // Sort by position
        usort($items, fn ($a, $b) => $a->startPosition <=> $b->startPosition);

        return PiiDetectionResult::fromItems($items, $text);
    }

    /**
     * Detect PII of a specific type in the text.
     *
     * @return array<int, DetectedPii>
     */
    private function detectByType(string $text, PiiType $type): array
    {
        $items = [];
        $pattern = $type->pattern();

        if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE) === false) {
            return [];
        }

        foreach ($matches[0] as $match) {
            $value = $match[0];
            $position = $match[1];

            // Skip false positives
            if (! $this->isValidMatch($value, $type, $text, $position)) {
                continue;
            }

            $items[] = new DetectedPii(
                id: Str::uuid()->toString(),
                type: $type,
                value: $value,
                redactedValue: $type->redactedPlaceholder(),
                startPosition: $position,
                endPosition: $position + strlen($value),
                context: $this->extractContext($text, $position, strlen($value)),
            );
        }

        return $items;
    }

    /**
     * Validate if a match is a genuine PII detection.
     */
    private function isValidMatch(string $value, PiiType $type, string $text, int $position): bool
    {
        return match ($type) {
            PiiType::SSN => $this->isValidSsn($value),
            PiiType::CREDIT_CARD => $this->isValidCreditCard($value),
            PiiType::PHONE => $this->isValidPhone($value),
            PiiType::BANK_ROUTING => $this->isValidBankRouting($value, $text, $position),
            PiiType::BANK_ACCOUNT => $this->isValidBankAccount($value, $text, $position),
            PiiType::EMAIL => $this->isValidEmail($value),
        };
    }

    /**
     * Validate SSN format and basic checksum.
     */
    private function isValidSsn(string $value): bool
    {
        // Remove dashes
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 9) {
            return false;
        }

        // SSN cannot start with 000, 666, or 900-999
        $area = (int) substr($digits, 0, 3);
        if ($area === 0 || $area === 666 || $area >= 900) {
            return false;
        }

        // Group cannot be 00
        $group = (int) substr($digits, 3, 2);
        if ($group === 0) {
            return false;
        }

        // Serial cannot be 0000
        $serial = (int) substr($digits, 5, 4);
        if ($serial === 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate credit card number using Luhn algorithm.
     */
    private function isValidCreditCard(string $value): bool
    {
        // Remove spaces and dashes
        $digits = preg_replace('/[\s\-]/', '', $value);

        if (! preg_match('/^\d{13,19}$/', $digits)) {
            return false;
        }

        // Luhn algorithm
        $sum = 0;
        $length = strlen($digits);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $digits[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Validate phone number format.
     */
    private function isValidPhone(string $value): bool
    {
        // Remove all non-digits
        $digits = preg_replace('/\D/', '', $value);

        // US phone numbers: 10 or 11 digits (with country code)
        return strlen($digits) === 10 || (strlen($digits) === 11 && $digits[0] === '1');
    }

    /**
     * Validate bank routing number (9 digits with checksum).
     */
    private function isValidBankRouting(string $value, string $text, int $position): bool
    {
        if (strlen($value) !== 9) {
            return false;
        }

        // Check if context suggests this is a routing number
        $contextBefore = strtolower(substr($text, max(0, $position - 50), 50));
        $routingKeywords = ['routing', 'aba', 'transit'];

        $hasContext = false;
        foreach ($routingKeywords as $keyword) {
            if (str_contains($contextBefore, $keyword)) {
                $hasContext = true;
                break;
            }
        }

        if (! $hasContext) {
            return false;
        }

        // ABA routing number checksum
        $digits = str_split($value);
        $checksum = (
            3 * ((int) $digits[0] + (int) $digits[3] + (int) $digits[6]) +
            7 * ((int) $digits[1] + (int) $digits[4] + (int) $digits[7]) +
            1 * ((int) $digits[2] + (int) $digits[5] + (int) $digits[8])
        );

        return $checksum % 10 === 0;
    }

    /**
     * Validate bank account number.
     */
    private function isValidBankAccount(string $value, string $text, int $position): bool
    {
        // Check if context suggests this is an account number
        $contextBefore = strtolower(substr($text, max(0, $position - 50), 50));
        $accountKeywords = ['account', 'acct', 'a/c'];

        foreach ($accountKeywords as $keyword) {
            if (str_contains($contextBefore, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate email address.
     */
    private function isValidEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Extract context around a match.
     */
    private function extractContext(string $text, int $position, int $length): string
    {
        $start = max(0, $position - self::CONTEXT_LENGTH);
        $end = min(strlen($text), $position + $length + self::CONTEXT_LENGTH);

        $context = substr($text, $start, $end - $start);

        // Add ellipsis if truncated
        if ($start > 0) {
            $context = '...'.$context;
        }
        if ($end < strlen($text)) {
            $context = $context.'...';
        }

        // Normalize whitespace
        $context = preg_replace('/\s+/', ' ', $context);

        return trim($context);
    }

    /**
     * Remove overlapping detections, keeping higher sensitivity items.
     *
     * @param array<int, DetectedPii> $items
     *
     * @return array<int, DetectedPii>
     */
    private function removeOverlaps(array $items): array
    {
        if (count($items) <= 1) {
            return $items;
        }

        // Sort by sensitivity (descending) then by position
        usort($items, function ($a, $b) {
            $sensA = $a->type->sensitivityLevel();
            $sensB = $b->type->sensitivityLevel();

            if ($sensA !== $sensB) {
                return $sensB <=> $sensA;
            }

            return $a->startPosition <=> $b->startPosition;
        });

        $kept = [];

        foreach ($items as $item) {
            $overlaps = false;

            foreach ($kept as $existing) {
                // Check for overlap
                if (
                    $item->startPosition < $existing->endPosition &&
                    $item->endPosition > $existing->startPosition
                ) {
                    $overlaps = true;
                    break;
                }
            }

            if (! $overlaps) {
                $kept[] = $item;
            }
        }

        return $kept;
    }

    /**
     * Apply redactions to text.
     *
     * @param array<int, string> $itemIds IDs of items to redact
     */
    public function applyRedactions(PiiDetectionResult $detection, array $itemIds): string
    {
        return $detection->applyRedactions($itemIds);
    }
}
