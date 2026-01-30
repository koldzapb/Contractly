<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotificationPreference
 */
class NotificationPreferenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'analysis_complete' => $this->analysis_complete,
            'deadline_reminder' => $this->deadline_reminder,
            'deadline_days_before' => $this->deadline_days_before,
            'weekly_digest' => $this->weekly_digest,
            'contract_expiring' => $this->contract_expiring,
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
