<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Resources\NotificationPreferenceResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    /**
     * Get notification preferences for the authenticated user.
     */
    public function show(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $preferences = $this->notifications->getPreferences($user);

        return NotificationPreferenceResource::make($preferences)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update notification preferences for the authenticated user.
     */
    public function update(UpdateNotificationPreferencesRequest $request): NotificationPreferenceResource
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $preferences = $this->notifications->updatePreferences($user, $request->validated());

        return NotificationPreferenceResource::make($preferences);
    }

    /**
     * Reset notification preferences to defaults.
     */
    public function reset(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $defaults = [
            'analysis_complete' => true,
            'deadline_reminder' => true,
            'deadline_days_before' => 7,
            'weekly_digest' => false,
            'contract_expiring' => true,
        ];

        $this->notifications->updatePreferences($user, $defaults);

        return response()->json(['message' => 'Notification preferences reset to defaults.']);
    }
}
