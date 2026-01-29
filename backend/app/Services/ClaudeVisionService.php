<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TextExtractionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for extracting text from images using Claude Vision API.
 */
class ClaudeVisionService
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
        $this->maxTokens = (int) Config::get('services.anthropic.vision_max_tokens', 4096);
        $this->timeout = (int) Config::get('services.anthropic.timeout', 120);
    }

    /**
     * Extract text from an image file using Claude Vision.
     *
     * @param  string                  $imagePath Absolute path to the image file
     * @throws TextExtractionException If extraction fails
     */
    public function extractText(string $imagePath): string
    {
        if (empty($this->apiKey)) {
            throw new TextExtractionException('Anthropic API key is not configured');
        }

        if (! file_exists($imagePath)) {
            throw new TextExtractionException("Image file not found: {$imagePath}");
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new TextExtractionException("Failed to read image file: {$imagePath}");
        }

        $mimeType = $this->getMimeType($imagePath);
        $base64Data = base64_encode($imageData);

        Log::info('Starting image text extraction', [
            'file' => basename($imagePath),
            'mime_type' => $mimeType,
            'size' => strlen($imageData),
        ]);

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
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mimeType,
                                        'data' => $base64Data,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $this->buildExtractionPrompt(),
                                ],
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                $error = $response->json('error.message', 'Unknown API error');
                Log::error('Claude Vision API error', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new TextExtractionException("Claude Vision API error: {$error}");
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';
            $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

            Log::info('Image text extraction complete', [
                'file' => basename($imagePath),
                'tokens_used' => $tokensUsed,
                'text_length' => strlen($content),
            ]);

            return trim($content);
        } catch (ConnectionException $e) {
            Log::error('Claude Vision API connection error', ['error' => $e->getMessage()]);

            throw new TextExtractionException("Failed to connect to Claude Vision API: {$e->getMessage()}", previous: $e);
        } catch (RequestException $e) {
            Log::error('Claude Vision API request error', ['error' => $e->getMessage()]);

            throw new TextExtractionException("Claude Vision API request failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Build the prompt for text extraction.
     */
    private function buildExtractionPrompt(): string
    {
        return <<<'PROMPT'
Extract ALL text from this document image. This appears to be a contract or legal document.

Instructions:
1. Extract every piece of text visible in the image, maintaining the original structure and formatting as much as possible
2. Preserve paragraph breaks and section headers
3. If there are numbered or bulleted lists, maintain that formatting
4. Include all headers, footers, and page numbers if visible
5. If text is partially obscured or unclear, indicate with [unclear] but include your best interpretation
6. Do not summarize or interpret - just extract the raw text

Output ONLY the extracted text, nothing else. Do not add any commentary or explanations.
PROMPT;
    }

    /**
     * Get the MIME type of an image file.
     */
    private function getMimeType(string $filePath): string
    {
        $mimeType = mime_content_type($filePath);

        if (! $mimeType) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            return match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'application/octet-stream',
            };
        }

        return $mimeType;
    }
}
