<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string         $id
 * @property int            $user_id
 * @property string         $title
 * @property string         $original_filename
 * @property string         $file_path
 * @property int            $file_size
 * @property int|null       $page_count
 * @property ContractStatus $status
 * @property RiskLevel|null $overall_risk_level
 * @property string|null    $language_detected
 * @property string|null    $error_message
 * @property Carbon|null    $analyzed_at
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 * @property Carbon|null    $deleted_at
 */
class Contract extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'file_path',
        'file_size',
        'page_count',
        'status',
        'overall_risk_level',
        'language_detected',
        'error_message',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'overall_risk_level' => RiskLevel::class,
            'file_size' => 'integer',
            'page_count' => 'integer',
            'analyzed_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this contract.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The AI analysis for this contract.
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(ContractAnalysis::class);
    }

    /**
     * Clauses extracted from this contract (through analysis).
     */
    public function clauses(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContractClause::class,
            ContractAnalysis::class,
            'contract_id',
            'contract_analysis_id',
        );
    }

    /**
     * Deadlines extracted from this contract (through analysis).
     */
    public function deadlines(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContractDeadline::class,
            ContractAnalysis::class,
            'contract_id',
            'contract_analysis_id',
        );
    }

    /**
     * Reminders set for this contract.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Check if the contract is still being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === ContractStatus::PROCESSING;
    }

    /**
     * Check if the contract analysis is complete.
     */
    public function isCompleted(): bool
    {
        return $this->status === ContractStatus::COMPLETED;
    }

    /**
     * Check if the contract analysis failed.
     */
    public function isFailed(): bool
    {
        return $this->status === ContractStatus::FAILED;
    }

    /**
     * Check if the contract is pending analysis.
     */
    public function isPending(): bool
    {
        return $this->status === ContractStatus::PENDING;
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFileSizeForHumansAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }
}
