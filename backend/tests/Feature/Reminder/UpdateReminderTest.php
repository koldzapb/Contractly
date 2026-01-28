<?php

declare(strict_types=1);

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
    $this->analysis = ContractAnalysis::factory()->for($this->contract)->create();
    $this->deadline = ContractDeadline::factory()
        ->for($this->analysis, 'analysis')
        ->upcoming(30)
        ->create();
});

describe('update reminder', function () {
    it('updates reminder title', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->forDeadline($this->deadline)
            ->pending()
            ->create(['title' => 'Old title']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/reminders/{$reminder->id}", [
                'title' => 'New title',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'New title');

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'New title',
        ]);
    });

    it('updates days_before and recalculates remind_at', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->forDeadline($this->deadline)
            ->pending()
            ->create(['days_before' => 7]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/reminders/{$reminder->id}", [
                'days_before' => 14,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.days_before', 14);

        $reminder->refresh();
        $expectedDate = $this->deadline->deadline_date->subDays(14)->setTime(8, 0, 0);
        expect($reminder->remind_at->toDateString())->toBe($expectedDate->toDateString());
    });

    it('validates days_before minimum value', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/reminders/{$reminder->id}", [
                'days_before' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days_before']);
    });

    it('prevents updating non-pending reminders', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->sent()
            ->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/reminders/{$reminder->id}", [
                'title' => 'New title',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Only pending reminders can be updated.',
            ]);
    });

    it('returns 404 for non-existent reminder', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/reminders/non-existent-id', [
                'title' => 'New title',
            ]);

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
            ->putJson("/api/reminders/{$reminder->id}", [
                'title' => 'New title',
            ]);

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->putJson("/api/reminders/{$reminder->id}", [
            'title' => 'New title',
        ]);

        $response->assertStatus(401);
    });
});
