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

describe('list reminders', function () {
    it('returns paginated reminders for the user', function () {
        Reminder::factory()
            ->count(3)
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/reminders');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'remind_at',
                        'days_before',
                        'status',
                        'status_label',
                        'contract',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'total',
                ],
            ]);
    });

    it('orders reminders by remind_at', function () {
        $reminder1 = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create(['remind_at' => now()->addDays(10)]);

        $reminder2 = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create(['remind_at' => now()->addDays(5)]);

        $reminder3 = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create(['remind_at' => now()->addDays(15)]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reminders');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        expect($ids)->toBe([$reminder2->id, $reminder1->id, $reminder3->id]);
    });

    it('does not return reminders from other users', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();

        Reminder::factory()
            ->for($otherUser)
            ->for($otherContract)
            ->count(3)
            ->create();

        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->count(2)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/reminders');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('paginates results', function () {
        Reminder::factory()
            ->count(20)
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/reminders?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 5);
    });

    it('returns empty array when no reminders exist', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/reminders');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/reminders');

        $response->assertStatus(401);
    });
});
