<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractDeadline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractDeadline
 */
class DashboardDeadlineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\ContractAnalysis|null $analysis */
        $analysis = $this->whenLoaded('analysis');

        /** @var \App\Models\Contract|null $contract */
        $contract = $analysis?->relationLoaded('contract') ? $analysis->contract : null;

        return [
            'id' => $this->id,
            'deadline_type' => $this->deadline_type->value,
            'deadline_type_label' => $this->deadline_type->label(),
            'title' => $this->title,
            'deadline_date' => $this->deadline_date?->toDateString(),
            'days_until' => $this->days_until,
            'urgency' => $this->urgency,
            'is_past' => $this->isPast(),
            'contract' => $contract !== null ? [
                'id' => $contract->id,
                'title' => $contract->title,
            ] : null,
        ];
    }
}
