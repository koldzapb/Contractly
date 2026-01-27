<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClauseType;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property string      $id
 * @property string      $contract_analysis_id
 * @property ClauseType  $clause_type
 * @property string      $original_text
 * @property string      $plain_explanation
 * @property RiskLevel   $risk_level
 * @property string|null $risk_reason
 * @property int|null    $page_number
 * @property int         $position_index
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class ContractClause extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_analysis_id',
        'clause_type',
        'original_text',
        'plain_explanation',
        'risk_level',
        'risk_reason',
        'page_number',
        'position_index',
    ];

    protected function casts(): array
    {
        return [
            'clause_type' => ClauseType::class,
            'risk_level' => RiskLevel::class,
            'page_number' => 'integer',
            'position_index' => 'integer',
        ];
    }

    /**
     * The analysis this clause belongs to.
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(ContractAnalysis::class, 'contract_analysis_id');
    }

    /**
     * The contract this clause belongs to (through analysis).
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
     * Check if this clause has elevated risk.
     */
    public function hasRisk(): bool
    {
        return $this->risk_level !== RiskLevel::NONE;
    }

    /**
     * Check if this clause is high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->risk_level === RiskLevel::HIGH;
    }

    /**
     * Check if this clause type typically requires attention.
     */
    public function requiresAttention(): bool
    {
        return in_array($this->clause_type, ClauseType::highAttentionTypes(), true)
            || $this->isHighRisk();
    }

    /**
     * Get a truncated version of the original text.
     */
    public function getTruncatedTextAttribute(): string
    {
        return strlen($this->original_text) > 200
            ? substr($this->original_text, 0, 200).'...'
            : $this->original_text;
    }
}
