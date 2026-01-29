<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AiAnalysisException;
use Illuminate\Support\Facades\Log;

/**
 * Service for performing quick (ephemeral) contract text analysis.
 * Results are not stored in the database.
 */
class QuickAnalysisService
{
    public function __construct(
        private ClaudeAiService $aiService,
        private PiiDetectionService $piiDetection,
    ) {}

    /**
     * Analyze contract text without storing results.
     *
     * @throws AiAnalysisException
     */
    public function analyze(string $text, ?string $title = null): QuickAnalysisResult
    {
        Log::info('Starting quick text analysis', [
            'text_length' => strlen($text),
            'has_title' => $title !== null,
        ]);

        // Detect PII (warning only, don't block)
        $piiResult = $this->piiDetection->detectPii($text);
        $hasPii = $piiResult->hasPii;

        if ($hasPii) {
            Log::info('PII detected in quick analysis text', [
                'pii_count' => $piiResult->totalCount,
            ]);
        }

        // Perform AI analysis
        $aiResult = $this->aiService->analyzeContract($text);

        Log::info('Quick analysis completed', [
            'clauses_found' => $aiResult->getClauseCount(),
            'deadlines_found' => $aiResult->getDeadlineCount(),
            'tokens_used' => $aiResult->tokensUsed,
            'processing_time_ms' => $aiResult->processingTimeMs,
        ]);

        return new QuickAnalysisResult(
            title: $title ?? 'Quick Analysis',
            summary: $aiResult->summary,
            overallRiskLevel: $aiResult->overallRiskLevel,
            keyFindings: $aiResult->keyFindings,
            clauses: $aiResult->clauses,
            deadlines: $aiResult->deadlines,
            tokensUsed: $aiResult->tokensUsed,
            processingTimeMs: $aiResult->processingTimeMs,
            piiWarning: $hasPii,
        );
    }
}
