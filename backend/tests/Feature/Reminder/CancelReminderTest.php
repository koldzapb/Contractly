<?php

declare(strict_types=1);

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\Reminder;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
});

describe('cancel reminder', function () {
    it('cancels a pending reminder', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'status' => ReminderStatus::CANCELLED->value,
        ]);
    });

    it('prevents cancelling sent reminders', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->sent()
            ->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only pending reminders can be cancelled.',
            ]);
    });

    it('prevents cancelling failed reminders', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->failed()
            ->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertStatus(422);
    });

    it('prevents cancelling already cancelled reminders', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->cancelled()
            ->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertStatus(422);
    });

    it('returns 404 for non-existent reminder', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders/non-existent-id/cancel');

        $response->assertNotFound();
    });

    it('returns 404 for reminders belonging to other users', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();
        $reminder = Reminder::factory()
            ->for($otherUser)
            ->for($otherContract)
            ->pending()
            ->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->postJson("/api/reminders/{$reminder->id}/cancel");

        $response->assertStatus(401);
    });
});
