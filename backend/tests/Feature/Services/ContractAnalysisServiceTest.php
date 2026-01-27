<?php

declare(strict_types=1);

use App\Enums\ClauseType;
use App\Enums\ContractStatus;
use App\Enums\DeadlineType;
use App\Enums\RiskLevel;
use App\Exceptions\AiAnalysisException;
use App\Exceptions\PdfParsingException;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\User;
use App\Services\AiAnalysisResult;
use App\Services\ClaudeAiService;
use App\Services\ContractAnalysisService;
use App\Services\PdfContent;
use App\Services\PdfParserService;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('contracts');
    $this->user = User::factory()->create();
});

describe('analyze', function () {
    it('successfully analyzes a contract', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        // Create fake file
        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        // Mock the services
        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: 'This is the contract text',
                textByPage: [1 => 'This is the contract text'],
                pageCount: 1,
            ));

        $aiService = Mockery::mock(ClaudeAiService::class);
        $aiService->shouldReceive('analyzeContract')
            ->once()
            ->andReturn(new AiAnalysisResult(
                summary: 'A simple service agreement',
                overallRiskLevel: 'medium',
                keyFindings: ['Important finding 1', 'Important finding 2'],
                clauses: [
                    [
                        'clause_type' => 'payment',
                        'original_text' => 'Payment terms text',
                        'plain_explanation' => 'Payment explanation',
                        'risk_level' => 'low',
                        'risk_reason' => null,
                        'page_number' => 1,
                    ],
                ],
                deadlines: [
                    [
                        'deadline_type' => 'payment',
                        'title' => 'First payment',
                        'description' => 'Initial payment due',
                        'deadline_date' => '2024-03-15',
                        'source_text' => 'Due March 15, 2024',
                        'is_recurring' => false,
                        'recurrence_pattern' => null,
                    ],
                ],
                model: 'claude-sonnet-4-20250514',
                tokensUsed: 1500,
                processingTimeMs: 2000,
            ));

        $service = app(ContractAnalysisService::class);

        // Bind mocks
        app()->instance(PdfParserService::class, $pdfParser);
        app()->instance(ClaudeAiService::class, $aiService);
        $service = app(ContractAnalysisService::class);

        $analysis = $service->analyze($contract);

        expect($analysis)->toBeInstanceOf(ContractAnalysis::class);
        expect($analysis->summary)->toBe('A simple service agreement');
        expect($analysis->overall_risk_level)->toBe(RiskLevel::MEDIUM);
        expect($analysis->key_findings)->toHaveCount(2);
        expect($analysis->clauses)->toHaveCount(1);
        expect($analysis->deadlines)->toHaveCount(1);

        // Check contract was updated
        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::COMPLETED);
        expect($contract->overall_risk_level)->toBe(RiskLevel::MEDIUM);
        expect($contract->page_count)->toBe(1);
        expect($contract->analyzed_at)->not->toBeNull();
    });

    it('marks contract as failed on PDF parsing error', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andThrow(new PdfParsingException('Failed to parse PDF'));

        app()->instance(PdfParserService::class, $pdfParser);
        $service = app(ContractAnalysisService::class);

        expect(fn () => $service->analyze($contract))
            ->toThrow(PdfParsingException::class);

        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::FAILED);
        expect($contract->error_message)->toBe('Failed to parse PDF');
    });

    it('marks contract as failed on AI analysis error', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: 'Contract text',
                textByPage: [1 => 'Contract text'],
                pageCount: 1,
            ));

        $aiService = Mockery::mock(ClaudeAiService::class);
        $aiService->shouldReceive('analyzeContract')
            ->once()
            ->andThrow(new AiAnalysisException('API rate limit exceeded'));

        app()->instance(PdfParserService::class, $pdfParser);
        app()->instance(ClaudeAiService::class, $aiService);
        $service = app(ContractAnalysisService::class);

        expect(fn () => $service->analyze($contract))
            ->toThrow(AiAnalysisException::class);

        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::FAILED);
        expect($contract->error_message)->toBe('API rate limit exceeded');
    });

    it('marks contract as failed when PDF has no extractable text', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: '',
                textByPage: [],
                pageCount: 1,
            ));

        app()->instance(PdfParserService::class, $pdfParser);
        $service = app(ContractAnalysisService::class);

        expect(fn () => $service->analyze($contract))
            ->toThrow(PdfParsingException::class, 'PDF does not contain extractable text');

        $contract->refresh();
        expect($contract->status)->toBe(ContractStatus::FAILED);
    });

    it('correctly maps risk levels', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: 'Contract text',
                textByPage: [1 => 'Contract text'],
                pageCount: 1,
            ));

        $aiService = Mockery::mock(ClaudeAiService::class);
        $aiService->shouldReceive('analyzeContract')
            ->once()
            ->andReturn(new AiAnalysisResult(
                summary: 'High risk contract',
                overallRiskLevel: 'high',
                keyFindings: [],
                clauses: [],
                deadlines: [],
                model: 'claude-sonnet-4-20250514',
                tokensUsed: 1000,
                processingTimeMs: 1500,
            ));

        app()->instance(PdfParserService::class, $pdfParser);
        app()->instance(ClaudeAiService::class, $aiService);
        $service = app(ContractAnalysisService::class);

        $analysis = $service->analyze($contract);

        expect($analysis->overall_risk_level)->toBe(RiskLevel::HIGH);
        $contract->refresh();
        expect($contract->overall_risk_level)->toBe(RiskLevel::HIGH);
    });

    it('correctly maps clause types', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: 'Contract text',
                textByPage: [1 => 'Contract text'],
                pageCount: 1,
            ));

        $aiService = Mockery::mock(ClaudeAiService::class);
        $aiService->shouldReceive('analyzeContract')
            ->once()
            ->andReturn(new AiAnalysisResult(
                summary: 'Test contract',
                overallRiskLevel: 'low',
                keyFindings: [],
                clauses: [
                    ['clause_type' => 'termination', 'original_text' => 'T', 'plain_explanation' => 'E', 'risk_level' => 'low'],
                    ['clause_type' => 'liability', 'original_text' => 'L', 'plain_explanation' => 'E', 'risk_level' => 'medium'],
                    ['clause_type' => 'unknown_type', 'original_text' => 'U', 'plain_explanation' => 'E', 'risk_level' => 'low'],
                ],
                deadlines: [],
                model: 'claude-sonnet-4-20250514',
                tokensUsed: 1000,
                processingTimeMs: 1500,
            ));

        app()->instance(PdfParserService::class, $pdfParser);
        app()->instance(ClaudeAiService::class, $aiService);
        $service = app(ContractAnalysisService::class);

        $analysis = $service->analyze($contract);
        $clauses = $analysis->clauses;

        expect($clauses[0]->clause_type)->toBe(ClauseType::TERMINATION);
        expect($clauses[1]->clause_type)->toBe(ClauseType::LIABILITY);
        expect($clauses[2]->clause_type)->toBe(ClauseType::OTHER); // Unknown maps to OTHER
    });

    it('correctly maps deadline types', function () {
        $contract = Contract::factory()->create([
            'user_id' => $this->user->id,
            'status' => ContractStatus::PENDING,
            'file_path' => 'test/contract.pdf',
        ]);

        Storage::disk('contracts')->put('test/contract.pdf', 'fake pdf content');

        $pdfParser = Mockery::mock(PdfParserService::class);
        $pdfParser->shouldReceive('extractText')
            ->once()
            ->andReturn(new PdfContent(
                fullText: 'Contract text',
                textByPage: [1 => 'Contract text'],
                pageCount: 1,
            ));

        $aiService = Mockery::mock(ClaudeAiService::class);
        $aiService->shouldReceive('analyzeContract')
            ->once()
            ->andReturn(new AiAnalysisResult(
                summary: 'Test contract',
                overallRiskLevel: 'low',
                keyFindings: [],
                clauses: [],
                deadlines: [
                    ['deadline_type' => 'renewal', 'title' => 'Renewal', 'source_text' => 'R'],
                    ['deadline_type' => 'termination_notice', 'title' => 'Notice', 'source_text' => 'N'],
                    ['deadline_type' => 'unknown_type', 'title' => 'Unknown', 'source_text' => 'U'],
                ],
                model: 'claude-sonnet-4-20250514',
                tokensUsed: 1000,
                processingTimeMs: 1500,
            ));

        app()->instance(PdfParserService::class, $pdfParser);
        app()->instance(ClaudeAiService::class, $aiService);
        $service = app(ContractAnalysisService::class);

        $analysis = $service->analyze($contract);
        $deadlines = $analysis->deadlines;

        expect($deadlines[0]->deadline_type)->toBe(DeadlineType::RENEWAL);
        expect($deadlines[1]->deadline_type)->toBe(DeadlineType::TERMINATION_NOTICE);
        expect($deadlines[2]->deadline_type)->toBe(DeadlineType::OTHER);
    });
});
