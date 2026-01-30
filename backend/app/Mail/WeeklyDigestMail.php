<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $digestData
     */
    public function __construct(
        public User $user,
        public array $digestData,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Contractly Weekly Summary',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'user' => $this->user,
                'contractsAnalyzed' => $this->digestData['contracts_analyzed'] ?? 0,
                'recentContracts' => $this->digestData['recent_contracts'] ?? collect(),
                'upcomingDeadlines' => $this->digestData['upcoming_deadlines'] ?? 0,
                'upcomingReminders' => $this->digestData['upcoming_reminders'] ?? collect(),
                'highRiskCount' => $this->digestData['high_risk_count'] ?? 0,
                'periodStart' => $this->digestData['period_start'] ?? now()->subWeek(),
                'periodEnd' => $this->digestData['period_end'] ?? now(),
                'appUrl' => config('app.frontend_url', config('app.url')),
            ],
        );
    }
}
