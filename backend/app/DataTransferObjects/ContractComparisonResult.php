<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Models\Contract;

final readonly class ContractComparisonResult
{
    /**
     * @param array<ClauseComparisonResult> $clauseComparisons
     * @param array<DeadlineComparisonResult> $deadlineComparisons
     */
    public function __construct(
        public Contract $contractA,
        public Contract $contractB,
        public float $similarityScore,
        public array $clauseComparisons,
        public array $deadlineComparisons,
    ) {}

    /**
     * Get matched clause comparisons.
     *
     * @return array<ClauseComparisonResult>
     */
    public function getMatchedClauses(): array
    {
        return array_filter(
            $this->clauseComparisons,
            fn (ClauseComparisonResult $c) => $c->isMatched()
        );
    }

    /**
     * Get clauses only in contract A.
     *
     * @return array<ClauseComparisonResult>
     */
    public function getClausesOnlyInA(): array
    {
        return array_filter(
            $this->clauseComparisons,
            fn (ClauseComparisonResult $c) => $c->isOnlyInA()
        );
    }

    /**
     * Get clauses only in contract B.
     *
     * @return array<ClauseComparisonResult>
     */
    public function getClausesOnlyInB(): array
    {
        return array_filter(
            $this->clauseComparisons,
            fn (ClauseComparisonResult $c) => $c->isOnlyInB()
        );
    }

    /**
     * Get matched deadline comparisons.
     *
     * @return array<DeadlineComparisonResult>
     */
    public function getMatchedDeadlines(): array
    {
        return array_filter(
            $this->deadlineComparisons,
            fn (DeadlineComparisonResult $d) => $d->isMatched()
        );
    }

    /**
     * Get deadlines only in contract A.
     *
     * @return array<DeadlineComparisonResult>
     */
    public function getDeadlinesOnlyInA(): array
    {
        return array_filter(
            $this->deadlineComparisons,
            fn (DeadlineComparisonResult $d) => $d->isOnlyInA()
        );
    }

    /**
     * Get deadlines only in contract B.
     *
     * @return array<DeadlineComparisonResult>
     */
    public function getDeadlinesOnlyInB(): array
    {
        return array_filter(
            $this->deadlineComparisons,
            fn (DeadlineComparisonResult $d) => $d->isOnlyInB()
        );
    }

    /**
     * Check if overall risk level changed between contracts.
     */
    public function hasRiskChange(): bool
    {
        return $this->contractA->overall_risk_level !== $this->contractB->overall_risk_level;
    }

    /**
     * Get comparison statistics.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $totalClausesA = count(array_filter(
            $this->clauseComparisons,
            fn (ClauseComparisonResult $c) => $c->clauseA !== null
        ));

        $totalClausesB = count(array_filter(
            $this->clauseComparisons,
            fn (ClauseComparisonResult $c) => $c->clauseB !== null
        ));

        $matchedClauses = count($this->getMatchedClauses());
        $clausesOnlyInA = count($this->getClausesOnlyInA());
        $clausesOnlyInB = count($this->getClausesOnlyInB());

        $totalDeadlinesA = count(array_filter(
            $this->deadlineComparisons,
            fn (DeadlineComparisonResult $d) => $d->deadlineA !== null
        ));

        $totalDeadlinesB = count(array_filter(
            $this->deadlineComparisons,
            fn (DeadlineComparisonResult $d) => $d->deadlineB !== null
        ));

        $matchedDeadlines = count($this->getMatchedDeadlines());
        $deadlinesOnlyInA = count($this->getDeadlinesOnlyInA());
        $deadlinesOnlyInB = count($this->getDeadlinesOnlyInB());

        return [
            'total_clauses_a' => $totalClausesA,
            'total_clauses_b' => $totalClausesB,
            'matched_clauses' => $matchedClauses,
            'clauses_only_in_a' => $clausesOnlyInA,
            'clauses_only_in_b' => $clausesOnlyInB,
            'total_deadlines_a' => $totalDeadlinesA,
            'total_deadlines_b' => $totalDeadlinesB,
            'matched_deadlines' => $matchedDeadlines,
            'deadlines_only_in_a' => $deadlinesOnlyInA,
            'deadlines_only_in_b' => $deadlinesOnlyInB,
        ];
    }
}
