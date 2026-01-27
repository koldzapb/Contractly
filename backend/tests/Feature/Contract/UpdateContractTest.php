<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('update contract', function () {
    it('updates contract title', function () {
        $contract = Contract::factory()->for($this->user)->create([
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/contracts/{$contract->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'title' => 'Updated Title',
        ]);
    });

    it('validates title is required', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/contracts/{$contract->id}", [
                'title' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('validates title max length', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/contracts/{$contract->id}", [
                'title' => str_repeat('a', 256),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/contracts/non-existent-id', [
                'title' => 'New Title',
            ]);

        $response->assertNotFound();
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/contracts/{$contract->id}", [
                'title' => 'New Title',
            ]);

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->putJson("/api/contracts/{$contract->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(401);
    });
});
