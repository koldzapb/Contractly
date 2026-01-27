<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\ContractAnalysisCompleted;
use App\Events\ContractAnalysisFailed;
use App\Exceptions\AiAnalysisException;
use App\Exceptions\PdfParsingException;
use App\Models\Contract;
use App\Services\ContractAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeContractJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * The maximum number of seconds the job should run.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Contract $contract,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ContractAnalysisService $analysisService): void
    {
        Log::info('AnalyzeContractJob started', [
            'contract_id' => $this->contract->id,
            'attempt' => $this->attempts(),
        ]);

        try {
            $analysis = $analysisService->analyze($this->contract);

            // Dispatch completion event
            event(new ContractAnalysisCompleted($this->contract, $analysis));

            Log::info('AnalyzeContractJob completed', [
                'contract_id' => $this->contract->id,
                'analysis_id' => $analysis->id,
            ]);
        } catch (PdfParsingException|AiAnalysisException $e) {
            Log::error('AnalyzeContractJob failed', [
                'contract_id' => $this->contract->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Dispatch failure event
            event(new ContractAnalysisFailed($this->contract, $e->getMessage()));

            // Don't retry for PDF parsing errors (they won't succeed on retry)
            if ($e instanceof PdfParsingException) {
                $this->fail($e);

                return;
            }

            // Re-throw to allow retry for AI errors (might be rate limiting)
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('AnalyzeContractJob failed permanently', [
            'contract_id' => $this->contract->id,
            'error' => $exception?->getMessage(),
        ]);

        event(new ContractAnalysisFailed(
            $this->contract,
            $exception?->getMessage() ?? 'Analysis failed after multiple attempts',
        ));
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'contract:'.$this->contract->id,
            'user:'.$this->contract->user_id,
        ];
    }
}
