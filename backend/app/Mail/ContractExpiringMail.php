<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public int $daysUntilExpiry,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match (true) {
            $this->daysUntilExpiry === 0 => "Contract expiring today: {$this->contract->title}",
            $this->daysUntilExpiry === 1 => "Contract expiring tomorrow: {$this->contract->title}",
            $this->daysUntilExpiry <= 7 => "Contract expiring in {$this->daysUntilExpiry} days: {$this->contract->title}",
            default => "Contract expiring soon: {$this->contract->title}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $this->contract->loadMissing('user');

        return new Content(
            view: 'emails.contract-expiring',
            with: [
                'contract' => $this->contract,
                'user' => $this->contract->user,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'appUrl' => config('app.frontend_url', config('app.url')),
            ],
        );
    }
}
