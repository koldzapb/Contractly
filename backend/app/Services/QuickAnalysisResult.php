<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Data transfer object for quick (ephemeral) analysis results.
 */
readonly class QuickAnalysisResult
{
    /**
     * @param string                           $title            Display title for the analysis
     * @param string                           $summary          Brief summary of the contract
     * @param string                           $overallRiskLevel Overall risk assessment (low, medium, high)
     * @param array<int, string>               $keyFindings      List of key findings
     * @param array<int, array<string, mixed>> $clauses          Extracted clauses
     * @param array<int, array<string, mixed>> $deadlines        Extracted deadlines
     * @param int                              $tokensUsed       Total tokens used
     * @param int                              $processingTimeMs Processing time in milliseconds
     * @param bool                             $piiWarning       Whether PII was detected
     */
    public function __construct(
        public string $title,
        public string $summary,
        public string $overallRiskLevel,
        public array $keyFindings,
        public array $clauses,
        public array $deadlines,
        public int $tokensUsed,
        public int $processingTimeMs,
        public bool $piiWarning = false,
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
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'summary' => $this->summary,
            'overall_risk_level' => $this->overallRiskLevel,
            'key_findings' => $this->keyFindings,
            'clauses' => $this->formatClauses(),
            'deadlines' => $this->formatDeadlines(),
            'tokens_used' => $this->tokensUsed,
            'processing_time_ms' => $this->processingTimeMs,
            'pii_warning' => $this->piiWarning,
        ];
    }

    /**
     * Format clauses with labels.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatClauses(): array
    {
        return array_map(function (array $clause, int $index): array {
            $clauseType = $clause['clause_type'] ?? 'other';

            return [
                'id' => (string) ($index + 1),
                'clause_type' => $clauseType,
                'clause_type_label' => $this->getClauseTypeLabel($clauseType),
                'original_text' => $clause['original_text'] ?? '',
                'plain_explanation' => $clause['plain_explanation'] ?? '',
                'risk_level' => $clause['risk_level'] ?? 'low',
                'risk_reason' => $clause['risk_reason'] ?? null,
                'page_number' => $clause['page_number'] ?? null,
            ];
        }, $this->clauses, array_keys($this->clauses));
    }

    /**
     * Format deadlines with labels.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatDeadlines(): array
    {
        return array_map(function (array $deadline, int $index): array {
            $deadlineType = $deadline['deadline_type'] ?? 'other';

            return [
                'id' => (string) ($index + 1),
                'deadline_type' => $deadlineType,
                'deadline_type_label' => $this->getDeadlineTypeLabel($deadlineType),
                'title' => $deadline['title'] ?? '',
                'description' => $deadline['description'] ?? null,
                'deadline_date' => $deadline['deadline_date'] ?? null,
                'source_text' => $deadline['source_text'] ?? null,
                'is_recurring' => $deadline['is_recurring'] ?? false,
                'recurrence_pattern' => $deadline['recurrence_pattern'] ?? null,
            ];
        }, $this->deadlines, array_keys($this->deadlines));
    }

    /**
     * Get human-readable label for clause type.
     */
    private function getClauseTypeLabel(string $type): string
    {
        return match ($type) {
            'payment', 'payment_terms' => 'Payment Terms',
            'termination' => 'Termination',
            'liability' => 'Liability',
            'penalty' => 'Penalty',
            'auto_renewal' => 'Auto Renewal',
            'non_compete' => 'Non-Compete',
            'confidentiality' => 'Confidentiality',
            'indemnification' => 'Indemnification',
            'dispute_resolution' => 'Dispute Resolution',
            'intellectual_property' => 'Intellectual Property',
            'warranty' => 'Warranty',
            'force_majeure' => 'Force Majeure',
            'governing_law' => 'Governing Law',
            'assignment' => 'Assignment',
            'amendment' => 'Amendment',
            default => 'Other',
        };
    }

    /**
     * Get human-readable label for deadline type.
     */
    private function getDeadlineTypeLabel(string $type): string
    {
        return match ($type) {
            'payment', 'payment_due' => 'Payment Due',
            'renewal', 'renewal_date' => 'Renewal Date',
            'termination_notice' => 'Termination Notice',
            'delivery', 'delivery_date' => 'Delivery Date',
            'review_period', 'review_date' => 'Review Date',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'milestone' => 'Milestone',
            default => 'Other',
        };
    }
}
