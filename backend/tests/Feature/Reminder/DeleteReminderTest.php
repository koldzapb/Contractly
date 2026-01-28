<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\Reminder;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
});

describe('delete reminder', function () {
    it('deletes a reminder', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/reminders/{$reminder->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Reminder deleted successfully.',
            ]);

        $this->assertDatabaseMissing('reminders', [
            'id' => $reminder->id,
        ]);
    });

    it('can delete reminders in any status', function () {
        $sentReminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->sent()
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/reminders/{$sentReminder->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('reminders', [
            'id' => $sentReminder->id,
        ]);
    });

    it('returns 404 for non-existent reminder', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/reminders/non-existent-id');

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Reminder not found.',
            ]);
    });

    it('returns 404 for reminders belonging to other users', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();
        $reminder = Reminder::factory()
            ->for($otherUser)
            ->for($otherContract)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/reminders/{$reminder->id}");

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->deleteJson("/api/reminders/{$reminder->id}");

        $response->assertStatus(401);
    });
});
