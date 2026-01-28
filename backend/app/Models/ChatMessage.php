<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChatRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string    $id
 * @property string    $contract_id
 * @property int       $user_id
 * @property ChatRole  $role
 * @property string    $content
 * @property int|null  $tokens_used
 * @property bool      $is_off_topic
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 */
class ChatMessage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'user_id',
        'role',
        'content',
        'tokens_used',
        'is_off_topic',
    ];

    protected function casts(): array
    {
        return [
            'role' => ChatRole::class,
            'tokens_used' => 'integer',
            'is_off_topic' => 'boolean',
        ];
    }

    /**
     * The contract this message belongs to.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * The user who sent or received this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a user message.
     */
    public function isUserMessage(): bool
    {
        return $this->role === ChatRole::USER;
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === ChatRole::ASSISTANT;
    }
}
