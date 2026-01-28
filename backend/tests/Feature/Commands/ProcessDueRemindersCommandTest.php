<?php

declare(strict_types=1);

use App\Jobs\SendReminderJob;
use App\Models\Contract;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();

    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
});

describe('ProcessDueRemindersCommand', function () {
    it('dispatches jobs for due reminders', function () {
        // Create due reminders
        $dueReminder1 = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->due()
            ->create();

        $dueReminder2 = Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->due()
            ->create();

        $this->artisan('reminders:process')
            ->assertSuccessful()
            ->expectsOutput('Processing due reminders...')
            ->expectsOutput('Found 2 due reminder(s).')
            ->expectsOutput('Dispatched 2 reminder job(s).');

        Queue::assertPushed(SendReminderJob::class, 2);
    });

    it('does not dispatch jobs for future reminders', function () {
        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->upcoming(7)
            ->create();

        $this->artisan('reminders:process')
            ->assertSuccessful()
            ->expectsOutput('No due reminders found.');

        Queue::assertNotPushed(SendReminderJob::class);
    });

    it('does not dispatch jobs for non-pending reminders', function () {
        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->sent()
            ->create(['remind_at' => now()->subHour()]);

        Reminder::factory()
            ->for($this->user)
            ->for($this->contract)
            ->cancelled()
            ->create(['remind_at' => now()->subHour()]);

        $this->artisan('reminders:process')
            ->assertSuccessful()
            ->expectsOutput('No due reminders found.');

        Queue::assertNotPushed(SendReminderJob::class);
    });

    it('handles empty reminder list', function () {
        $this->artisan('reminders:process')
            ->assertSuccessful()
            ->expectsOutput('No due reminders found.');

        Queue::assertNotPushed(SendReminderJob::class);
    });
});
