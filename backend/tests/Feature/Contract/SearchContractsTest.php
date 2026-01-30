<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Enums\FileType;
use App\Enums\RiskLevel;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('search contracts', function () {
    it('searches contracts by title', function () {
        Contract::factory()->for($this->user)->create(['title' => 'Service Agreement 2024']);
        Contract::factory()->for($this->user)->create(['title' => 'Employment Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'NDA Document']);

        $response = $this->getJson('/api/contracts?q=Service');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Service Agreement 2024');
        expect($response->json('filters.query'))->toBe('Service');
        expect($response->json('filters.has_filters'))->toBeTrue();
    });

    it('searches contracts by filename', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Contract A',
            'original_filename' => 'lease-agreement.pdf',
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Contract B',
            'original_filename' => 'employment.pdf',
        ]);

        $response = $this->getJson('/api/contracts?q=lease');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Contract A');
    });

    it('searches contracts by analysis summary', function () {
        $contract1 = Contract::factory()->for($this->user)->completed()->create(['title' => 'Contract A']);
        ContractAnalysis::factory()->for($contract1)->create([
            'summary' => 'This is a comprehensive employment agreement with standard clauses.',
        ]);

        $contract2 = Contract::factory()->for($this->user)->completed()->create(['title' => 'Contract B']);
        ContractAnalysis::factory()->for($contract2)->create([
            'summary' => 'Lease agreement for commercial property.',
        ]);

        $response = $this->getJson('/api/contracts?q=employment');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Contract A');
    });

    it('returns all contracts when no search query provided', function () {
        Contract::factory()->for($this->user)->count(3)->create();

        $response = $this->getJson('/api/contracts');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(3);
        expect($response->json('filters.query'))->toBeNull();
        expect($response->json('filters.has_filters'))->toBeFalse();
    });

    it('returns empty results when no matches found', function () {
        Contract::factory()->for($this->user)->create(['title' => 'Service Agreement']);

        $response = $this->getJson('/api/contracts?q=nonexistent');

        $response->assertOk();
        expect($response->json('data'))->toBeEmpty();
    });
});

describe('filter by status', function () {
    it('filters contracts by single status', function () {
        Contract::factory()->for($this->user)->pending()->create(['title' => 'Pending Contract']);
        Contract::factory()->for($this->user)->completed()->create(['title' => 'Completed Contract']);
        Contract::factory()->for($this->user)->failed()->create(['title' => 'Failed Contract']);

        $response = $this->getJson('/api/contracts?status=completed');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Completed Contract');
        expect($response->json('filters.statuses'))->toBe(['completed']);
    });

    it('filters contracts by multiple statuses', function () {
        Contract::factory()->for($this->user)->pending()->create(['title' => 'Pending']);
        Contract::factory()->for($this->user)->completed()->create(['title' => 'Completed']);
        Contract::factory()->for($this->user)->failed()->create(['title' => 'Failed']);

        $response = $this->getJson('/api/contracts?status=pending,completed');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
        $titles = collect($response->json('data'))->pluck('title')->all();
        expect($titles)->toContain('Pending');
        expect($titles)->toContain('Completed');
    });

    it('rejects invalid status values', function () {
        Contract::factory()->for($this->user)->create();

        $response = $this->getJson('/api/contracts?status=invalid');

        $response->assertStatus(422);
    });
});

describe('filter by risk level', function () {
    it('filters contracts by single risk level', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'High Risk',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Low Risk',
            'overall_risk_level' => RiskLevel::LOW,
        ]);

        $response = $this->getJson('/api/contracts?risk_level=high');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('High Risk');
    });

    it('filters contracts by multiple risk levels', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'High Risk',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Medium Risk',
            'overall_risk_level' => RiskLevel::MEDIUM,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Low Risk',
            'overall_risk_level' => RiskLevel::LOW,
        ]);

        $response = $this->getJson('/api/contracts?risk_level=high,medium');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });
});

describe('filter by file type', function () {
    it('filters contracts by file type', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'PDF Contract',
            'file_type' => FileType::PDF,
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Image Contract',
            'file_type' => FileType::IMAGE,
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Text Contract',
            'file_type' => FileType::TEXT,
        ]);

        $response = $this->getJson('/api/contracts?file_type=pdf');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('PDF Contract');
    });

    it('filters contracts by multiple file types', function () {
        Contract::factory()->for($this->user)->create(['file_type' => FileType::PDF]);
        Contract::factory()->for($this->user)->create(['file_type' => FileType::IMAGE]);
        Contract::factory()->for($this->user)->create(['file_type' => FileType::TEXT]);

        $response = $this->getJson('/api/contracts?file_type=pdf,image');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });
});

