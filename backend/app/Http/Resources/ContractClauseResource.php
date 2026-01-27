<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractClause;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractClause
 */
class ContractClauseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'clause_type' => $this->clause_type->value,
            'clause_type_label' => $this->clause_type->label(),
            'original_text' => $this->original_text,
            'plain_explanation' => $this->plain_explanation,
            'risk_level' => $this->risk_level->value,
            'risk_reason' => $this->risk_reason,
            'page_number' => $this->page_number,
            'position_index' => $this->position_index,
        ];
    }
}
