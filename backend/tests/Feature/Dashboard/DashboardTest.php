<?php

declare(strict_types=1);

use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractClause;
use App\Models\ContractDeadline;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('dashboard endpoint', function () {
    it('returns dashboard data for authenticated user', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'total_contracts',
                        'pending_analysis',
                        'processing_analysis',
                        'completed_analysis',
                        'failed_analysis',
                        'high_risk_clauses',
                        'overdue_deadlines',
                    ],
                    'upcoming_deadlines',
                    'recent_contracts',
                ],
            ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    });
});

describe('dashboard stats', function () {
    it('counts contracts by status correctly', function () {
        Contract::factory()->for($this->user)->pending()->count(2)->create();
        Contract::factory()->for($this->user)->processing()->count(1)->create();
        Contract::factory()->for($this->user)->completed()->count(3)->create();
        Contract::factory()->for($this->user)->failed()->count(1)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.stats.total_contracts', 7)
            ->assertJsonPath('data.stats.pending_analysis', 2)
            ->assertJsonPath('data.stats.processing_analysis', 1)
            ->assertJsonPath('data.stats.completed_analysis', 3)
            ->assertJsonPath('data.stats.failed_analysis', 1);
    });

    it('returns zero stats when user has no contracts', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.stats.total_contracts', 0)
            ->assertJsonPath('data.stats.high_risk_clauses', 0)
            ->assertJsonPath('data.stats.overdue_deadlines', 0);
    });

    it('does not include other users contracts in stats', function () {
        $otherUser = User::factory()->create();
        Contract::factory()->for($this->user)->completed()->count(2)->create();
        Contract::factory()->for($otherUser)->completed()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.stats.total_contracts', 2);
    });

    it('counts high risk clauses correctly', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        ContractClause::factory()->for($analysis, 'analysis')->highRisk()->count(3)->create();
        ContractClause::factory()->for($analysis, 'analysis')->lowRisk()->count(2)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.stats.high_risk_clauses', 3);
    });

    it('counts overdue deadlines correctly', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        // Create past deadlines (overdue)
        ContractDeadline::factory()->for($analysis, 'analysis')->past()->count(2)->create();

        // Create future deadlines (not overdue)
        ContractDeadline::factory()->for($analysis, 'analysis')->upcoming()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.stats.overdue_deadlines', 2);
    });
});

describe('recent contracts', function () {
    it('returns recent contracts', function () {
        Contract::factory()->for($this->user)->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(3, 'data.recent_contracts');
    });

    it('limits recent contracts to 5', function () {
        Contract::factory()->for($this->user)->count(10)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(5, 'data.recent_contracts');
    });

    it('returns contracts ordered by most recent first', function () {
        $oldest = Contract::factory()->for($this->user)->create([
            'created_at' => now()->subDays(2),
        ]);
        $newest = Contract::factory()->for($this->user)->create([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk();

        $ids = collect($response->json('data.recent_contracts'))->pluck('id')->toArray();
        expect($ids[0])->toBe($newest->id);
    });

    it('returns contracts with expected structure', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'overall_risk_level' => RiskLevel::MEDIUM,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'recent_contracts' => [
                        '*' => [
                            'id',
                            'title',
                            'status',
                            'overall_risk_level',
                            'created_at',
                        ],
                    ],
                ],
            ]);
    });
});

describe('upcoming deadlines', function () {
    it('returns upcoming deadlines', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        ContractDeadline::factory()->for($analysis, 'analysis')->upcoming(14)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(1, 'data.upcoming_deadlines');
    });

    it('does not include past deadlines', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        ContractDeadline::factory()->for($analysis, 'analysis')->past()->create();
        ContractDeadline::factory()->for($analysis, 'analysis')->upcoming(7)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(1, 'data.upcoming_deadlines');
    });

    it('returns deadlines with contract information', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        ContractDeadline::factory()->for($analysis, 'analysis')->upcoming(7)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'upcoming_deadlines' => [
                        '*' => [
                            'id',
                            'deadline_type',
                            'deadline_type_label',
                            'title',
                            'deadline_date',
                            'days_until',
                            'urgency',
                            'is_past',
                            'contract' => [
                                'id',
                                'title',
                            ],
                        ],
                    ],
                ],
            ]);
    });

    it('limits upcoming deadlines to 5', function () {
        $contract = Contract::factory()->for($this->user)->completed()->create();
        $analysis = ContractAnalysis::factory()->for($contract)->create();

        ContractDeadline::factory()->for($analysis, 'analysis')->upcoming(20)->count(10)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(5, 'data.upcoming_deadlines');
    });

    it('does not include deadlines from other users', function () {
        $otherUser = User::factory()->create();

        $myContract = Contract::factory()->for($this->user)->completed()->create();
        $myAnalysis = ContractAnalysis::factory()->for($myContract)->create();
        ContractDeadline::factory()->for($myAnalysis, 'analysis')->upcoming(7)->create();

        $otherContract = Contract::factory()->for($otherUser)->completed()->create();
        $otherAnalysis = ContractAnalysis::factory()->for($otherContract)->create();
        ContractDeadline::factory()->for($otherAnalysis, 'analysis')->upcoming(7)->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonCount(1, 'data.upcoming_deadlines');
    });
});
