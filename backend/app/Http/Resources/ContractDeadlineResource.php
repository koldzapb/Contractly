<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContractDeadline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractDeadline
 */
class ContractDeadlineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'deadline_type' => $this->deadline_type->value,
            'deadline_type_label' => $this->deadline_type->label(),
            'title' => $this->title,
            'description' => $this->description,
            'deadline_date' => $this->deadline_date?->toDateString(),
            'source_text' => $this->source_text,
            'is_recurring' => $this->is_recurring,
            'recurrence_pattern' => $this->recurrence_pattern,
            'days_until' => $this->days_until,
            'urgency' => $this->urgency,
            'is_past' => $this->isPast(),
            'has_reminder' => $this->reminders()->exists(),
        ];
    }
}
