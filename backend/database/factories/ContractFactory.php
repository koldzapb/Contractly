<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->words(3, true).'.pdf';

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'original_filename' => $filename,
            'file_path' => 'contracts/'.Str::uuid().'/'.$filename,
            'file_size' => fake()->numberBetween(10000, 5000000),
            'page_count' => fake()->numberBetween(1, 50),
            'status' => ContractStatus::PENDING,
            'language_detected' => 'en',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::PENDING,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::PROCESSING,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::COMPLETED,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::FAILED,
            'error_message' => fake()->sentence(),
        ]);
    }
}
