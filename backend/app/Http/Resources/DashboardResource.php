<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        return [
            'stats' => $data['stats'],
            'upcoming_deadlines' => DashboardDeadlineResource::collection($data['upcoming_deadlines']),
            'recent_contracts' => $data['recent_contracts']->map(function (Contract $contract) {
                return [
                    'id' => $contract->id,
                    'title' => $contract->title,
                    'status' => $contract->status->value,
                    'overall_risk_level' => $contract->overall_risk_level?->value,
                    'created_at' => $contract->created_at->toIso8601String(),
                ];
            }),
        ];
    }
}
