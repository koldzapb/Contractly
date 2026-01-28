<?php

declare(strict_types=1);

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
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

describe('create reminder', function () {
    it('creates a reminder for a deadline', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
                'days_before' => 7,
                'title' => 'Custom reminder title',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'remind_at',
                    'days_before',
                    'status',
                    'contract',
                    'deadline',
                ],
            ])
            ->assertJsonPath('data.title', 'Custom reminder title')
            ->assertJsonPath('data.days_before', 7)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $this->user->id,
            'contract_id' => $this->contract->id,
            'contract_deadline_id' => $this->deadline->id,
            'days_before' => 7,
            'status' => ReminderStatus::PENDING->value,
        ]);
    });

    it('uses default title from deadline when not provided', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
                'days_before' => 7,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', "Reminder: {$this->deadline->title}");
    });

    it('calculates remind_at from deadline date and days_before', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
                'days_before' => 7,
            ]);

        $response->assertStatus(201);

        $reminder = $this->user->reminders()->first();
        $expectedDate = $this->deadline->deadline_date->subDays(7)->setTime(8, 0, 0);

        expect($reminder->remind_at->toDateString())->toBe($expectedDate->toDateString());
    });

    it('validates contract_deadline_id is required', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'days_before' => 7,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_deadline_id']);
    });

    it('validates days_before is required', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days_before']);
    });

    it('validates days_before minimum value', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
                'days_before' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days_before']);
    });

    it('validates days_before maximum value', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $this->deadline->id,
                'days_before' => 400,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days_before']);
    });

    it('validates deadline belongs to user contract', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();
        $otherAnalysis = ContractAnalysis::factory()->for($otherContract)->create();
        $otherDeadline = ContractDeadline::factory()->for($otherAnalysis, 'analysis')->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $otherDeadline->id,
                'days_before' => 7,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_deadline_id']);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/reminders', [
            'contract_deadline_id' => $this->deadline->id,
            'days_before' => 7,
        ]);

        $response->assertStatus(401);
    });

    it('accepts and saves deadline_date for deadlines without a date', function () {
        // Create a deadline without a date
        $deadlineWithoutDate = ContractDeadline::factory()
            ->for($this->analysis, 'analysis')
            ->create(['deadline_date' => null]);

        $futureDate = now()->addDays(30)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $deadlineWithoutDate->id,
                'days_before' => 7,
                'deadline_date' => $futureDate,
            ]);

        $response->assertStatus(201);

        // Check that the deadline was updated with the date
        $deadlineWithoutDate->refresh();
        expect($deadlineWithoutDate->deadline_date)->not->toBeNull();
        expect($deadlineWithoutDate->deadline_date->toDateString())->toBe($futureDate);

        // Check that remind_at is calculated correctly
        $reminder = $this->user->reminders()->first();
        $expectedRemindAt = now()->addDays(30)->subDays(7)->setTime(8, 0, 0);
        expect($reminder->remind_at->toDateString())->toBe($expectedRemindAt->toDateString());
    });

    it('validates deadline_date must be today or in the future', function () {
        $deadlineWithoutDate = ContractDeadline::factory()
            ->for($this->analysis, 'analysis')
            ->create(['deadline_date' => null]);

        $pastDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->postJson('/api/reminders', [
                'contract_deadline_id' => $deadlineWithoutDate->id,
                'days_before' => 7,
                'deadline_date' => $pastDate,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deadline_date']);
    });
});
