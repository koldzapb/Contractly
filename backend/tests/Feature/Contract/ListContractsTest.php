<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('list contracts', function () {
    it('returns paginated contracts for the user', function () {
        Contract::factory()->for($this->user)->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'original_filename',
                        'file_size',
                        'status',
                        'created_at',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    });

    it('returns contracts ordered by latest first', function () {
        $oldest = Contract::factory()->for($this->user)->create([
            'created_at' => now()->subDays(2),
        ]);
        $newest = Contract::factory()->for($this->user)->create([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        expect($ids[0])->toBe($newest->id)
            ->and($ids[1])->toBe($oldest->id);
    });

    it('does not return contracts from other users', function () {
        $otherUser = User::factory()->create();
        Contract::factory()->for($this->user)->count(2)->create();
        Contract::factory()->for($otherUser)->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('paginates results', function () {
        Contract::factory()->for($this->user)->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.last_page', 3);
    });

    it('returns empty array when no contracts exist', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/contracts');

        $response->assertStatus(401);
    });
});
