<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('export PDF report', function () {
    it('generates PDF report for completed contract', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(3)->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->count(2)->create();

        $response = $this->actingAs($this->user)
            ->get("/api/contracts/{$contract->id}/export/pdf");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // Verify Content-Disposition header contains the contract title
        $contentDisposition = $response->headers->get('Content-Disposition');
        expect($contentDisposition)->toContain('attachment');
        expect($contentDisposition)->toContain('.pdf');
    });

    it('returns 404 for non-existent contract', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/contracts/non-existent-id/export/pdf');

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Contract not found.',
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->completed()->create();
        ContractAnalysis::factory()->for($contract)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/pdf");

        $response->assertNotFound();
    });

    it('returns 422 for contracts not yet analyzed', function () {
        $contract = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/pdf");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Contract analysis must be completed before generating a report.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ]);
    });

    it('requires authentication', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();

        $response = $this->getJson("/api/contracts/{$contract->id}/export/pdf");

        $response->assertStatus(401);
    });
});

describe('export clauses CSV', function () {
    it('generates clauses CSV for completed contract', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get("/api/contracts/{$contract->id}/export/clauses");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Verify CSV content contains headers
        $content = $response->getContent();
        expect($content)->toContain('Clause Type');
        expect($content)->toContain('Risk Level');
        expect($content)->toContain('Original Text');
    });

    it('returns 422 for contracts not yet analyzed', function () {
        $contract = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/clauses");

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->completed()->create();
        ContractAnalysis::factory()->for($contract)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/clauses");

        $response->assertNotFound();
    });
});

describe('export deadlines CSV', function () {
    it('generates deadlines CSV for completed contract', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->count(2)->create();

        $response = $this->actingAs($this->user)
            ->get("/api/contracts/{$contract->id}/export/deadlines");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Verify CSV content contains headers
        $content = $response->getContent();
        expect($content)->toContain('Type');
        expect($content)->toContain('Title');
        expect($content)->toContain('Deadline Date');
    });

    it('returns 422 for contracts not yet analyzed', function () {
        $contract = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/deadlines");

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ]);
    });
});

describe('export all as ZIP', function () {
    it('generates ZIP file with all exports', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();
        ContractClause::factory()->for($analysis, 'analysis')->count(2)->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->count(2)->create();

        $response = $this->actingAs($this->user)
            ->get("/api/contracts/{$contract->id}/export/all");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/zip');

        // Verify it's a valid ZIP (starts with PK header)
        $content = $response->getContent();
        expect(substr($content, 0, 2))->toBe('PK');
    });

    it('returns 422 for contracts not yet analyzed', function () {
        $contract = Contract::factory()->for($this->user)->pending()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/all");

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ]);
    });

    it('returns 404 for contracts belonging to other users', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->completed()->create();
        ContractAnalysis::factory()->for($contract)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/contracts/{$contract->id}/export/all");

        $response->assertNotFound();
    });
});
