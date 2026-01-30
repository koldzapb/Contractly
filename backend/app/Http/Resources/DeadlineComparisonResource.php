<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\DeadlineComparisonResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeadlineComparisonResult
 */
class DeadlineComparisonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'match_type' => $this->matchType,
            'days_difference' => $this->daysDifference,
            'has_date_difference' => $this->hasDateDifference(),
            'deadline_a' => $this->deadlineA !== null ? [
                'id' => $this->deadlineA->id,
                'deadline_type' => $this->deadlineA->deadline_type->value,
                'deadline_type_label' => $this->deadlineA->deadline_type->label(),
                'title' => $this->deadlineA->title,
                'description' => $this->deadlineA->description,
                'deadline_date' => $this->deadlineA->deadline_date?->toDateString(),
                'is_recurring' => $this->deadlineA->is_recurring,
                'recurrence_pattern' => $this->deadlineA->recurrence_pattern,
            ] : null,
            'deadline_b' => $this->deadlineB !== null ? [
                'id' => $this->deadlineB->id,
                'deadline_type' => $this->deadlineB->deadline_type->value,
                'deadline_type_label' => $this->deadlineB->deadline_type->label(),
                'title' => $this->deadlineB->title,
                'description' => $this->deadlineB->description,
                'deadline_date' => $this->deadlineB->deadline_date?->toDateString(),
                'is_recurring' => $this->deadlineB->is_recurring,
                'recurrence_pattern' => $this->deadlineB->recurrence_pattern,
            ] : null,
        ];
    }
}
