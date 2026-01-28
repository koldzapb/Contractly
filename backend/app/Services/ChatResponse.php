<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Data transfer object for chat response.
 */
readonly class ChatResponse
{
    public function __construct(
        public string $content,
        public int $tokensUsed,
        public bool $isOffTopic = false,
    ) {}
}
