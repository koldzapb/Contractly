<?php

declare(strict_types=1);

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

describe('clear chat history', function () {
    it('clears chat history for a contract', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(5)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Chat history cleared.',
                'messages_deleted' => 5,
            ]);

        $this->assertDatabaseMissing('chat_messages', [
            'contract_id' => $this->contract->id,
        ]);
    });

    it('returns zero when no messages exist', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Chat history cleared.',
                'messages_deleted' => 0,
            ]);
    });

    it('does not delete messages from other contracts', function () {
        $otherContract = Contract::factory()->for($this->user)->create();

        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(3)
            ->create();

        ChatMessage::factory()
            ->for($otherContract)
            ->for($this->user)
            ->count(2)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(200)
            ->assertJson(['messages_deleted' => 3]);

        $this->assertDatabaseCount('chat_messages', 2);
        $this->assertDatabaseHas('chat_messages', [
            'contract_id' => $otherContract->id,
        ]);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/contracts/non-existent-id/chat');

        $response->assertStatus(404);
    });

    it('returns 404 for contract belonging to other user', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$otherContract->id}/chat");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->deleteJson("/api/contracts/{$this->contract->id}/chat");

        $response->assertStatus(401);
    });
});
