<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'contract_id' => Contract::factory(),
            'contract_deadline_id' => null,
            'title' => fake()->sentence(4),
            'remind_at' => fake()->dateTimeBetween('now', '+1 month'),
            'days_before' => fake()->randomElement([1, 3, 7, 14, 30]),
            'channel' => 'email',
            'status' => ReminderStatus::PENDING,
        ];
    }

    public function forDeadline(ContractDeadline $deadline): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_deadline_id' => $deadline->id,
            'contract_id' => $deadline->analysis->contract_id,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::PENDING,
            'sent_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::FAILED,
            'error_message' => fake()->sentence(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::CANCELLED,
        ]);
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::PENDING,
            'remind_at' => now()->subMinutes(5),
        ]);
    }

    public function upcoming(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReminderStatus::PENDING,
            'remind_at' => now()->addDays(fake()->numberBetween(1, $days)),
        ]);
    }
}
