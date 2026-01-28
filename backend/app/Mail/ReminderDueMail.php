<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reminder $reminder,
    ) {}

    public function envelope(): Envelope
    {
        /** @var \App\Models\ContractDeadline|null $deadline */
        $deadline = $this->reminder->deadline;
        $deadlineTitle = $deadline !== null ? $deadline->title : $this->reminder->title;
        $daysUntil = $deadline?->days_until;

        $subject = match (true) {
            $daysUntil === null => "Reminder: {$deadlineTitle}",
            $daysUntil === 0 => "Reminder: {$deadlineTitle} is today!",
            $daysUntil === 1 => "Reminder: {$deadlineTitle} is tomorrow!",
            $daysUntil < 0 => "Reminder: {$deadlineTitle} was ".abs($daysUntil).' days ago',
            default => "Reminder: {$deadlineTitle} in {$daysUntil} days",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        /** @var \App\Models\ContractDeadline|null $deadline */
        $deadline = $this->reminder->deadline;

        return new Content(
            view: 'emails.reminder-due',
            with: [
                'reminder' => $this->reminder,
                'deadline' => $deadline,
                'contract' => $this->reminder->contract,
                'user' => $this->reminder->user,
                'daysUntil' => $deadline?->days_until,
                'appUrl' => config('app.frontend_url', config('app.url')),
            ],
        );
    }
}
