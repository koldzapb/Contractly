<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChatRole;
use App\Exceptions\AiAnalysisException;
use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ChatMessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractChatService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private string $apiVersion;

    private int $maxTokens;

    private int $timeout;

    private int $maxHistoryMessages;

    private int $maxContractTextLength;

    public function __construct(
        private ChatMessageRepositoryInterface $chatMessages,
        private PdfParserService $pdfParser,
    ) {
        $this->apiKey = Config::get('services.anthropic.api_key', '');
        $this->model = Config::get('services.anthropic.model', 'claude-sonnet-4-20250514');
        $this->baseUrl = Config::get('services.anthropic.base_url', 'https://api.anthropic.com');
        $this->apiVersion = Config::get('services.anthropic.api_version', '2023-06-01');
        $this->maxTokens = (int) Config::get('services.anthropic.chat_max_tokens', 2048);
        $this->timeout = (int) Config::get('services.anthropic.timeout', 120);
        $this->maxHistoryMessages = 20;
        $this->maxContractTextLength = 50000;
    }

    /**
     * Send a message and get a response from the AI.
     *
     * @throws AiAnalysisException
     */
    public function sendMessage(Contract $contract, User $user, string $message): ChatMessage
    {
        if (empty($this->apiKey)) {
            throw new AiAnalysisException('Anthropic API key is not configured');
        }

        // Load contract with analysis if not already loaded
        if (! $contract->relationLoaded('analysis')) {
            $contract->load(['analysis.clauses', 'analysis.deadlines']);
        }

        // Check if contract has been analyzed
        if (! $contract->analysis) {
            throw new AiAnalysisException('Contract has not been analyzed yet');
        }

        // Save user message
        $userMessage = $this->chatMessages->create([
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'role' => ChatRole::USER,
            'content' => $message,
        ]);

        // Get conversation history
        $history = $this->getConversationHistory($contract);

        // Build system prompt with contract context
        $systemPrompt = $this->buildSystemPrompt($contract);

        // Build messages array for API
        $messages = $this->buildMessages($history, $message);

        try {
            $response = $this->callClaudeApi($systemPrompt, $messages);

            // Check if response indicates off-topic
            $isOffTopic = $this->isOffTopicResponse($response->content);

            // Save assistant message
            $assistantMessage = $this->chatMessages->create([
                'contract_id' => $contract->id,
                'user_id' => $user->id,
                'role' => ChatRole::ASSISTANT,
                'content' => $response->content,
                'tokens_used' => $response->tokensUsed,
                'is_off_topic' => $isOffTopic,
            ]);

            // Mark user message as off-topic too if the response was off-topic
            if ($isOffTopic) {
                $userMessage->update(['is_off_topic' => true]);
            }

            return $assistantMessage;
        } catch (\Exception $e) {
            // Delete the user message if we failed to get a response
            $this->chatMessages->delete($userMessage);

            throw $e;
        }
    }

    /**
     * Get chat history for a contract.
     */
    public function getHistory(Contract $contract): Collection
    {
        return $this->chatMessages->getForContract($contract);
    }

    /**
     * Clear chat history for a contract.
     */
    public function clearHistory(Contract $contract): int
    {
        return $this->chatMessages->clearForContract($contract);
    }

    /**
     * Build the system prompt with contract context.
     */
    private function buildSystemPrompt(Contract $contract): string
    {
        /** @var \App\Models\ContractAnalysis $analysis */
        $analysis = $contract->analysis;

        // Format key findings
        $keyFindings = implode("\n- ", $analysis->key_findings);

        // Format clauses
        /** @var Collection<int, \App\Models\ContractClause> $clauses */
        $clauses = $analysis->clauses;
        $clausesFormatted = $this->formatClauses($clauses);

        // Format deadlines
        /** @var Collection<int, \App\Models\ContractDeadline> $deadlines */
        $deadlines = $analysis->deadlines;
        $deadlinesFormatted = $this->formatDeadlines($deadlines);

        // Get contract text (truncated if needed)
        $contractText = $this->getContractText($contract);

        return <<<PROMPT
You are a contract analysis assistant for Contractly. Your role is to help users understand their specific contract.

STRICT RULES:
1. ONLY answer questions about the contract provided below
2. ONLY answer questions about legal concepts relevant to this contract
3. NEVER provide legal advice - you explain, you don't advise
4. NEVER discuss topics unrelated to contracts or law
5. If asked about anything else, respond with the OFF-TOPIC RESPONSE below
6. Always cite specific sections when referencing the contract
7. Include the disclaimer when discussing risk or recommendations

OFF-TOPIC RESPONSE (use this exact format when the question is unrelated):
"I can only help with questions about this specific contract or related legal concepts. Here are some things I can help with:

• Explain specific clauses or terms
• Identify potential risks or concerns
• Clarify your rights and obligations
• Compare terms to industry standards

What would you like to know about your contract?"

DISCLAIMER (include when giving opinions or recommendations):
"Note: This is informational only, not legal advice. Consult a qualified attorney for decisions about this contract."

---

CONTRACT TITLE: {$contract->title}

OVERALL RISK LEVEL: {$analysis->overall_risk_level->value}

SUMMARY:
{$analysis->summary}

KEY FINDINGS:
- {$keyFindings}

EXTRACTED CLAUSES:
{$clausesFormatted}

EXTRACTED DEADLINES:
{$deadlinesFormatted}

FULL CONTRACT TEXT:
{$contractText}

---

Answer the user's questions based on the contract information above. Be helpful, accurate, and cite specific sections when possible.
PROMPT;
    }

    /**
     * Format clauses for the system prompt.
     *
     * @param Collection<int, \App\Models\ContractClause> $clauses
     */
    private function formatClauses(Collection $clauses): string
    {
        if ($clauses->isEmpty()) {
            return 'No clauses extracted.';
        }

        $formatted = [];
        /** @var \App\Models\ContractClause $clause */
        foreach ($clauses as $clause) {
            $risk = strtoupper($clause->risk_level->value);
            $formatted[] = <<<CLAUSE
[{$clause->clause_type->label()}] (Risk: {$risk})
Text: "{$clause->original_text}"
Explanation: {$clause->plain_explanation}
CLAUSE;
        }

        return implode("\n\n", $formatted);
    }

    /**
     * Format deadlines for the system prompt.
     *
     * @param Collection<int, \App\Models\ContractDeadline> $deadlines
     */
    private function formatDeadlines(Collection $deadlines): string
    {
        if ($deadlines->isEmpty()) {
            return 'No deadlines extracted.';
        }

        $formatted = [];
        /** @var \App\Models\ContractDeadline $deadline */
        foreach ($deadlines as $deadline) {
            $date = $deadline->deadline_date?->format('Y-m-d') ?? 'No specific date';
            $recurring = $deadline->is_recurring ? " (Recurring: {$deadline->recurrence_pattern})" : '';
            $formatted[] = "- {$deadline->title}: {$date}{$recurring} - {$deadline->description}";
        }

        return implode("\n", $formatted);
    }

    /**
     * Get contract text, truncated if necessary.
     */
    private function getContractText(Contract $contract): string
    {
        try {
            $filePath = Storage::disk('contracts')->path($contract->file_path);
            $pdfContent = $this->pdfParser->extractText($filePath);
            $text = $pdfContent->fullText;

            if (strlen($text) > $this->maxContractTextLength) {
                $text = substr($text, 0, $this->maxContractTextLength)
                    ."\n\n[Contract text truncated due to length...]";
            }

            return $text;
        } catch (\Exception $e) {
            Log::warning('Failed to extract contract text for chat', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);

            return '[Contract text could not be extracted. Please rely on the analysis data above.]';
        }
    }

    /**
     * Get recent conversation history.
     */
    private function getConversationHistory(Contract $contract): Collection
    {
        return $this->chatMessages->getRecentForContract(
            $contract,
            $this->maxHistoryMessages,
        );
    }

    /**
     * Build messages array for the API request.
     *
     * @param Collection<int, ChatMessage> $history
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function buildMessages(Collection $history, string $newMessage): array
    {
        $messages = [];

        /** @var ChatMessage $message */
        foreach ($history as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
        }

        // Add the new user message
        $messages[] = [
            'role' => 'user',
            'content' => $newMessage,
        ];

        return $messages;
    }

    /**
     * Call the Claude API.
     *
     * @param array<int, array{role: string, content: string}> $messages
     *
     * @throws AiAnalysisException
     */
    private function callClaudeApi(string $systemPrompt, array $messages): ChatResponse
    {
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
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ]);

            if (! $response->successful()) {
                $error = $response->json('error.message', 'Unknown API error');
                Log::error('Claude Chat API error', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new AiAnalysisException("Claude API error: {$error}");
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';
            $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

            return new ChatResponse(
                content: $content,
                tokensUsed: $tokensUsed,
            );
        } catch (ConnectionException $e) {
            Log::error('Claude Chat API connection error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Failed to connect to Claude API: {$e->getMessage()}", previous: $e);
        } catch (RequestException $e) {
            Log::error('Claude Chat API request error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Claude API request failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Check if the response indicates an off-topic question.
     */
    private function isOffTopicResponse(string $response): bool
    {
        $offTopicIndicators = [
            'I can only help with questions about this specific contract',
            'questions about this contract or related legal concepts',
            'What would you like to know about your contract?',
        ];

        foreach ($offTopicIndicators as $indicator) {
            if (str_contains($response, $indicator)) {
                return true;
            }
        }

        return false;
    }
}
