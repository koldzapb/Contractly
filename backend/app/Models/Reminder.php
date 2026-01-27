<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReminderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string         $id
 * @property int            $user_id
 * @property string         $contract_id
 * @property string|null    $contract_deadline_id
 * @property string         $title
 * @property Carbon         $remind_at
 * @property int            $days_before
 * @property string         $channel
 * @property ReminderStatus $status
 * @property Carbon|null    $sent_at
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 */
class Reminder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'contract_id',
        'contract_deadline_id',
        'title',
        'remind_at',
        'days_before',
        'channel',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReminderStatus::class,
            'remind_at' => 'datetime',
            'sent_at' => 'datetime',
            'days_before' => 'integer',
        ];
    }

    /**
     * The user who owns this reminder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The contract this reminder is for.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * The deadline this reminder is for (optional).
     */
    public function deadline(): BelongsTo
    {
        return $this->belongsTo(ContractDeadline::class, 'contract_deadline_id');
    }

    /**
     * Check if the reminder is pending.
     */
    public function isPending(): bool
    {
        return $this->status === ReminderStatus::PENDING;
    }

    /**
     * Check if the reminder has been sent.
     */
    public function isSent(): bool
    {
        return $this->status === ReminderStatus::SENT;
    }

    /**
     * Check if the reminder is due to be sent.
     */
    public function isDue(): bool
    {
        return $this->isPending() && $this->remind_at->isPast();
    }
}
