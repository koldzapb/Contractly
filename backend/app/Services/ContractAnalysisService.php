<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClauseType;
use App\Enums\ContractStatus;
use App\Enums\DeadlineType;
use App\Enums\FileType;
use App\Enums\RiskLevel;
use App\Exceptions\AiAnalysisException;
use App\Exceptions\TextExtractionException;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Repositories\Contracts\ContractAnalysisRepositoryInterface;
use App\Repositories\Contracts\ContractClauseRepositoryInterface;
use App\Repositories\Contracts\ContractDeadlineRepositoryInterface;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\PiiDetectionService;
use App\Services\TextExtraction\TextExtractorFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractAnalysisService
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
        private ContractAnalysisRepositoryInterface $analyses,
        private ContractClauseRepositoryInterface $clauses,
        private ContractDeadlineRepositoryInterface $deadlines,
        private TextExtractorFactory $textExtractorFactory,
        private ClaudeAiService $aiService,
        private PiiDetectionService $piiDetection,
    ) {}

    /**
     * Analyze a contract.
     *
     * @throws TextExtractionException
     * @throws AiAnalysisException
     */
    public function analyze(Contract $contract): ContractAnalysis
    {
        Log::info('Starting contract analysis', [
            'contract_id' => $contract->id,
            'file_type' => $contract->file_type->value,
        ]);

        // Mark as processing
        $this->contracts->update($contract, [
            'status' => ContractStatus::PROCESSING,
            'error_message' => null,
        ]);

        try {
            // Step 1: Extract text using appropriate extractor
            $filePath = Storage::disk('contracts')->path($contract->file_path);
            $extractedContent = $this->textExtractorFactory->extract($filePath);

            // Update page count
            $this->contracts->update($contract, [
                'page_count' => $extractedContent->pageCount,
            ]);

            // Check if extraction produced any text
            if (! $extractedContent->hasText()) {
                $errorMessage = match ($contract->file_type) {
                    FileType::PDF => 'PDF does not contain extractable text. It may be scanned or image-based.',
                    FileType::IMAGE => 'Could not extract text from the image. The image may be unclear or contain no readable text.',
                    FileType::TEXT => 'The text file appears to be empty.',
                };

                throw new TextExtractionException($errorMessage);
            }

            Log::info('Text extracted', [
                'contract_id' => $contract->id,
                'file_type' => $contract->file_type->value,
                'pages' => $extractedContent->pageCount,
                'words' => $extractedContent->getWordCount(),
            ]);

            // Step 2: Detect PII in extracted text
            $piiResult = $this->piiDetection->detectPii($extractedContent->fullText);
            $this->contracts->update($contract, [
                'pii_detection' => $piiResult->toArray(),
            ]);

            Log::info('PII detection complete', [
                'contract_id' => $contract->id,
                'has_pii' => $piiResult->hasPii,
                'total_count' => $piiResult->totalCount,
            ]);

            // Step 3: Analyze with Claude AI
            $aiResult = $this->aiService->analyzeContract($extractedContent->fullText);

            Log::info('AI analysis complete', [
                'contract_id' => $contract->id,
                'clauses' => $aiResult->getClauseCount(),
                'deadlines' => $aiResult->getDeadlineCount(),
                'tokens' => $aiResult->tokensUsed,
            ]);

            // Step 4: Save results in a transaction
            return DB::transaction(function () use ($contract, $aiResult) {
                // Create the analysis record
                $analysis = $this->saveAnalysis($contract, $aiResult);

                // Save clauses
                $this->saveClauses($analysis, $aiResult->clauses);

                // Save deadlines
                $this->saveDeadlines($analysis, $aiResult->deadlines);

                // Update contract status
                $overallRisk = $this->mapRiskLevel($aiResult->overallRiskLevel);
                $this->contracts->update($contract, [
                    'status' => ContractStatus::COMPLETED,
                    'overall_risk_level' => $overallRisk,
                    'analyzed_at' => Carbon::now(),
                ]);

                Log::info('Contract analysis saved', [
                    'contract_id' => $contract->id,
                    'analysis_id' => $analysis->id,
                ]);

                return $analysis;
            });
        } catch (TextExtractionException|AiAnalysisException $e) {
            $this->markAsFailed($contract, $e->getMessage());

            throw $e;
        } catch (\Exception $e) {
            $message = 'An unexpected error occurred during analysis';
            $this->markAsFailed($contract, $message);
            Log::error('Contract analysis failed', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);

            throw new AiAnalysisException($message, previous: $e);
        }
    }

    /**
     * Save the analysis record.
     */
    private function saveAnalysis(Contract $contract, AiAnalysisResult $result): ContractAnalysis
    {
        return $this->analyses->create([
            'contract_id' => $contract->id,
            'summary' => $result->summary,
            'overall_risk_level' => $this->mapRiskLevel($result->overallRiskLevel),
            'key_findings' => $result->keyFindings,
            'ai_model' => $result->model,
            'tokens_used' => $result->tokensUsed,
            'processing_time_ms' => $result->processingTimeMs,
            'raw_response' => $result->rawResponse,
        ]);
    }

    /**
     * Save extracted clauses.
     *
     * @param array<int, array<string, mixed>> $clauses
     */
    private function saveClauses(ContractAnalysis $analysis, array $clauses): void
    {
        foreach ($clauses as $index => $clauseData) {
            $this->clauses->create([
                'contract_analysis_id' => $analysis->id,
                'clause_type' => $this->mapClauseType($clauseData['clause_type'] ?? 'other'),
                'original_text' => $clauseData['original_text'] ?? '',
                'plain_explanation' => $clauseData['plain_explanation'] ?? '',
                'risk_level' => $this->mapRiskLevel($clauseData['risk_level'] ?? 'low'),
                'risk_reason' => $clauseData['risk_reason'] ?? null,
                'page_number' => $clauseData['page_number'] ?? null,
                'position_index' => $index,
            ]);
        }
    }

    /**
     * Save extracted deadlines.
     *
     * @param array<int, array<string, mixed>> $deadlines
     */
    private function saveDeadlines(ContractAnalysis $analysis, array $deadlines): void
    {
        foreach ($deadlines as $deadlineData) {
            $deadlineDate = null;
            if (! empty($deadlineData['deadline_date'])) {
                try {
                    $deadlineDate = Carbon::parse($deadlineData['deadline_date']);
                } catch (\Exception) {
                    // Invalid date format, skip
                }
            }

            $this->deadlines->create([
                'contract_analysis_id' => $analysis->id,
                'deadline_type' => $this->mapDeadlineType($deadlineData['deadline_type'] ?? 'other'),
                'title' => $deadlineData['title'] ?? 'Unnamed Deadline',
                'description' => $deadlineData['description'] ?? null,
                'deadline_date' => $deadlineDate,
                'source_text' => $deadlineData['source_text'] ?? '',
                'is_recurring' => $deadlineData['is_recurring'] ?? false,
                'recurrence_pattern' => $deadlineData['recurrence_pattern'] ?? null,
            ]);
        }
    }

    /**
     * Mark a contract as failed.
     */
    private function markAsFailed(Contract $contract, string $errorMessage): void
    {
        $this->contracts->update($contract, [
            'status' => ContractStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Map string risk level to enum.
     */
    private function mapRiskLevel(string $level): RiskLevel
    {
        return match (strtolower($level)) {
            'high' => RiskLevel::HIGH,
            'medium' => RiskLevel::MEDIUM,
            'low' => RiskLevel::LOW,
            default => RiskLevel::NONE,
        };
    }

    /**
     * Map string clause type to enum.
     */
    private function mapClauseType(string $type): ClauseType
    {
        return match (strtolower($type)) {
            'payment' => ClauseType::PAYMENT,
            'termination' => ClauseType::TERMINATION,
            'liability' => ClauseType::LIABILITY,
            'penalty' => ClauseType::PENALTY,
            'auto_renewal' => ClauseType::AUTO_RENEWAL,
            'non_compete' => ClauseType::NON_COMPETE,
            'confidentiality' => ClauseType::CONFIDENTIALITY,
            'indemnification' => ClauseType::INDEMNIFICATION,
            'dispute_resolution' => ClauseType::DISPUTE_RESOLUTION,
            default => ClauseType::OTHER,
        };
    }

    /**
     * Map string deadline type to enum.
     */
    private function mapDeadlineType(string $type): DeadlineType
    {
        return match (strtolower($type)) {
            'payment' => DeadlineType::PAYMENT,
            'renewal' => DeadlineType::RENEWAL,
            'termination_notice' => DeadlineType::TERMINATION_NOTICE,
            'delivery' => DeadlineType::DELIVERY,
            'review_period' => DeadlineType::REVIEW_PERIOD,
            default => DeadlineType::OTHER,
        };
    }
}
