<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\ClauseComparisonResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClauseComparisonResult
 */
class ClauseComparisonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'match_type' => $this->matchType,
            'similarity' => $this->similarity !== null ? round($this->similarity, 3) : null,
            'has_risk_difference' => $this->hasRiskDifference(),
            'clause_a' => $this->clauseA !== null ? [
                'id' => $this->clauseA->id,
                'clause_type' => $this->clauseA->clause_type->value,
                'clause_type_label' => $this->clauseA->clause_type->label(),
                'original_text' => $this->clauseA->original_text,
                'plain_explanation' => $this->clauseA->plain_explanation,
                'risk_level' => $this->clauseA->risk_level->value,
                'risk_reason' => $this->clauseA->risk_reason,
                'page_number' => $this->clauseA->page_number,
            ] : null,
            'clause_b' => $this->clauseB !== null ? [
                'id' => $this->clauseB->id,
                'clause_type' => $this->clauseB->clause_type->value,
                'clause_type_label' => $this->clauseB->clause_type->label(),
                'original_text' => $this->clauseB->original_text,
                'plain_explanation' => $this->clauseB->plain_explanation,
                'risk_level' => $this->clauseB->risk_level->value,
                'risk_reason' => $this->clauseB->risk_reason,
                'page_number' => $this->clauseB->page_number,
            ] : null,
        ];
    }
}
