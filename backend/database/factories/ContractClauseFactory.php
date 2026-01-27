<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClauseType;
use App\Enums\RiskLevel;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractClause>
 */
class ContractClauseFactory extends Factory
{
    protected $model = ContractClause::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_analysis_id' => ContractAnalysis::factory(),
            'clause_type' => fake()->randomElement(ClauseType::cases()),
            'original_text' => fake()->paragraph(3),
            'plain_explanation' => fake()->paragraph(2),
            'risk_level' => fake()->randomElement(RiskLevel::cases()),
            'risk_reason' => fake()->optional(0.7)->sentence(),
            'position_index' => fake()->numberBetween(1, 20),
        ];
    }

    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'clause_type' => ClauseType::PAYMENT,
        ]);
    }

    public function termination(): static
    {
        return $this->state(fn (array $attributes) => [
            'clause_type' => ClauseType::TERMINATION,
        ]);
    }

    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'clause_type' => ClauseType::LIABILITY,
        ]);
    }

    public function noRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::NONE,
            'risk_reason' => null,
        ]);
    }

    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::LOW,
            'risk_reason' => fake()->sentence(),
        ]);
    }

    public function mediumRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::MEDIUM,
            'risk_reason' => fake()->sentence(),
        ]);
    }

    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => RiskLevel::HIGH,
            'risk_reason' => fake()->sentence(),
        ]);
    }
}
