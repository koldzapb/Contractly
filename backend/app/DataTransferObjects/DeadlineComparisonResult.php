<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Models\ContractDeadline;

final readonly class DeadlineComparisonResult
{
    /**
     * @param 'matched'|'only_in_a'|'only_in_b' $matchType
     */
    public function __construct(
        public string $matchType,
        public ?ContractDeadline $deadlineA,
        public ?ContractDeadline $deadlineB,
        public ?int $daysDifference = null,
    ) {}

    /**
     * Create a matched deadline comparison.
     */
    public static function matched(
        ContractDeadline $deadlineA,
        ContractDeadline $deadlineB,
    ): self {
        $daysDifference = null;

        if ($deadlineA->deadline_date !== null && $deadlineB->deadline_date !== null) {
            $daysDifference = (int) $deadlineA->deadline_date->diffInDays($deadlineB->deadline_date, false);
        }

        return new self(
            matchType: 'matched',
            deadlineA: $deadlineA,
            deadlineB: $deadlineB,
            daysDifference: $daysDifference,
        );
    }

    /**
     * Create a deadline that only exists in contract A.
     */
    public static function onlyInA(ContractDeadline $deadline): self
    {
        return new self(
            matchType: 'only_in_a',
            deadlineA: $deadline,
            deadlineB: null,
            daysDifference: null,
        );
    }

    /**
     * Create a deadline that only exists in contract B.
     */
    public static function onlyInB(ContractDeadline $deadline): self
    {
        return new self(
            matchType: 'only_in_b',
            deadlineA: null,
            deadlineB: $deadline,
            daysDifference: null,
        );
    }

    /**
     * Check if this is a matched comparison.
     */
    public function isMatched(): bool
    {
        return $this->matchType === 'matched';
    }

    /**
     * Check if deadline only exists in contract A.
     */
    public function isOnlyInA(): bool
    {
        return $this->matchType === 'only_in_a';
    }

    /**
     * Check if deadline only exists in contract B.
     */
    public function isOnlyInB(): bool
    {
        return $this->matchType === 'only_in_b';
    }

    /**
     * Check if dates differ between matched deadlines.
     */
    public function hasDateDifference(): bool
    {
        return $this->daysDifference !== null && $this->daysDifference !== 0;
    }
}
