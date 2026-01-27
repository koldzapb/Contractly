<?php

declare(strict_types=1);

use App\Exceptions\AiAnalysisException;
use App\Services\AiAnalysisResult;
use App\Services\ClaudeAiService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.anthropic.api_key', 'test-api-key');
    Config::set('services.anthropic.model', 'claude-sonnet-4-20250514');
    $this->service = new ClaudeAiService;
});

describe('analyzeContract', function () {
    it('throws exception when API key is not configured', function () {
        Config::set('services.anthropic.api_key', '');
        $service = new ClaudeAiService;

        expect(fn () => $service->analyzeContract('Test contract text'))
            ->toThrow(AiAnalysisException::class, 'Anthropic API key is not configured');
    });

    it('successfully analyzes contract text', function () {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'summary' => 'This is a service agreement between Party A and Party B.',
                            'overall_risk_level' => 'medium',
                            'key_findings' => [
                                'Payment terms are favorable',
                                'Auto-renewal clause present',
                            ],
                            'clauses' => [
                                [
                                    'clause_type' => 'payment',
                                    'original_text' => 'Payment is due within 30 days',
                                    'plain_explanation' => 'You must pay within 30 days of receiving the invoice',
                                    'risk_level' => 'low',
                                    'risk_reason' => null,
                                    'page_number' => 1,
                                ],
                                [
                                    'clause_type' => 'auto_renewal',
                                    'original_text' => 'This agreement shall automatically renew',
                                    'plain_explanation' => 'The contract will renew automatically unless you cancel',
                                    'risk_level' => 'high',
                                    'risk_reason' => 'Auto-renewal may lock you in unexpectedly',
                                    'page_number' => 3,
                                ],
                            ],
                            'deadlines' => [
                                [
                                    'deadline_type' => 'payment',
                                    'title' => 'Initial Payment Due',
                                    'description' => 'First payment must be made',
                                    'deadline_date' => '2024-02-15',
                                    'source_text' => 'Payment is due by February 15, 2024',
                                    'is_recurring' => false,
                                    'recurrence_pattern' => null,
                                ],
                            ],
                        ]),
                    ],
                ],
                'usage' => [
                    'input_tokens' => 1000,
                    'output_tokens' => 500,
                ],
            ], 200),
        ]);

        $result = $this->service->analyzeContract('Sample contract text...');

        expect($result)->toBeInstanceOf(AiAnalysisResult::class);
        expect($result->summary)->toBe('This is a service agreement between Party A and Party B.');
        expect($result->overallRiskLevel)->toBe('medium');
        expect($result->keyFindings)->toHaveCount(2);
        expect($result->clauses)->toHaveCount(2);
        expect($result->deadlines)->toHaveCount(1);
        expect($result->tokensUsed)->toBe(1500);
        expect($result->model)->toBe('claude-sonnet-4-20250514');
    });

    it('handles API errors gracefully', function () {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'error' => [
                    'message' => 'Rate limit exceeded',
                ],
            ], 429),
        ]);

        expect(fn () => $this->service->analyzeContract('Test text'))
            ->toThrow(AiAnalysisException::class, 'Claude API error: Rate limit exceeded');
    });

    it('handles invalid JSON response', function () {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'This is not valid JSON',
                    ],
                ],
                'usage' => [
                    'input_tokens' => 100,
                    'output_tokens' => 50,
                ],
            ], 200),
        ]);

        expect(fn () => $this->service->analyzeContract('Test text'))
            ->toThrow(AiAnalysisException::class, 'Failed to parse AI response');
    });

    it('handles JSON wrapped in markdown code blocks', function () {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "```json\n".json_encode([
                            'summary' => 'Test summary',
                            'overall_risk_level' => 'low',
                            'key_findings' => [],
                            'clauses' => [],
                            'deadlines' => [],
                        ])."\n```",
                    ],
                ],
                'usage' => [
                    'input_tokens' => 100,
                    'output_tokens' => 50,
                ],
            ], 200),
        ]);

        $result = $this->service->analyzeContract('Test text');

        expect($result->summary)->toBe('Test summary');
        expect($result->overallRiskLevel)->toBe('low');
    });

    it('handles connection errors', function () {
        Http::fake([
            'api.anthropic.com/*' => fn () => throw new Illuminate\Http\Client\ConnectionException('Connection refused'),
        ]);

        expect(fn () => $this->service->analyzeContract('Test text'))
            ->toThrow(AiAnalysisException::class, 'Failed to connect to Claude API');
    });
});

describe('AiAnalysisResult', function () {
    it('calculates clause and deadline counts', function () {
        $result = new AiAnalysisResult(
            summary: 'Test',
            overallRiskLevel: 'low',
            keyFindings: [],
            clauses: [
                ['clause_type' => 'payment'],
                ['clause_type' => 'termination'],
            ],
            deadlines: [
                ['deadline_type' => 'payment'],
            ],
            model: 'test-model',
            tokensUsed: 100,
            processingTimeMs: 1000,
        );

        expect($result->getClauseCount())->toBe(2);
        expect($result->getDeadlineCount())->toBe(1);
    });

    it('filters high risk clauses', function () {
        $result = new AiAnalysisResult(
            summary: 'Test',
            overallRiskLevel: 'medium',
            keyFindings: [],
            clauses: [
                ['clause_type' => 'payment', 'risk_level' => 'low'],
                ['clause_type' => 'termination', 'risk_level' => 'high'],
                ['clause_type' => 'liability', 'risk_level' => 'high'],
            ],
            deadlines: [],
            model: 'test-model',
            tokensUsed: 100,
            processingTimeMs: 1000,
        );

        $highRisk = $result->getHighRiskClauses();

        expect($highRisk)->toHaveCount(2);
    });
});
