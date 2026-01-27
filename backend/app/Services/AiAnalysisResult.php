<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Data transfer object for AI analysis results.
 */
readonly class AiAnalysisResult
{
    /**
     * @param string                           $summary          Brief summary of the contract
     * @param string                           $overallRiskLevel Overall risk assessment (low, medium, high)
     * @param array<int, string>               $keyFindings      List of key findings
     * @param array<int, array<string, mixed>> $clauses          Extracted clauses
     * @param array<int, array<string, mixed>> $deadlines        Extracted deadlines
     * @param string                           $model            AI model used
     * @param int                              $tokensUsed       Total tokens used
     * @param int                              $processingTimeMs Processing time in milliseconds
     * @param array<string, mixed>             $rawResponse      Raw API response
     */
    public function __construct(
        public string $summary,
        public string $overallRiskLevel,
        public array $keyFindings,
        public array $clauses,
        public array $deadlines,
        public string $model,
        public int $tokensUsed,
        public int $processingTimeMs,
        public array $rawResponse = [],
    ) {}

    /**
     * Get the number of clauses extracted.
     */
    public function getClauseCount(): int
    {
        return count($this->clauses);
    }

    /**
     * Get the number of deadlines extracted.
     */
    public function getDeadlineCount(): int
    {
        return count($this->deadlines);
    }

    /**
     * Get high-risk clauses.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getHighRiskClauses(): array
    {
        return array_filter(
            $this->clauses,
            fn (array $clause) => ($clause['risk_level'] ?? '') === 'high',
        );
    }
}
