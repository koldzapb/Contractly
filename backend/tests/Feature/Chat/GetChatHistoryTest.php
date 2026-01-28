<?php

declare(strict_types=1);

use App\Enums\ChatRole;
use App\Enums\ContractStatus;
use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()
        ->for($this->user)
        ->create(['status' => ContractStatus::COMPLETED]);
});

describe('get chat history', function () {
    it('returns chat history for a contract', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->user()
            ->create(['content' => 'What are the payment terms?']);

        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->assistant()
            ->create(['content' => 'The payment terms are...']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'role',
                        'role_label',
                        'content',
                        'tokens_used',
                        'is_off_topic',
                        'is_user_message',
                        'is_assistant_message',
                        'created_at',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    });

    it('returns messages in chronological order', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()->subMinutes(2)]);

        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()]);

        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()->subMinute()]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200);

        $data = $response->json('data');
        expect($data[0]['created_at'] < $data[1]['created_at'])->toBeTrue()
            ->and($data[1]['created_at'] < $data[2]['created_at'])->toBeTrue();
    });

    it('returns empty array when no messages exist', function () {
        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts/non-existent-id/chat');

        $response->assertStatus(404);
    });

    it('returns 404 for contract belonging to other user', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$otherContract->id}/chat");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->getJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(401);
    });
});
