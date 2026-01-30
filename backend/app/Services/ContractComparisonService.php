<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\ClauseComparisonResult;
use App\DataTransferObjects\ContractComparisonResult;
use App\DataTransferObjects\DeadlineComparisonResult;
use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ContractComparisonService
{
    private const float MIN_SIMILARITY_THRESHOLD = 0.30;

    private const int SHORT_TEXT_THRESHOLD = 500;

    public function __construct(
        private ContractRepositoryInterface $contracts,
    ) {}

    /**
     * Compare two contracts.
     *
     * @throws \InvalidArgumentException
     */
    public function compare(string $contractIdA, string $contractIdB, User $user): ContractComparisonResult
    {
        // Validate that contracts are different
        if ($contractIdA === $contractIdB) {
            throw new \InvalidArgumentException('Cannot compare a contract with itself.');
        }

        // Load contracts with all relations
        $contractA = $this->contracts->findForUser($contractIdA, $user);
        $contractB = $this->contracts->findForUser($contractIdB, $user);

        if ($contractA === null || $contractB === null) {
            throw new \InvalidArgumentException('One or both contracts not found.');
        }

        // Validate both contracts are completed and analyzed
        if ($contractA->status !== ContractStatus::COMPLETED || $contractB->status !== ContractStatus::COMPLETED) {
            throw new \InvalidArgumentException('Both contracts must be fully analyzed.');
        }

        // Load analysis relationships
        $contractA->load(['analysis.clauses', 'analysis.deadlines']);
        $contractB->load(['analysis.clauses', 'analysis.deadlines']);

        if ($contractA->analysis === null || $contractB->analysis === null) {
            throw new \InvalidArgumentException('Both contracts must have analysis data.');
        }

        // Compare clauses
        $clauseComparisons = $this->compareClauses(
            $contractA->analysis->clauses,
            $contractB->analysis->clauses,
        );

        // Compare deadlines
        $deadlineComparisons = $this->compareDeadlines(
            $contractA->analysis->deadlines,
            $contractB->analysis->deadlines,
        );

        // Calculate overall similarity
        $similarityScore = $this->calculateOverallSimilarity($clauseComparisons);

        return new ContractComparisonResult(
            contractA: $contractA,
            contractB: $contractB,
            similarityScore: $similarityScore,
            clauseComparisons: $clauseComparisons,
            deadlineComparisons: $deadlineComparisons,
        );
    }

    /**
     * Compare clauses between two contracts.
     *
     * @param Collection<int, ContractClause> $clausesA
     * @param Collection<int, ContractClause> $clausesB
     * @return array<ClauseComparisonResult>
     */
    private function compareClauses(Collection $clausesA, Collection $clausesB): array
    {
        $results = [];
        $matchedB = [];

        // Group clauses by type for efficient matching
        $clausesByTypeB = $clausesB->groupBy(fn (ContractClause $c) => $c->clause_type->value);

        foreach ($clausesA as $clauseA) {
            $typeKey = $clauseA->clause_type->value;
            $candidates = $clausesByTypeB->get($typeKey, collect());

            $bestMatch = null;
            $bestSimilarity = 0.0;

            foreach ($candidates as $clauseB) {
                // Skip if already matched
                if (in_array($clauseB->id, $matchedB, true)) {
                    continue;
                }

                $similarity = $this->calculateTextSimilarity(
                    $clauseA->original_text,
                    $clauseB->original_text,
                );

                if ($similarity > $bestSimilarity && $similarity >= self::MIN_SIMILARITY_THRESHOLD) {
                    $bestMatch = $clauseB;
                    $bestSimilarity = $similarity;
                }
            }

            if ($bestMatch !== null) {
                $results[] = ClauseComparisonResult::matched($clauseA, $bestMatch, $bestSimilarity);
                $matchedB[] = $bestMatch->id;
            } else {
                $results[] = ClauseComparisonResult::onlyInA($clauseA);
            }
        }

        // Add unmatched clauses from B
        foreach ($clausesB as $clauseB) {
            if (! in_array($clauseB->id, $matchedB, true)) {
                $results[] = ClauseComparisonResult::onlyInB($clauseB);
            }
        }

        return $results;
    }

    /**
     * Compare deadlines between two contracts.
     *
     * @param Collection<int, ContractDeadline> $deadlinesA
     * @param Collection<int, ContractDeadline> $deadlinesB
     * @return array<DeadlineComparisonResult>
     */
    private function compareDeadlines(Collection $deadlinesA, Collection $deadlinesB): array
    {
        $results = [];
        $matchedB = [];

        // Group deadlines by type for efficient matching
        $deadlinesByTypeB = $deadlinesB->groupBy(fn (ContractDeadline $d) => $d->deadline_type->value);

        foreach ($deadlinesA as $deadlineA) {
            $typeKey = $deadlineA->deadline_type->value;
            $candidates = $deadlinesByTypeB->get($typeKey, collect());

            $bestMatch = null;
            $bestSimilarity = 0.0;

            foreach ($candidates as $deadlineB) {
                // Skip if already matched
                if (in_array($deadlineB->id, $matchedB, true)) {
                    continue;
                }

                // Match by title similarity
                $similarity = $this->calculateTextSimilarity(
                    $deadlineA->title,
                    $deadlineB->title,
                );

                if ($similarity > $bestSimilarity && $similarity >= self::MIN_SIMILARITY_THRESHOLD) {
                    $bestMatch = $deadlineB;
                    $bestSimilarity = $similarity;
                }
            }

            if ($bestMatch !== null) {
                $results[] = DeadlineComparisonResult::matched($deadlineA, $bestMatch);
                $matchedB[] = $bestMatch->id;
            } else {
                $results[] = DeadlineComparisonResult::onlyInA($deadlineA);
            }
        }

        // Add unmatched deadlines from B
        foreach ($deadlinesB as $deadlineB) {
            if (! in_array($deadlineB->id, $matchedB, true)) {
                $results[] = DeadlineComparisonResult::onlyInB($deadlineB);
            }
        }

        return $results;
    }

    /**
     * Calculate text similarity between two strings.
     */
    public function calculateTextSimilarity(string $textA, string $textB): float
    {
        // Normalize texts
        $textA = $this->normalizeText($textA);
        $textB = $this->normalizeText($textB);

        if ($textA === '' && $textB === '') {
            return 1.0;
        }

        if ($textA === '' || $textB === '') {
            return 0.0;
        }

        // Use Levenshtein for short text, Jaccard for long text
        $maxLength = max(strlen($textA), strlen($textB));

        if ($maxLength < self::SHORT_TEXT_THRESHOLD) {
            return $this->calculateLevenshteinSimilarity($textA, $textB);
        }

        return $this->calculateJaccardSimilarity($textA, $textB);
    }

    /**
     * Calculate normalized Levenshtein similarity.
     */
    private function calculateLevenshteinSimilarity(string $textA, string $textB): float
    {
        $maxLength = max(strlen($textA), strlen($textB));

        if ($maxLength === 0) {
            return 1.0;
        }

        $distance = levenshtein($textA, $textB);

        return 1.0 - ($distance / $maxLength);
    }

    /**
     * Calculate Jaccard similarity (word overlap).
     */
    private function calculateJaccardSimilarity(string $textA, string $textB): float
    {
        $wordsA = $this->extractWords($textA);
        $wordsB = $this->extractWords($textB);

        if (empty($wordsA) && empty($wordsB)) {
            return 1.0;
        }

        if (empty($wordsA) || empty($wordsB)) {
            return 0.0;
        }

        $intersection = count(array_intersect($wordsA, $wordsB));
        $union = count(array_unique(array_merge($wordsA, $wordsB)));

        // Union is always > 0 here due to the early return checks above
        return $intersection / $union;
    }

    /**
     * Normalize text for comparison.
     */
    private function normalizeText(string $text): string
    {
        // Convert to lowercase
        $text = mb_strtolower($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        return trim($text ?? '');
    }

    /**
     * Extract words from text for Jaccard similarity.
     *
     * @return array<string>
     */
    private function extractWords(string $text): array
    {
        // Split on non-word characters
        $words = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Filter out very short words (articles, etc.)
        return array_filter(
            $words ?: [],
            fn (string $word) => strlen($word) > 2
        );
    }

    /**
     * Calculate overall similarity score based on clause comparisons.
     *
     * @param array<ClauseComparisonResult> $clauseComparisons
     */
    private function calculateOverallSimilarity(array $clauseComparisons): float
    {
        $matched = array_filter($clauseComparisons, fn (ClauseComparisonResult $c) => $c->isMatched());
        $totalA = count(array_filter($clauseComparisons, fn (ClauseComparisonResult $c) => $c->clauseA !== null));
        $totalB = count(array_filter($clauseComparisons, fn (ClauseComparisonResult $c) => $c->clauseB !== null));

        $maxTotal = max($totalA, $totalB);

        if ($maxTotal === 0) {
            return 1.0; // Both have no clauses
        }

        $matchedCount = count($matched);

        // Calculate coverage
        $coverage = $matchedCount / $maxTotal;

        // Calculate average match quality
        $avgQuality = 0.0;
        if ($matchedCount > 0) {
            $totalSimilarity = array_sum(array_map(
                fn (ClauseComparisonResult $c) => $c->similarity ?? 0.0,
                $matched
            ));
            $avgQuality = $totalSimilarity / $matchedCount;
        }

        // Overall similarity: average of coverage and quality
        return ($coverage + $avgQuality) / 2;
    }
}
