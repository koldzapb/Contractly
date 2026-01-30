<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnalysisCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your contract analysis is ready - {$this->contract->title}",
        );
    }

    public function content(): Content
    {
        $this->contract->loadMissing(['analysis.clauses', 'analysis.deadlines', 'user']);

        $analysis = $this->contract->analysis;

        if ($analysis !== null) {
            $riskLevel = $analysis->overall_risk_level->value;
            $clauseCount = $analysis->clauses->count();
            $deadlineCount = $analysis->deadlines->count();
            $keyFindings = $analysis->key_findings;
        } else {
            $riskLevel = 'unknown';
            $clauseCount = 0;
            $deadlineCount = 0;
            $keyFindings = [];
        }

        return new Content(
            view: 'emails.analysis-complete',
            with: [
                'contract' => $this->contract,
                'user' => $this->contract->user,
                'riskLevel' => $riskLevel,
                'clauseCount' => $clauseCount,
                'deadlineCount' => $deadlineCount,
                'keyFindings' => $keyFindings,
                'appUrl' => config('app.frontend_url', config('app.url')),
            ],
        );
    }
}
