<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

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

    it('deletes associated reminders when contract is deleted', function () {
        $contract = Contract::factory()->for($this->user)->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        $deadline = ContractDeadline::factory()->for($analysis, 'analysis')->create();

        // Create reminders for this contract
        $reminder1 = Reminder::factory()->for($this->user)->create([
            'contract_id' => $contract->id,
            'contract_deadline_id' => $deadline->id,
        ]);
        $reminder2 = Reminder::factory()->for($this->user)->create([
            'contract_id' => $contract->id,
            'contract_deadline_id' => $deadline->id,
        ]);

        // Create a reminder for another contract (should not be deleted)
        $otherContract = Contract::factory()->for($this->user)->create();
        $otherReminder = Reminder::factory()->for($this->user)->create([
            'contract_id' => $otherContract->id,
        ]);

        Storage::disk('contracts')->put($contract->file_path, 'fake content');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/contracts/{$contract->id}");

        $response->assertOk();

        // Reminders for deleted contract should be gone
        $this->assertDatabaseMissing('reminders', ['id' => $reminder1->id]);
        $this->assertDatabaseMissing('reminders', ['id' => $reminder2->id]);

        // Reminder for other contract should still exist
        $this->assertDatabaseHas('reminders', ['id' => $otherReminder->id]);
    });
});
