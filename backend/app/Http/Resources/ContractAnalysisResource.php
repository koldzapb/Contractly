<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractAnalysis
 */
class ContractAnalysisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'summary' => $this->summary,
            'overall_risk_level' => $this->overall_risk_level->value,
            'key_findings' => $this->key_findings,
            'ai_model' => $this->ai_model,
            'tokens_used' => $this->tokens_used,
            'processing_time_ms' => $this->processing_time_ms,
            'created_at' => $this->created_at->toIso8601String(),

            // Relationships (when loaded)
            'clauses' => ContractClauseResource::collection($this->whenLoaded('clauses')),
            'deadlines' => ContractDeadlineResource::collection($this->whenLoaded('deadlines')),
        ];
    }
}
