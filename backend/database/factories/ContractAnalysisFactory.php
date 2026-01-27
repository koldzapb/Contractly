<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractAnalysis>
 */
class ContractAnalysisFactory extends Factory
{
    protected $model = ContractAnalysis::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'summary' => fake()->paragraphs(2, true),
            'overall_risk_level' => fake()->randomElement(RiskLevel::cases()),
            'key_findings' => [
                fake()->sentence(),
                fake()->sentence(),
                fake()->sentence(),
            ],
            'ai_model' => 'claude-3-5-sonnet-20241022',
            'tokens_used' => fake()->numberBetween(1000, 10000),
            'processing_time_ms' => fake()->numberBetween(2000, 30000),
            'raw_response' => ['analysis' => 'test response'],
        ];
    }

    public function lowRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_risk_level' => RiskLevel::LOW,
        ]);
    }

    public function mediumRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_risk_level' => RiskLevel::MEDIUM,
        ]);
    }

    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
    }
}
