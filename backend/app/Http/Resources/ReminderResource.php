<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reminder
 */
class ReminderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'remind_at' => $this->remind_at->toIso8601String(),
            'days_before' => $this->days_before,
            'channel' => $this->channel,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'is_pending' => $this->isPending(),
            'is_sent' => $this->isSent(),
            'is_due' => $this->isDue(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Related contract (minimal info)
            'contract' => $this->when($this->relationLoaded('contract') && $this->resource->contract !== null, function () {
                /** @var \App\Models\Contract $contract */
                $contract = $this->resource->contract;

                return [
                    'id' => $contract->id,
                    'title' => $contract->title,
                ];
            }),

            // Related deadline (minimal info)
            'deadline' => $this->when($this->relationLoaded('deadline') && $this->resource->deadline !== null, function () {
                /** @var \App\Models\ContractDeadline $deadline */
                $deadline = $this->resource->deadline;

                return [
                    'id' => $deadline->id,
                    'title' => $deadline->title,
                    'deadline_date' => $deadline->deadline_date?->toIso8601String(),
                    'deadline_type' => $deadline->deadline_type->value,
                    'deadline_type_label' => $deadline->deadline_type->label(),
                ];
            }),
        ];
    }
}
