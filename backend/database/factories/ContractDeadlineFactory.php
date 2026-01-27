<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeadlineType;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDeadline>
 */
class ContractDeadlineFactory extends Factory
{
    protected $model = ContractDeadline::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_analysis_id' => ContractAnalysis::factory(),
            'deadline_type' => fake()->randomElement(DeadlineType::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'deadline_date' => fake()->dateTimeBetween('now', '+1 year'),
            'source_text' => fake()->sentence(),
            'is_recurring' => false,
            'recurrence_pattern' => null,
        ];
    }

    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline_type' => DeadlineType::PAYMENT,
        ]);
    }

    public function renewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline_type' => DeadlineType::RENEWAL,
        ]);
    }

    public function terminationNotice(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline_type' => DeadlineType::TERMINATION_NOTICE,
        ]);
    }

    public function upcoming(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline_date' => now()->addDays(fake()->numberBetween(1, $days)),
        ]);
    }

    public function past(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline_date' => now()->subDays(fake()->numberBetween(1, $days)),
        ]);
    }

    public function recurring(string $pattern = 'monthly'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_pattern' => $pattern,
        ]);
    }
}
