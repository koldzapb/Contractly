<?php

declare(strict_types=1);

use App\Exceptions\AiAnalysisException;
use App\Models\User;
use App\Services\AiAnalysisResult;
use App\Services\ClaudeAiService;
use App\Services\PiiDetectionResult;
use App\Services\PiiDetectionService;
use App\Services\QuickAnalysisService;

describe('quick analysis endpoint', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/analyze-text', [
            'text' => str_repeat('a', 150),
        ]);

        $response->assertUnauthorized();
    });

    it('validates text is required', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    });

    it('validates text minimum length', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => 'too short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    });

    it('validates text maximum length', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => str_repeat('a', 500001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    });

    it('validates title maximum length', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => str_repeat('a', 150),
                'title' => str_repeat('a', 256),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('returns analysis result for valid text', function () {
        $mockAiResult = new AiAnalysisResult(
            summary: 'This is a service agreement.',
            overallRiskLevel: 'medium',
            keyFindings: ['Finding 1', 'Finding 2'],
            clauses: [
                [
                    'clause_type' => 'payment',
                    'original_text' => 'Payment is due within 30 days.',
                    'plain_explanation' => 'You must pay within 30 days.',
                    'risk_level' => 'low',
                    'risk_reason' => null,
                    'page_number' => 1,
                ],
            ],
            deadlines: [
                [
                    'deadline_type' => 'payment',
                    'title' => 'Payment Due',
                    'description' => 'First payment due date.',
                    'deadline_date' => '2024-03-15',
                    'source_text' => 'Payment due on March 15, 2024.',
                    'is_recurring' => false,
                    'recurrence_pattern' => null,
                ],
            ],
            model: 'claude-sonnet-4-20250514',
            tokensUsed: 1500,
            processingTimeMs: 2500,
        );

        $contractText = str_repeat('This is contract text. ', 50);
        $mockPiiResult = PiiDetectionResult::empty($contractText);

        $this->mock(ClaudeAiService::class)
            ->shouldReceive('analyzeContract')
            ->once()
            ->andReturn($mockAiResult);

        $this->mock(PiiDetectionService::class)
            ->shouldReceive('detectPii')
            ->once()
            ->andReturn($mockPiiResult);

        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => $contractText,
                'title' => 'Test Contract',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Test Contract')
            ->assertJsonPath('data.summary', 'This is a service agreement.')
            ->assertJsonPath('data.overall_risk_level', 'medium')
            ->assertJsonPath('data.pii_warning', false)
            ->assertJsonCount(2, 'data.key_findings')
            ->assertJsonCount(1, 'data.clauses')
            ->assertJsonCount(1, 'data.deadlines')
            ->assertJsonPath('data.clauses.0.clause_type', 'payment')
            ->assertJsonPath('data.clauses.0.clause_type_label', 'Payment Terms')
            ->assertJsonPath('data.deadlines.0.deadline_type', 'payment')
            ->assertJsonPath('data.deadlines.0.deadline_type_label', 'Payment Due');
    });

    it('uses default title when not provided', function () {
        $mockAiResult = new AiAnalysisResult(
            summary: 'Test summary',
            overallRiskLevel: 'low',
            keyFindings: [],
            clauses: [],
            deadlines: [],
            model: 'claude-sonnet-4-20250514',
            tokensUsed: 500,
            processingTimeMs: 1000,
        );

        $contractText = str_repeat('Contract text here. ', 50);

        $this->mock(ClaudeAiService::class)
            ->shouldReceive('analyzeContract')
            ->once()
            ->andReturn($mockAiResult);

        $this->mock(PiiDetectionService::class)
            ->shouldReceive('detectPii')
            ->once()
            ->andReturn(PiiDetectionResult::empty($contractText));

        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => $contractText,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Quick Analysis');
    });

    it('includes pii warning when pii is detected', function () {
        $mockAiResult = new AiAnalysisResult(
            summary: 'Test summary',
            overallRiskLevel: 'low',
            keyFindings: [],
            clauses: [],
            deadlines: [],
            model: 'claude-sonnet-4-20250514',
            tokensUsed: 500,
            processingTimeMs: 1000,
        );

        $mockPiiResult = PiiDetectionResult::fromArray([
            'has_pii' => true,
            'total_count' => 1,
            'counts_by_type' => ['email' => 1, 'ssn' => 0, 'phone' => 0, 'credit_card' => 0, 'bank_routing' => 0, 'bank_account' => 0],
            'items' => [],
            'extracted_text' => '',
        ]);

        $this->mock(ClaudeAiService::class)
            ->shouldReceive('analyzeContract')
            ->once()
            ->andReturn($mockAiResult);

        $this->mock(PiiDetectionService::class)
            ->shouldReceive('detectPii')
            ->once()
            ->andReturn($mockPiiResult);

        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => str_repeat('Contract text with email test@example.com. ', 10),
            ]);

        $response->assertOk()
            ->assertJsonPath('data.pii_warning', true);
    });

    it('returns error when ai service fails', function () {
        $contractText = str_repeat('Contract text. ', 50);

        $this->mock(ClaudeAiService::class)
            ->shouldReceive('analyzeContract')
            ->once()
            ->andThrow(new AiAnalysisException('API connection failed'));

        $this->mock(PiiDetectionService::class)
            ->shouldReceive('detectPii')
            ->once()
            ->andReturn(PiiDetectionResult::empty($contractText));

        $response = $this->actingAs($this->user)
            ->postJson('/api/analyze-text', [
                'text' => $contractText,
            ]);

        $response->assertStatus(500)
            ->assertJsonPath('message', 'Analysis failed. Please try again.');
    });
});

describe('quick analysis service', function () {
    it('orchestrates pii detection and ai analysis', function () {
        $mockAiResult = new AiAnalysisResult(
            summary: 'Test summary',
            overallRiskLevel: 'high',
            keyFindings: ['Finding 1'],
            clauses: [],
            deadlines: [],
            model: 'claude-sonnet-4-20250514',
            tokensUsed: 1000,
            processingTimeMs: 2000,
        );

        $mockAiService = Mockery::mock(ClaudeAiService::class);
        $mockAiService->shouldReceive('analyzeContract')
            ->once()
            ->with('Test contract text')
            ->andReturn($mockAiResult);

        $mockPiiService = Mockery::mock(PiiDetectionService::class);
        $mockPiiService->shouldReceive('detectPii')
            ->once()
            ->with('Test contract text')
            ->andReturn(PiiDetectionResult::empty('Test contract text'));

        $service = new QuickAnalysisService($mockAiService, $mockPiiService);

        $result = $service->analyze('Test contract text', 'My Contract');

        expect($result->title)->toBe('My Contract');
        expect($result->summary)->toBe('Test summary');
        expect($result->overallRiskLevel)->toBe('high');
        expect($result->keyFindings)->toBe(['Finding 1']);
        expect($result->tokensUsed)->toBe(1000);
        expect($result->processingTimeMs)->toBe(2000);
        expect($result->piiWarning)->toBeFalse();
    });
});