describe('filter by date range', function () {
    it('filters contracts by date_from', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Old Contract',
            'created_at' => now()->subDays(30),
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Recent Contract',
            'created_at' => now()->subDays(5),
        ]);

        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $response = $this->getJson("/api/contracts?date_from={$dateFrom}");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Recent Contract');
    });

    it('filters contracts by date_to', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Old Contract',
            'created_at' => now()->subDays(30),
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Recent Contract',
            'created_at' => now()->subDays(5),
        ]);

        $dateTo = now()->subDays(20)->format('Y-m-d');
        $response = $this->getJson("/api/contracts?date_to={$dateTo}");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Old Contract');
    });

    it('filters contracts by date range', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Very Old',
            'created_at' => now()->subDays(60),
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Middle',
            'created_at' => now()->subDays(15),
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'Recent',
            'created_at' => now()->subDays(2),
        ]);

        $dateFrom = now()->subDays(30)->format('Y-m-d');
        $dateTo = now()->subDays(10)->format('Y-m-d');
        $response = $this->getJson("/api/contracts?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Middle');
    });

    it('validates date_to must be after date_from', function () {
        Contract::factory()->for($this->user)->create();

        $response = $this->getJson('/api/contracts?date_from=2026-01-30&date_to=2026-01-01');

        $response->assertStatus(422);
    });
});

describe('filter by has deadlines', function () {
    it('filters contracts with deadlines', function () {
        $contractWithDeadlines = Contract::factory()->for($this->user)->completed()->create(['title' => 'With Deadlines']);
        $analysis = ContractAnalysis::factory()->for($contractWithDeadlines)->create();
        ContractDeadline::factory()->create(['contract_analysis_id' => $analysis->id]);

        $contractWithoutDeadlines = Contract::factory()->for($this->user)->completed()->create(['title' => 'Without Deadlines']);
        ContractAnalysis::factory()->for($contractWithoutDeadlines)->create();

        $response = $this->getJson('/api/contracts?has_deadlines=true');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('With Deadlines');
    });

    it('filters contracts without deadlines', function () {
        $contractWithDeadlines = Contract::factory()->for($this->user)->completed()->create(['title' => 'With Deadlines']);
        $analysis = ContractAnalysis::factory()->for($contractWithDeadlines)->create();
        ContractDeadline::factory()->create(['contract_analysis_id' => $analysis->id]);

        $contractWithoutDeadlines = Contract::factory()->for($this->user)->completed()->create(['title' => 'Without Deadlines']);
        ContractAnalysis::factory()->for($contractWithoutDeadlines)->create();

        $response = $this->getJson('/api/contracts?has_deadlines=false');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Without Deadlines');
    });
});

describe('sorting', function () {
    it('sorts by title ascending', function () {
        Contract::factory()->for($this->user)->create(['title' => 'Zebra Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'Alpha Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'Middle Contract']);

        $response = $this->getJson('/api/contracts?sort_by=title&sort_order=asc');

        $response->assertOk();
        expect($response->json('data.0.title'))->toBe('Alpha Contract');
        expect($response->json('data.1.title'))->toBe('Middle Contract');
        expect($response->json('data.2.title'))->toBe('Zebra Contract');
    });

    it('sorts by title descending', function () {
        Contract::factory()->for($this->user)->create(['title' => 'Zebra Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'Alpha Contract']);

        $response = $this->getJson('/api/contracts?sort_by=title&sort_order=desc');

        $response->assertOk();
        expect($response->json('data.0.title'))->toBe('Zebra Contract');
    });

    it('sorts by risk level', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Low',
            'overall_risk_level' => RiskLevel::LOW,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'High',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Medium',
            'overall_risk_level' => RiskLevel::MEDIUM,
        ]);

        // desc = highest risk first
        $response = $this->getJson('/api/contracts?sort_by=overall_risk_level&sort_order=desc');

        $response->assertOk();
        expect($response->json('data.0.title'))->toBe('High');
        expect($response->json('data.1.title'))->toBe('Medium');
        expect($response->json('data.2.title'))->toBe('Low');
    });

    it('defaults to created_at descending', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Old',
            'created_at' => now()->subDays(10),
        ]);
        Contract::factory()->for($this->user)->create([
            'title' => 'New',
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/contracts');

        $response->assertOk();
        expect($response->json('data.0.title'))->toBe('New');
        expect($response->json('filters.sort_by'))->toBe('created_at');
        expect($response->json('filters.sort_order'))->toBe('desc');
    });

    it('rejects invalid sort field', function () {
        Contract::factory()->for($this->user)->create();

        $response = $this->getJson('/api/contracts?sort_by=invalid_field');

        $response->assertStatus(422);
    });
});

