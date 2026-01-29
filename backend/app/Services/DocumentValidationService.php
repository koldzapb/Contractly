<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentCategory;
use App\Enums\DocumentType;
use App\Exceptions\AiAnalysisException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentValidationService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private string $apiVersion;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = Config::get('services.anthropic.api_key', '');
        $this->model = Config::get('services.anthropic.model', 'claude-sonnet-4-20250514');
        $this->baseUrl = Config::get('services.anthropic.base_url', 'https://api.anthropic.com');
        $this->apiVersion = Config::get('services.anthropic.api_version', '2023-06-01');
        $this->timeout = (int) Config::get('services.anthropic.timeout', 120);
    }

    /**
     * Classify a document to determine if it's a legal contract.
     *
     * @param string $documentText Extracted text from the document (first few pages)
     *
     * @throws AiAnalysisException
     */
    public function classifyDocument(string $documentText): DocumentClassificationResult
    {
        if (empty($this->apiKey)) {
            throw new AiAnalysisException('Anthropic API key is not configured');
        }

        // Use only the first portion of the document for classification (faster + cheaper)
        $sampleText = $this->getSampleText($documentText);
        $prompt = $this->buildClassificationPrompt($sampleText);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => $this->apiVersion,
                    'content-type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v1/messages", [
                    'model' => $this->model,
                    'max_tokens' => 1024,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                $error = $response->json('error.message', 'Unknown API error');
                Log::error('Document classification API error', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new AiAnalysisException("Claude API error: {$error}");
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';

            return $this->parseClassificationResponse($content);
        } catch (ConnectionException $e) {
            Log::error('Document classification connection error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Failed to connect to Claude API: {$e->getMessage()}", previous: $e);
        } catch (RequestException $e) {
            Log::error('Document classification request error', ['error' => $e->getMessage()]);

            throw new AiAnalysisException("Claude API request failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Get a sample of the document text for classification.
     */
    private function getSampleText(string $text, int $maxChars = 5000): string
    {
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, $maxChars).'...';
    }

    /**
     * Build the classification prompt.
     */
    private function buildClassificationPrompt(string $documentText): string
    {
        return <<<PROMPT
You are a document classification expert. Analyze the following document and classify it.

Respond ONLY with a valid JSON object in the following format (no markdown, no explanations, just JSON):

{
  "document_type": "contract|amendment|nda|mou|loi|term_sheet|invoice|receipt|letter|report|other|unknown",
  "confidence": 0.0 to 1.0,
  "is_legal_document": true or false,
  "reasoning": "Brief explanation of why you classified it this way"
}

Document type definitions:
- "contract": A legally binding agreement between parties (employment, service, sales, lease, etc.)
- "amendment": A modification to an existing contract
- "nda": Non-disclosure or confidentiality agreement
- "mou": Memorandum of Understanding (typically non-binding)
- "loi": Letter of Intent (typically non-binding)
- "term_sheet": Summary of proposed terms (typically non-binding)
- "invoice": A bill for goods or services
- "receipt": Proof of payment
- "letter": General correspondence
- "report": Business or financial report
- "other": Other document type not listed
- "unknown": Cannot determine document type

A document is a "legal document" (is_legal_document: true) if it is:
- A binding contract (contract, amendment, nda)
- A pre-contractual document (mou, loi, term_sheet)

A document is NOT a legal document (is_legal_document: false) if it is:
- An invoice, receipt, letter, report, or other non-contractual document

DOCUMENT TEXT:
{$documentText}
PROMPT;
    }

    /**
     * Parse the classification response from Claude.
     *
     * @throws AiAnalysisException
     */
    private function parseClassificationResponse(string $content): DocumentClassificationResult
    {
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
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse classification response', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 500),
            ]);

            throw new AiAnalysisException('Failed to parse classification response: '.json_last_error_msg());
        }

        $documentType = DocumentType::tryFrom($data['document_type'] ?? '') ?? DocumentType::UNKNOWN;
        $isLegalDocument = $data['is_legal_document'] ?? false;
        $confidence = (float) ($data['confidence'] ?? 0.5);
        $reasoning = $data['reasoning'] ?? '';

        $category = $documentType->category();

        // Build rejection reason for non-legal documents
        $rejectionReason = null;
        if (! $isLegalDocument) {
            $rejectionReason = $this->buildRejectionReason($documentType, $reasoning);
        }

        // Get warnings for pre-contractual documents
        $warnings = $category->getWarnings($documentType);

        return new DocumentClassificationResult(
            isLegalDocument: $isLegalDocument || $category !== DocumentCategory::NON_LEGAL,
            documentType: $documentType,
            category: $category,
            confidence: $confidence,
            rejectionReason: $rejectionReason,
            warnings: $warnings,
        );
    }

    /**
     * Build a user-friendly rejection reason.
     */
    private function buildRejectionReason(DocumentType $type, string $reasoning): string
    {
        $typeLabel = $type->label();

        return match ($type) {
            DocumentType::INVOICE => "This appears to be an invoice, not a legal contract. {$reasoning}",
            DocumentType::RECEIPT => "This appears to be a receipt, not a legal contract. {$reasoning}",
            DocumentType::LETTER => "This appears to be a general letter, not a legal contract. {$reasoning}",
            DocumentType::REPORT => "This appears to be a report, not a legal contract. {$reasoning}",
            DocumentType::OTHER => "This document does not appear to be a legal contract. {$reasoning}",
            DocumentType::UNKNOWN => "We could not determine the document type. {$reasoning}",
            default => "This {$typeLabel} is not a legal contract. {$reasoning}",
        };
    }
}
