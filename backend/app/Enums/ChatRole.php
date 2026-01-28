<?php

declare(strict_types=1);

namespace App\Enums;

enum ChatRole: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ASSISTANT => 'Assistant',
        };
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public function isAssistant(): bool
    {
        return $this === self::ASSISTANT;
    }
}
