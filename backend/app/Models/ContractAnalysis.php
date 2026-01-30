<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string                                              $id
 * @property string                                              $contract_id
 * @property string                                              $summary
 * @property RiskLevel                                           $overall_risk_level
 * @property array<int, string>                                  $key_findings
 * @property string                                              $ai_model
 * @property int                                                 $tokens_used
 * @property int                                                 $processing_time_ms
 * @property array<string, mixed>|null                           $raw_response
 * @property Carbon                                              $created_at
 * @property Carbon                                              $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContractClause>   $clauses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContractDeadline> $deadlines
 */
class ContractAnalysis extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contract_analyses';

    protected $fillable = [
        'contract_id',
        'summary',
        'overall_risk_level',
        'key_findings',
        'ai_model',
        'tokens_used',
        'processing_time_ms',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'overall_risk_level' => RiskLevel::class,
            'key_findings' => 'array',
            'raw_response' => 'array',
            'tokens_used' => 'integer',
            'processing_time_ms' => 'integer',
        ];
    }

    /**
     * The contract this analysis belongs to.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Clauses extracted during this analysis.
     */
    public function clauses(): HasMany
    {
        return $this->hasMany(ContractClause::class)->orderBy('position_index');
    }

    /**
     * Deadlines extracted during this analysis.
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(ContractDeadline::class)->orderBy('deadline_date');
    }

    /**
     * Get processing time in seconds.
     */
    public function getProcessingTimeSecondsAttribute(): float
    {
        return $this->processing_time_ms / 1000;
    }
}
