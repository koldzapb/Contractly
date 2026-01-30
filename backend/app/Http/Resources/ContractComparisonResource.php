<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\ContractComparisonResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractComparisonResult
 */
class ContractComparisonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contract_a' => [
                'id' => $this->contractA->id,
                'title' => $this->contractA->title,
                'overall_risk_level' => $this->contractA->overall_risk_level?->value,
            ],
            'contract_b' => [
                'id' => $this->contractB->id,
                'title' => $this->contractB->title,
                'overall_risk_level' => $this->contractB->overall_risk_level?->value,
            ],
            'similarity_score' => round($this->similarityScore, 3),
            'risk_comparison' => [
                'contract_a' => $this->contractA->overall_risk_level?->value,
                'contract_b' => $this->contractB->overall_risk_level?->value,
                'changed' => $this->hasRiskChange(),
            ],
            'clauses' => [
                'matched' => ClauseComparisonResource::collection(
                    collect($this->getMatchedClauses())->values()
                ),
                'only_in_a' => ClauseComparisonResource::collection(
                    collect($this->getClausesOnlyInA())->values()
                ),
                'only_in_b' => ClauseComparisonResource::collection(
                    collect($this->getClausesOnlyInB())->values()
                ),
            ],
            'deadlines' => [
                'matched' => DeadlineComparisonResource::collection(
                    collect($this->getMatchedDeadlines())->values()
                ),
                'only_in_a' => DeadlineComparisonResource::collection(
                    collect($this->getDeadlinesOnlyInA())->values()
                ),
                'only_in_b' => DeadlineComparisonResource::collection(
                    collect($this->getDeadlinesOnlyInB())->values()
                ),
            ],
            'stats' => $this->getStats(),
        ];
    }
}
