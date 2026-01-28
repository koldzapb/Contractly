<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ChatRole;
use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement([ChatRole::USER, ChatRole::ASSISTANT]),
            'content' => fake()->paragraph(),
            'tokens_used' => null,
            'is_off_topic' => false,
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => ChatRole::USER,
            'tokens_used' => null,
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => ChatRole::ASSISTANT,
            'tokens_used' => fake()->numberBetween(100, 1000),
        ]);
    }

    public function offTopic(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_off_topic' => true,
        ]);
    }

    public function withContent(string $content): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $content,
        ]);
    }
}
