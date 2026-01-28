<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendReminderJob;
use App\Models\Reminder;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Console\Command;

class ProcessDueRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     */
    protected $description = 'Process and send all due reminders';

    /**
     * Execute the console command.
     */
    public function handle(ReminderRepositoryInterface $reminders): int
    {
        $this->info('Processing due reminders...');

        $dueReminders = $reminders->getDue();

        if ($dueReminders->isEmpty()) {
            $this->info('No due reminders found.');

            return self::SUCCESS;
        }

        $this->info("Found {$dueReminders->count()} due reminder(s).");

        $dispatched = 0;

        /** @var Reminder $reminder */
        foreach ($dueReminders as $reminder) {
            $this->line("  Dispatching reminder: {$reminder->id} - {$reminder->title}");
            SendReminderJob::dispatch($reminder);
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} reminder job(s).");

        return self::SUCCESS;
    }
}
