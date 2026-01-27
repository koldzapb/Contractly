<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('contracts');
    $this->user = User::factory()->create();
});

describe('delete contract', function () {
    it('soft deletes a contract', function () {
        $contract = Contract::factory()->for($this->user)->create();

        // Create a fake file
        Storage::disk('contracts')->put($contract->file_path, 'fake content');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$contract->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Contract deleted successfully.',
            ]);

        // Contract should be soft deleted
        $this->assertSoftDeleted('contracts', [
            'id' => $contract->id,
        ]);

        // File should be deleted
        Storage::disk('contracts')->assertMissing($contract->file_path);
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/contracts/non-existent-id');

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Contract not found.',
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$contract->id}");

        $response->assertNotFound();
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->deleteJson("/api/contracts/{$contract->id}");

        $response->assertStatus(401);
    });
});
