<?php

declare(strict_types=1);

use App\Enums\DeadlineType;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\User;
use App\Repositories\Contracts\ContractDeadlineRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ContractDeadlineRepositoryInterface::class);
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
    $this->analysis = ContractAnalysis::factory()->for($this->contract)->create();
});

describe('find', function () {
    it('finds a deadline by id', function () {
        $deadline = ContractDeadline::factory()->for($this->analysis, 'analysis')->create();

        $found = $this->repository->find($deadline->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($deadline->id);
    });
});

describe('getForAnalysis', function () {
    it('returns all deadlines for an analysis', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->count(4)->create();

        $deadlines = $this->repository->getForAnalysis($this->analysis);

        expect($deadlines)->toHaveCount(4);
    });
});

describe('getByTypeForAnalysis', function () {
    it('returns deadlines filtered by type', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->payment()->count(2)->create();
        ContractDeadline::factory()->for($this->analysis, 'analysis')->renewal()->create();

        $payment = $this->repository->getByTypeForAnalysis(DeadlineType::PAYMENT, $this->analysis);
        $renewal = $this->repository->getByTypeForAnalysis(DeadlineType::RENEWAL, $this->analysis);

        expect($payment)->toHaveCount(2)
            ->and($renewal)->toHaveCount(1);
    });
});

describe('getUpcoming', function () {
    it('returns deadlines within specified days', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->create([
            'deadline_date' => now()->addDays(5),
        ]);
        ContractDeadline::factory()->for($this->analysis, 'analysis')->create([
            'deadline_date' => now()->addDays(15),
        ]);
        ContractDeadline::factory()->for($this->analysis, 'analysis')->create([
            'deadline_date' => now()->addDays(45),
        ]);

        $upcoming7 = $this->repository->getUpcoming(7);
        $upcoming30 = $this->repository->getUpcoming(30);

        expect($upcoming7)->toHaveCount(1)
            ->and($upcoming30)->toHaveCount(2);
    });

    it('does not return past deadlines', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->past()->create();
        ContractDeadline::factory()->for($this->analysis, 'analysis')->upcoming(5)->create();

        $upcoming = $this->repository->getUpcoming(30);

        expect($upcoming)->toHaveCount(1);
    });
});

describe('getUpcomingForUser', function () {
    it('returns upcoming deadlines for a specific user', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();
        $otherAnalysis = ContractAnalysis::factory()->for($otherContract)->create();

        ContractDeadline::factory()->for($this->analysis, 'analysis')->upcoming(5)->create();
        ContractDeadline::factory()->for($otherAnalysis, 'analysis')->upcoming(5)->create();

        $upcoming = $this->repository->getUpcomingForUser($this->user, 30);

        expect($upcoming)->toHaveCount(1);
    });
});

describe('getPast', function () {
    it('returns past deadlines', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->past(5)->create();
        ContractDeadline::factory()->for($this->analysis, 'analysis')->past(10)->create();
        ContractDeadline::factory()->for($this->analysis, 'analysis')->upcoming(5)->create();

        $past = $this->repository->getPast();

        expect($past)->toHaveCount(2);
    });
});

describe('getPastForUser', function () {
    it('returns past deadlines for a specific user', function () {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();
        $otherAnalysis = ContractAnalysis::factory()->for($otherContract)->create();

        ContractDeadline::factory()->for($this->analysis, 'analysis')->past(5)->create();
        ContractDeadline::factory()->for($otherAnalysis, 'analysis')->past(5)->create();

        $past = $this->repository->getPastForUser($this->user);

        expect($past)->toHaveCount(1);
    });
});

describe('getOverdue', function () {
    it('returns overdue deadlines within specified days back', function () {
        ContractDeadline::factory()->for($this->analysis, 'analysis')->create([
            'deadline_date' => now()->subDays(5),
        ]);
        ContractDeadline::factory()->for($this->analysis, 'analysis')->create([
            'deadline_date' => now()->subDays(45),
        ]);

        $overdue30 = $this->repository->getOverdue(30);
        $overdue60 = $this->repository->getOverdue(60);

        expect($overdue30)->toHaveCount(1)
            ->and($overdue60)->toHaveCount(2);
    });
});

describe('createMany', function () {
    it('creates multiple deadlines', function () {
        $deadlines = [
            [
                'contract_analysis_id' => $this->analysis->id,
                'deadline_type' => DeadlineType::PAYMENT,
                'title' => 'Payment Due',
                'deadline_date' => now()->addDays(30),
                'description' => 'First payment due',
                'source_text' => 'Payment of $1000 due within 30 days',
            ],
            [
                'contract_analysis_id' => $this->analysis->id,
                'deadline_type' => DeadlineType::RENEWAL,
                'title' => 'Contract Renewal',
                'deadline_date' => now()->addMonths(6),
                'description' => 'Contract renewal',
                'source_text' => 'Contract automatically renews on anniversary date',
            ],
        ];

        $result = $this->repository->createMany($deadlines);

        expect($result)->toHaveCount(2)
            ->and(ContractDeadline::count())->toBe(2);
    });
});

describe('delete', function () {
    it('deletes a deadline', function () {
        $deadline = ContractDeadline::factory()->for($this->analysis, 'analysis')->create();

        $result = $this->repository->delete($deadline);

        expect($result)->toBeTrue()
            ->and(ContractDeadline::find($deadline->id))->toBeNull();
    });
});
