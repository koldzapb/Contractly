<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ReminderDueMail;
use App\Models\Reminder;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReminderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The maximum number of seconds the job should run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Reminder $reminder,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ReminderRepositoryInterface $reminders): void
    {
        // Refresh the reminder to get latest state
        $this->reminder->refresh();

        // Skip if reminder is no longer pending
        if (! $this->reminder->isPending()) {
            Log::info('SendReminderJob skipped - reminder not pending', [
                'reminder_id' => $this->reminder->id,
                'status' => $this->reminder->status->value,
            ]);

            return;
        }

        // Load relationships
        $this->reminder->load(['user', 'contract', 'deadline']);

        Log::info('SendReminderJob started', [
            'reminder_id' => $this->reminder->id,
            'user_id' => $this->reminder->user_id,
            'attempt' => $this->attempts(),
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = $this->reminder->user;

            // Send the email
            Mail::to($user->email)
                ->send(new ReminderDueMail($this->reminder));

            // Mark as sent
            $reminders->markAsSent($this->reminder);

            Log::info('SendReminderJob completed', [
                'reminder_id' => $this->reminder->id,
                'email' => $user->email,
            ]);
        } catch (Throwable $e) {
            Log::error('SendReminderJob failed to send email', [
                'reminder_id' => $this->reminder->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('SendReminderJob failed permanently', [
            'reminder_id' => $this->reminder->id,
            'error' => $exception?->getMessage(),
        ]);

        // Mark reminder as failed
        $repository = app(ReminderRepositoryInterface::class);
        $repository->markAsFailed($this->reminder);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'reminder:'.$this->reminder->id,
            'user:'.$this->reminder->user_id,
            'contract:'.$this->reminder->contract_id,
        ];
    }
}
