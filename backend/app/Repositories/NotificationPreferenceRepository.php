<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Repositories\Contracts\NotificationPreferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<NotificationPreference>
 */
class NotificationPreferenceRepository extends BaseRepository implements NotificationPreferenceRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(NotificationPreference::class);
    }

    public function getForUser(User|int $user): ?NotificationPreference
    {
        return $this->query()
            ->where('user_id', $this->resolveUserId($user))
            ->first();
    }

    public function getOrCreateForUser(User|int $user): NotificationPreference
    {
        $userId = $this->resolveUserId($user);

        return $this->query()->firstOrCreate(
            ['user_id' => $userId],
            [
                'analysis_complete' => true,
                'deadline_reminder' => true,
                'deadline_days_before' => 7,
                'weekly_digest' => false,
                'contract_expiring' => true,
            ]
        );
    }

    public function create(array $data): NotificationPreference
    {
        return $this->query()->create($data);
    }

    public function update(NotificationPreference $preference, array $data): bool
    {
        return $preference->update($data);
    }

    public function wantsNotification(User|int $user, string $type): bool
    {
        $preference = $this->getOrCreateForUser($user);

        return match ($type) {
            'analysis_complete' => $preference->analysis_complete,
            'deadline_reminder' => $preference->deadline_reminder,
            'weekly_digest' => $preference->weekly_digest,
            'contract_expiring' => $preference->contract_expiring,
            default => false,
        };
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersWantingNotification(string $type): Collection
    {
        $column = match ($type) {
            'analysis_complete' => 'analysis_complete',
            'deadline_reminder' => 'deadline_reminder',
            'weekly_digest' => 'weekly_digest',
            'contract_expiring' => 'contract_expiring',
            default => null,
        };

        if ($column === null) {
            return new Collection();
        }

        return User::query()
            ->whereHas('notificationPreference', function ($query) use ($column) {
                $query->where($column, true);
            })
            ->get();
    }
}
