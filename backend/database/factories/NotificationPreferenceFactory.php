<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'analysis_complete' => true,
            'deadline_reminder' => true,
            'deadline_days_before' => 7,
            'weekly_digest' => false,
            'contract_expiring' => true,
        ];
    }

    /**
     * All notifications enabled.
     */
    public function allEnabled(): static
    {
        return $this->state(fn () => [
            'analysis_complete' => true,
            'deadline_reminder' => true,
            'weekly_digest' => true,
            'contract_expiring' => true,
        ]);
    }

    /**
     * All notifications disabled.
     */
    public function allDisabled(): static
    {
        return $this->state(fn () => [
            'analysis_complete' => false,
            'deadline_reminder' => false,
            'weekly_digest' => false,
            'contract_expiring' => false,
        ]);
    }

    /**
     * Weekly digest enabled.
     */
    public function withWeeklyDigest(): static
    {
        return $this->state(fn () => [
            'weekly_digest' => true,
        ]);
    }
}
