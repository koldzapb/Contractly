<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AiAnalysisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private string $apiVersion;

    private int $maxTokens;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = Config::get('services.anthropic.api_key', '');
        $this->model = Config::get('services.anthropic.model', 'claude-sonnet-4-20250514');
        $this->baseUrl = Config::get('services.anthropic.base_url', 'https://api.anthropic.com');
        $this->apiVersion = Config::get('services.anthropic.api_version', '2023-06-01');
        $this->maxTokens = (int) Config::get('services.anthropic.max_tokens', 4096);
        $this->timeout = (int) Config::get('services.anthropic.timeout', 120);
    }

    /**
     * Analyze contract text using Claude AI.
     *
     * @throws AiAnalysisException
     */
    public function analyzeContract(string $contractText): AiAnalysisResult
    {
        if (empty($this->apiKey)) {
            throw new AiAnalysisException('Anthropic API key is not configured');
        }

        $prompt = $this->buildAnalysisPrompt($contractText);
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->apiVersion,
                    'content-type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v1/messages", [
                    'model' => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            if (! $response->successful()) {
                $error = $response->json('error.message', 'Unknown API error');
                Log::error('Claude API error', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new AiAnalysisException("Claude API error: {$error}");
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';
            $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

            // Parse the JSON response from Claude
            $analysis = $this->parseAnalysisResponse($content);

            return new AiAnalysisResult(
                summary: $analysis['summary'] ?? '',
                overallRiskLevel: $analysis['overall_risk_level'] ?? 'low',
                keyFindings: $analysis['key_findings'] ?? [],
                clauses: $analysis['clauses'] ?? [],
                deadlines: $analysis['deadlines'] ?? [],
                model: $this->model,
                tokensUsed: $tokensUsed,
                processingTimeMs: $processingTimeMs,
                rawResponse: $data,
            );
        } catch (ConnectionException $e) {
            Log::error('Claude API connection error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Failed to connect to Claude API: {$e->getMessage()}", previous: $e);
        } catch (RequestException $e) {
            Log::error('Claude API request error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Claude API request failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Build the analysis prompt for Claude.
     */
    private function buildAnalysisPrompt(string $contractText): string
    {
        return <<<PROMPT
You are a legal contract analyst. Analyze the following contract and extract key information.

Respond ONLY with a valid JSON object in the following format (no markdown, no explanations, just JSON):

{
  "summary": "A 2-3 sentence summary of what this contract is about",
  "overall_risk_level": "low|medium|high",
  "key_findings": [
    "First important finding or concern",
    "Second important finding",
    "Third important finding"
  ],
  "clauses": [
    {
      "clause_type": "payment|termination|liability|penalty|auto_renewal|non_compete|confidentiality|indemnification|dispute_resolution|other",
      "original_text": "The exact text from the contract",
      "plain_explanation": "A simple explanation of what this clause means in plain language",
      "risk_level": "low|medium|high",
      "risk_reason": "Why this clause has this risk level (or null if low risk)",
      "page_number": 1
    }
  ],
  "deadlines": [
    {
      "deadline_type": "payment|renewal|termination_notice|delivery|review_period|other",
      "title": "Short title for the deadline",
      "description": "Description of what happens at this deadline",
      "deadline_date": "YYYY-MM-DD or null if no specific date",
      "source_text": "The exact text that mentions this deadline",
      "is_recurring": false,
      "recurrence_pattern": "monthly|quarterly|annually|null"
    }
  ]
}

Important guidelines:
1. Extract ALL significant clauses, especially those related to payments, termination, liability, penalties, auto-renewal, and confidentiality
2. Mark clauses as "high" risk if they contain unfavorable terms, unlimited liability, automatic renewals, or non-compete restrictions
3. Mark clauses as "medium" risk if they need attention but are relatively standard
4. For deadlines, try to extract specific dates if mentioned; otherwise use relative dates or leave null
5. Be thorough but concise in explanations
6. Page numbers are 1-indexed

CONTRACT TEXT:
{$contractText}
PROMPT;
    }

    /**
     * Parse the JSON response from Claude.
     *
     *
     * @throws AiAnalysisException
     *
     * @return array<string, mixed>
     */
    private function parseAnalysisResponse(string $content): array
    {
        // Try to extract JSON from the response
        $content = trim($content);

        // Remove potential markdown code blocks
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        } elseif (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }

        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }

        $content = trim($content);

        $analysis = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse Claude response as JSON', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 500),
            ]);

            throw new AiAnalysisException('Failed to parse AI response: '.json_last_error_msg());
        }

        return $analysis;
    }
}
