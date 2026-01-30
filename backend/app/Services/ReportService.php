<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractDeadline;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Generate a PDF report for a contract.
     *
     * @return string Binary PDF content
     */
    public function generatePdfReport(Contract $contract): string
    {
        Log::info('Generating PDF report', [
            'contract_id' => $contract->id,
        ]);

        // Load relationships needed for the report
        $contract->load(['analysis.clauses', 'analysis.deadlines']);

        // Prepare data for the view
        $data = $this->prepareReportData($contract);

        // Generate PDF using Blade template
        $pdf = Pdf::loadView('reports.contract', $data);

        // Configure PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'sans-serif');

        Log::info('PDF report generated', [
            'contract_id' => $contract->id,
        ]);

        return $pdf->output();
    }

    /**
     * Get the filename for a PDF report.
     */
    public function getPdfFilename(Contract $contract): string
    {
        $title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $contract->title);
        $date = now()->format('Y-m-d');

        return "Contractly_Report_{$title}_{$date}.pdf";
    }

    /**
     * Prepare data for the report template.
     *
     * @return array<string, mixed>
     */
    private function prepareReportData(Contract $contract): array
    {
        $analysis = $contract->analysis;

        // Group clauses by risk level
        $clausesByRisk = [
            'high' => [],
            'medium' => [],
            'low' => [],
            'none' => [],
        ];

        // Separate past and upcoming deadlines
        /** @var array<int, ContractDeadline> $pastDeadlines */
        $pastDeadlines = [];
        /** @var array<int, ContractDeadline> $upcomingDeadlines */
        $upcomingDeadlines = [];

        if ($analysis) {
            foreach ($analysis->clauses as $clause) {
                $riskLevel = $clause->risk_level->value;
                $clausesByRisk[$riskLevel][] = $clause;
            }

            foreach ($analysis->deadlines as $deadline) {
                if ($deadline->isPast()) {
                    $pastDeadlines[] = $deadline;
                } else {
                    $upcomingDeadlines[] = $deadline;
                }
            }

            // Sort upcoming by date
            usort($upcomingDeadlines, function (ContractDeadline $a, ContractDeadline $b): int {
                if (!$a->deadline_date) return 1;
                if (!$b->deadline_date) return -1;
                return $a->deadline_date->timestamp - $b->deadline_date->timestamp;
            });
        }

        return [
            'contract' => $contract,
            'analysis' => $analysis,
            'clausesByRisk' => $clausesByRisk,
            'pastDeadlines' => $pastDeadlines,
            'upcomingDeadlines' => $upcomingDeadlines,
            'generatedAt' => now(),
            'totalClauses' => $analysis ? $analysis->clauses->count() : 0,
            'totalDeadlines' => $analysis ? $analysis->deadlines->count() : 0,
            'highRiskCount' => count($clausesByRisk['high']),
            'mediumRiskCount' => count($clausesByRisk['medium']),
        ];
    }
}
