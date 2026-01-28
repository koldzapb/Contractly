<?php

declare(strict_types=1);

use App\Enums\ReminderStatus;
use App\Jobs\SendReminderJob;
use App\Mail\ReminderDueMail;
use App\Models\Contract;
use App\Models\ContractAnalysis;
use App\Models\ContractDeadline;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
    $this->analysis = ContractAnalysis::factory()->for($this->contract)->create();
    $this->deadline = ContractDeadline::factory()
        ->for($this->analysis, 'analysis')
        ->upcoming(30)
        ->create();
});

describe('SendReminderJob', function () {
    it('sends email and marks reminder as sent', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->forDeadline($this->deadline)
            ->pending()
            ->create();

        SendReminderJob::dispatch($reminder);

        Mail::assertSent(ReminderDueMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });

        $reminder->refresh();
        expect($reminder->status)->toBe(ReminderStatus::SENT);
        expect($reminder->sent_at)->not->toBeNull();
    });

    it('skips non-pending reminders', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->sent()
            ->create();

        SendReminderJob::dispatch($reminder);

        Mail::assertNothingSent();
    });

    it('marks reminder as failed on mail error', function () {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new Exception('Mail server error'));

        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->pending()
            ->create();

        $job = new SendReminderJob($reminder);

        try {
            $job->handle(app(\App\Repositories\Contracts\ReminderRepositoryInterface::class));
        } catch (Exception $e) {
            // Expected to throw
        }

        // Call the failed method manually since we're not using the queue
        $job->failed(new Exception('Mail server error'));

        $reminder->refresh();
        expect($reminder->status)->toBe(ReminderStatus::FAILED);
    });

    it('has correct job tags', function () {
        $reminder = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->create();

        $job = new SendReminderJob($reminder);
        $tags = $job->tags();

        expect($tags)->toContain("reminder:{$reminder->id}");
        expect($tags)->toContain("user:{$this->user->id}");
        expect($tags)->toContain("contract:{$this->contract->id}");
    });
});
