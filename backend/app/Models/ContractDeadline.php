<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeadlineType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property string       $id
 * @property string       $contract_analysis_id
 * @property DeadlineType $deadline_type
 * @property string       $title
 * @property string|null  $description
 * @property Carbon|null  $deadline_date
 * @property string       $source_text
 * @property bool         $is_recurring
 * @property string|null  $recurrence_pattern
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 */
class ContractDeadline extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_analysis_id',
        'deadline_type',
        'title',
        'description',
        'deadline_date',
        'source_text',
        'is_recurring',
        'recurrence_pattern',
    ];

    protected function casts(): array
    {
        return [
            'deadline_type' => DeadlineType::class,
            'deadline_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * The analysis this deadline belongs to.
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(ContractAnalysis::class, 'contract_analysis_id');
    }

    /**
     * The contract this deadline belongs to (through analysis).
     */
    public function contract(): HasOneThrough
    {
        return $this->hasOneThrough(
            Contract::class,
            ContractAnalysis::class,
            'id',
            'id',
            'contract_analysis_id',
            'contract_id',
        );
    }

    /**
     * Reminders set for this deadline.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'contract_deadline_id');
    }

    /**
     * Check if the deadline has passed.
     */
    public function isPast(): bool
    {
        return $this->deadline_date !== null
            && $this->deadline_date->isPast();
    }

    /**
     * Check if the deadline is upcoming (within specified days).
     */
    public function isUpcoming(int $days = 30): bool
    {
        if ($this->deadline_date === null) {
            return false;
        }

        return $this->deadline_date->isFuture()
            && $this->deadline_date->diffInDays(now()) <= $days;
    }

    /**
     * Get the number of days until the deadline.
     */
    public function getDaysUntilAttribute(): ?int
    {
        if ($this->deadline_date === null) {
            return null;
        }

        return (int) now()->diffInDays($this->deadline_date, false);
    }

    /**
     * Get urgency level based on how close the deadline is.
     */
    public function getUrgencyAttribute(): string
    {
        $days = $this->days_until;

        if ($days === null) {
            return 'unknown';
        }

        return match (true) {
            $days < 0 => 'overdue',
            $days <= 7 => 'critical',
            $days <= 14 => 'high',
            $days <= 30 => 'medium',
            default => 'low',
        };
    }
}
