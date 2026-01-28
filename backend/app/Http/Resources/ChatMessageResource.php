<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ChatMessage
 */
class ChatMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'content' => $this->content,
            'tokens_used' => $this->tokens_used,
            'is_off_topic' => $this->is_off_topic,
            'is_user_message' => $this->isUserMessage(),
            'is_assistant_message' => $this->isAssistantMessage(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