describe('combined filters', function () {
    it('combines search with filters', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Service Agreement - High Risk',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Service Agreement - Low Risk',
            'overall_risk_level' => RiskLevel::LOW,
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Employment Contract - High Risk',
            'overall_risk_level' => RiskLevel::HIGH,
        ]);

        $response = $this->getJson('/api/contracts?q=Service&risk_level=high');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Service Agreement - High Risk');
    });

    it('combines multiple filters', function () {
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Match',
            'overall_risk_level' => RiskLevel::HIGH,
            'file_type' => FileType::PDF,
            'created_at' => now()->subDays(5),
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Wrong Risk',
            'overall_risk_level' => RiskLevel::LOW,
            'file_type' => FileType::PDF,
            'created_at' => now()->subDays(5),
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Wrong Type',
            'overall_risk_level' => RiskLevel::HIGH,
            'file_type' => FileType::IMAGE,
            'created_at' => now()->subDays(5),
        ]);
        Contract::factory()->for($this->user)->completed()->create([
            'title' => 'Wrong Date',
            'overall_risk_level' => RiskLevel::HIGH,
            'file_type' => FileType::PDF,
            'created_at' => now()->subDays(30),
        ]);

        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $response = $this->getJson("/api/contracts?risk_level=high&file_type=pdf&date_from={$dateFrom}");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.title'))->toBe('Match');
    });
});

describe('pagination', function () {
    it('respects per_page parameter', function () {
        Contract::factory()->for($this->user)->count(10)->create();

        $response = $this->getJson('/api/contracts?per_page=3');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(3);
        expect($response->json('meta.per_page'))->toBe(3);
        expect($response->json('meta.total'))->toBe(10);
    });

    it('respects page parameter', function () {
        Contract::factory()->for($this->user)->count(10)->create();

        $response = $this->getJson('/api/contracts?per_page=3&page=2');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(3);
        expect($response->json('meta.current_page'))->toBe(2);
    });

    it('limits per_page to 100', function () {
        Contract::factory()->for($this->user)->count(5)->create();

        $response = $this->getJson('/api/contracts?per_page=200');

        $response->assertStatus(422);
    });

    it('maintains filters across pages', function () {
        Contract::factory()->for($this->user)->completed()->count(5)->create([
            'overall_risk_level' => RiskLevel::HIGH,
        ]);
        Contract::factory()->for($this->user)->completed()->count(5)->create([
            'overall_risk_level' => RiskLevel::LOW,
        ]);

        $response = $this->getJson('/api/contracts?risk_level=high&per_page=2&page=2');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
        expect($response->json('meta.total'))->toBe(5);
        expect($response->json('meta.current_page'))->toBe(2);
        expect($response->json('filters.risk_levels'))->toBe(['high']);
    });
});

describe('filter metadata in response', function () {
    it('includes filter metadata in response', function () {
        Contract::factory()->for($this->user)->create();

        $response = $this->getJson('/api/contracts?q=test&status=completed&risk_level=high&file_type=pdf&sort_by=title&sort_order=asc');

        $response->assertOk();
        expect($response->json('filters.query'))->toBe('test');
        expect($response->json('filters.statuses'))->toBe(['completed']);
        expect($response->json('filters.risk_levels'))->toBe(['high']);
        expect($response->json('filters.file_types'))->toBe(['pdf']);
        expect($response->json('filters.sort_by'))->toBe('title');
        expect($response->json('filters.sort_order'))->toBe('asc');
        expect($response->json('filters.has_filters'))->toBeTrue();
    });

    it('shows has_filters as false when no filters applied', function () {
        Contract::factory()->for($this->user)->create();

        $response = $this->getJson('/api/contracts');

        $response->assertOk();
        expect($response->json('filters.has_filters'))->toBeFalse();
    });
});
