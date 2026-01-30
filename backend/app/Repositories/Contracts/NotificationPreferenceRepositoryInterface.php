<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\NotificationPreference;
use App\Models\User;

interface NotificationPreferenceRepositoryInterface
{
    /**
     * Get notification preferences for a user.
     */
    public function getForUser(User|int $user): ?NotificationPreference;

    /**
     * Get or create notification preferences for a user with defaults.
     */
    public function getOrCreateForUser(User|int $user): NotificationPreference;

    /**
     * Create notification preferences for a user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): NotificationPreference;

    /**
     * Update notification preferences.
     *
     * @param array<string, mixed> $data
     */
    public function update(NotificationPreference $preference, array $data): bool;

    /**
     * Check if a user wants a specific notification type.
     */
    public function wantsNotification(User|int $user, string $type): bool;

    /**
     * Get all users who want a specific notification type.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getUsersWantingNotification(string $type): \Illuminate\Database\Eloquent\Collection;
}
