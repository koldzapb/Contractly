<?php

declare(strict_types=1);

use App\Enums\ReminderStatus;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ReminderRepositoryInterface::class);
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
});

describe('find', function () {
    it('finds a reminder by id', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $found = $this->repository->find($reminder->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($reminder->id);
    });
});

describe('findForUser', function () {
    it('finds a reminder for the correct user', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $found = $this->repository->findForUser($reminder->id, $this->user);

        expect($found)->not->toBeNull();
    });

    it('returns null for wrong user', function () {
        $otherUser = User::factory()->create();
        $reminder = Reminder::factory()
            ->for($otherUser)
            ->for(Contract::factory()->for($otherUser))
            ->create();

        $found = $this->repository->findForUser($reminder->id, $this->user);

        expect($found)->toBeNull();
    });
});

describe('getAllForUser', function () {
    it('returns all reminders for a user', function () {
        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->count(5)
            ->create();

        $reminders = $this->repository->getAllForUser($this->user);

        expect($reminders)->toHaveCount(5);
    });
});

describe('getByStatusForUser', function () {
    it('returns reminders filtered by status', function () {
        Reminder::factory()->for($this->user)->for($this->contract)->pending()->count(3)->create();
        Reminder::factory()->for($this->user)->for($this->contract)->sent()->count(2)->create();

        $pending = $this->repository->getByStatusForUser(ReminderStatus::PENDING, $this->user);
        $sent = $this->repository->getByStatusForUser(ReminderStatus::SENT, $this->user);

        expect($pending)->toHaveCount(3)
            ->and($sent)->toHaveCount(2);
    });
});

describe('getPendingForUser', function () {
    it('returns only pending reminders', function () {
        Reminder::factory()->for($this->user)->for($this->contract)->pending()->count(2)->create();
        Reminder::factory()->for($this->user)->for($this->contract)->sent()->create();
        Reminder::factory()->for($this->user)->for($this->contract)->cancelled()->create();

        $pending = $this->repository->getPendingForUser($this->user);

        expect($pending)->toHaveCount(2);
    });
});

describe('getUpcomingForUser', function () {
    it('returns pending reminders within specified days', function () {
        Reminder::factory()->for($this->user)->for($this->contract)->create([
            'status' => ReminderStatus::PENDING,
            'remind_at' => now()->addDays(3),
        ]);
        Reminder::factory()->for($this->user)->for($this->contract)->create([
            'status' => ReminderStatus::PENDING,
            'remind_at' => now()->addDays(10),
        ]);

        $upcoming7 = $this->repository->getUpcomingForUser($this->user, 7);
        $upcoming14 = $this->repository->getUpcomingForUser($this->user, 14);

        expect($upcoming7)->toHaveCount(1)
            ->and($upcoming14)->toHaveCount(2);
    });
});

describe('getForContract', function () {
    it('returns reminders for a contract', function () {
        $otherContract = Contract::factory()->for($this->user)->create();

        Reminder::factory()->for($this->user)->for($this->contract)->count(3)->create();
        Reminder::factory()->for($this->user)->for($otherContract)->count(2)->create();

        $reminders = $this->repository->getForContract($this->contract);

        expect($reminders)->toHaveCount(3);
    });
});

describe('getForDeadline', function () {
    it('returns reminders for a deadline', function () {
        $analysis = ContractAnalysis::factory()->for($this->contract)->create();
        $deadline = ContractDeadline::factory()->for($analysis, 'analysis')->create();

        Reminder::factory()->for($this->user)->for($this->contract)->create([
            'contract_deadline_id' => $deadline->id,
        ]);
        Reminder::factory()->for($this->user)->for($this->contract)->create([
            'contract_deadline_id' => $deadline->id,
        ]);
        Reminder::factory()->for($this->user)->for($this->contract)->create();

        $reminders = $this->repository->getForDeadline($deadline);

        expect($reminders)->toHaveCount(2);
    });
});

describe('getDue', function () {
    it('returns pending reminders where remind_at is in the past', function () {
        Reminder::factory()->for($this->user)->for($this->contract)->due()->count(2)->create();
        Reminder::factory()->for($this->user)->for($this->contract)->upcoming(5)->create();
        Reminder::factory()->for($this->user)->for($this->contract)->create([
            'status' => ReminderStatus::SENT,
            'remind_at' => now()->subHour(),
        ]);

        $due = $this->repository->getDue();

        expect($due)->toHaveCount(2);
    });
});

describe('markAsSent', function () {
    it('marks a reminder as sent', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $result = $this->repository->markAsSent($reminder);

        expect($result)->toBeTrue();

        $reminder->refresh();
        expect($reminder->status)->toBe(ReminderStatus::SENT)
            ->and($reminder->sent_at)->not->toBeNull();
    });
});

describe('markAsFailed', function () {
    it('marks a reminder as failed', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $result = $this->repository->markAsFailed($reminder);

        expect($result)->toBeTrue();

        $reminder->refresh();
        expect($reminder->status)->toBe(ReminderStatus::FAILED);
    });
});

describe('cancel', function () {
    it('cancels a reminder', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $result = $this->repository->cancel($reminder);

        expect($result)->toBeTrue();

        $reminder->refresh();
        expect($reminder->status)->toBe(ReminderStatus::CANCELLED);
    });
});

describe('paginateForUser', function () {
    it('paginates reminders for a user', function () {
        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->count(25)
            ->create();

        $result = $this->repository->paginateForUser($this->user, 10);

        expect($result->count())->toBe(10)
            ->and($result->total())->toBe(25);
    });
});

describe('create', function () {
    it('creates a new reminder', function () {
        $data = [
            'user_id' => $this->user->id,
            'contract_id' => $this->contract->id,
            'title' => 'Test Reminder',
            'remind_at' => now()->addDays(7),
            'days_before' => 7,
            'channel' => 'email',
            'status' => ReminderStatus::PENDING,
        ];

        $reminder = $this->repository->create($data);

        expect($reminder)->toBeInstanceOf(Reminder::class)
            ->and($reminder->title)->toBe('Test Reminder')
            ->and($reminder->exists)->toBeTrue();
    });
});

describe('delete', function () {
    it('deletes a reminder', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $result = $this->repository->delete($reminder);

        expect($result)->toBeTrue()
            ->and(Reminder::find($reminder->id))->toBeNull();
    });
});
