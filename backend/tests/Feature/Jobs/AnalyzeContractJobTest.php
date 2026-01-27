<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Events\ContractAnalysisCompleted;
use App\Events\ContractAnalysisFailed;
use App\Exceptions\AiAnalysisException;
use App\Exceptions\PdfParsingException;
use App\Jobs\AnalyzeContractJob;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\User;
use App\Services\ContractAnalysisService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('contracts');
    Event::fake();
    $this->user = User::factory()->create();
});

describe('AnalyzeContractJob', function () {
    it('dispatches ContractAnalysisCompleted event on success', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf');

        $mockAnalysis = ContractAnalysis::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $analysisService = Mockery::mock(ContractAnalysisService::class);
        $analysisService->shouldReceive('analyze')
            ->once()
            ->with(Mockery::on(fn ($c) => $c->id === $contract->id))
            ->andReturn($mockAnalysis);

        app()->instance(ContractAnalysisService::class, $analysisService);

        $job = new AnalyzeContractJob($contract);
        $job->handle(app(ContractAnalysisService::class));

        Event::assertDispatched(ContractAnalysisCompleted::class, function ($event) use ($contract, $mockAnalysis) {
            return $event->contract->id === $contract->id
                && $event->analysis->id === $mockAnalysis->id;
        });
    });

    it('dispatches ContractAnalysisFailed event on PDF parsing error', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf');

        $analysisService = Mockery::mock(ContractAnalysisService::class);
        $analysisService->shouldReceive('analyze')
            ->once()
            ->andThrow(new PdfParsingException('Invalid PDF'));

        app()->instance(ContractAnalysisService::class, $analysisService);

        $job = new AnalyzeContractJob($contract);

        try {
            $job->handle(app(ContractAnalysisService::class));
        } catch (PdfParsingException) {
            // Expected
        }

        Event::assertDispatched(ContractAnalysisFailed::class, function ($event) use ($contract) {
            return $event->contract->id === $contract->id
                && $event->errorMessage === 'Invalid PDF';
        });
    });

    it('re-throws AI errors for retry', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf');

        $analysisService = Mockery::mock(ContractAnalysisService::class);
        $analysisService->shouldReceive('analyze')
            ->once()
            ->andThrow(new AiAnalysisException('Rate limit exceeded'));

        app()->instance(ContractAnalysisService::class, $analysisService);

        $job = new AnalyzeContractJob($contract);

        expect(fn () => $job->handle(app(ContractAnalysisService::class)))
            ->toThrow(AiAnalysisException::class);

        Event::assertDispatched(ContractAnalysisFailed::class);
    });

    it('has correct job configuration', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $job = new AnalyzeContractJob($contract);

        expect($job->tries)->toBe(3);
        expect($job->backoff)->toBe(30);
        expect($job->timeout)->toBe(300);
    });

    it('generates correct tags', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $job = new AnalyzeContractJob($contract);
        $tags = $job->tags();

        expect($tags)->toContain("contract:{$contract->id}");
        expect($tags)->toContain("user:{$contract->user_id}");
    });
});
