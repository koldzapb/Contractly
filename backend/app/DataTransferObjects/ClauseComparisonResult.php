<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Models\ContractClause;

final readonly class ClauseComparisonResult
{
    /**
     * @param 'matched'|'only_in_a'|'only_in_b' $matchType
     */
    public function __construct(
        public string $matchType,
        public ?ContractClause $clauseA,
        public ?ContractClause $clauseB,
        public ?float $similarity = null,
    ) {}

    /**
     * Create a matched clause comparison.
     */
    public static function matched(
        ContractClause $clauseA,
        ContractClause $clauseB,
        float $similarity,
    ): self {
        return new self(
            matchType: 'matched',
            clauseA: $clauseA,
            clauseB: $clauseB,
            similarity: $similarity,
        );
    }

    /**
     * Create a clause that only exists in contract A.
     */
    public static function onlyInA(ContractClause $clause): self
    {
        return new self(
            matchType: 'only_in_a',
            clauseA: $clause,
            clauseB: null,
            similarity: null,
        );
    }

    /**
     * Create a clause that only exists in contract B.
     */
    public static function onlyInB(ContractClause $clause): self
    {
        return new self(
            matchType: 'only_in_b',
            clauseA: null,
            clauseB: $clause,
            similarity: null,
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
     * Check if clause only exists in contract A.
     */
    public function isOnlyInA(): bool
    {
        return $this->matchType === 'only_in_a';
    }

    /**
     * Check if clause only exists in contract B.
     */
    public function isOnlyInB(): bool
    {
        return $this->matchType === 'only_in_b';
    }

    /**
     * Check if risk levels differ between matched clauses.
     */
    public function hasRiskDifference(): bool
    {
        if (! $this->isMatched()) {
            return false;
        }

        return $this->clauseA->risk_level !== $this->clauseB->risk_level;
    }
}
