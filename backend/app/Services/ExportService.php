<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ExportService
{
    /**
     * Generate CSV content for clauses.
     */
    public function generateClausesCsv(Contract $contract): string
    {
        Log::info('Generating clauses CSV', [
            'contract_id' => $contract->id,
        ]);

        $contract->load('analysis.clauses');

        $output = fopen('php://temp', 'r+');

        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, [
            'Clause Type',
            'Risk Level',
            'Original Text',
            'Plain Explanation',
            'Risk Reason',
            'Page Number',
        ]);

        // Write data rows
        if ($contract->analysis) {
            foreach ($contract->analysis->clauses as $clause) {
                fputcsv($output, [
                    $clause->clause_type->label(),
                    $clause->risk_level->label(),
                    $this->sanitizeForCsv($clause->original_text),
                    $this->sanitizeForCsv($clause->plain_explanation),
                    $this->sanitizeForCsv($clause->risk_reason ?? ''),
                    $clause->page_number ?? '',
                ]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate CSV content for deadlines.
     */
    public function generateDeadlinesCsv(Contract $contract): string
    {
        Log::info('Generating deadlines CSV', [
            'contract_id' => $contract->id,
        ]);

        $contract->load('analysis.deadlines');

        $output = fopen('php://temp', 'r+');

        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, [
            'Type',
            'Title',
            'Description',
            'Deadline Date',
            'Days Until',
            'Status',
            'Is Recurring',
            'Recurrence Pattern',
        ]);

        // Write data rows
        if ($contract->analysis) {
            foreach ($contract->analysis->deadlines as $deadline) {
                $status = match (true) {
                    $deadline->isPast() => 'Past Due',
                    $deadline->days_until !== null && $deadline->days_until <= 7 => 'Urgent',
                    $deadline->days_until !== null && $deadline->days_until <= 30 => 'Upcoming',
                    default => 'Future',
                };

                fputcsv($output, [
                    $deadline->deadline_type->label(),
                    $this->sanitizeForCsv($deadline->title),
                    $this->sanitizeForCsv($deadline->description ?? ''),
                    $deadline->deadline_date?->toDateString() ?? 'Not specified',
                    $deadline->days_until ?? 'N/A',
                    $status,
                    $deadline->is_recurring ? 'Yes' : 'No',
                    $deadline->recurrence_pattern ?? '',
                ]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate a summary CSV with contract overview.
     */
    public function generateSummaryCsv(Contract $contract): string
    {
        Log::info('Generating summary CSV', [
            'contract_id' => $contract->id,
        ]);

        $contract->load(['analysis.clauses', 'analysis.deadlines']);

        $output = fopen('php://temp', 'r+');

        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write contract info
        fputcsv($output, ['Contract Analysis Summary']);
        fputcsv($output, ['']);
        fputcsv($output, ['Property', 'Value']);
        fputcsv($output, ['Title', $contract->title]);
        fputcsv($output, ['Original Filename', $contract->original_filename]);
        fputcsv($output, ['File Type', $contract->file_type->label()]);
        fputcsv($output, ['Upload Date', $contract->created_at->toDateString()]);
        fputcsv($output, ['Analysis Date', $contract->analyzed_at?->toDateString() ?? 'Not analyzed']);
        fputcsv($output, ['Overall Risk Level', $contract->overall_risk_level?->label() ?? 'Not assessed']);
        fputcsv($output, ['']);

        if ($contract->analysis) {
            fputcsv($output, ['Analysis Summary']);
            fputcsv($output, [$this->sanitizeForCsv($contract->analysis->summary)]);
            fputcsv($output, ['']);

            // Key findings
            fputcsv($output, ['Key Findings']);
            foreach ($contract->analysis->key_findings as $finding) {
                fputcsv($output, ['- ' . $this->sanitizeForCsv($finding)]);
            }
            fputcsv($output, ['']);

            // Statistics
            $clauses = $contract->analysis->clauses;
            $highRisk = $clauses->filter(fn($c) => $c->risk_level->value === 'high')->count();
            $mediumRisk = $clauses->filter(fn($c) => $c->risk_level->value === 'medium')->count();

            fputcsv($output, ['Statistics']);
            fputcsv($output, ['Total Clauses', $clauses->count()]);
            fputcsv($output, ['High Risk Clauses', $highRisk]);
            fputcsv($output, ['Medium Risk Clauses', $mediumRisk]);
            fputcsv($output, ['Total Deadlines', $contract->analysis->deadlines->count()]);
        }

        fputcsv($output, ['']);
        fputcsv($output, ['Generated by Contractly', now()->toDateTimeString()]);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate a ZIP file containing all exports.
     *
     * @return string Binary ZIP content
     */
    public function generateAllExportsZip(Contract $contract): string
    {
        Log::info('Generating all exports ZIP', [
            'contract_id' => $contract->id,
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'contract_export_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add summary CSV
        $zip->addFromString('summary.csv', $this->generateSummaryCsv($contract));

        // Add clauses CSV
        $zip->addFromString('clauses.csv', $this->generateClausesCsv($contract));

        // Add deadlines CSV
        $zip->addFromString('deadlines.csv', $this->generateDeadlinesCsv($contract));

        $zip->close();

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    /**
     * Get filename for clauses CSV.
     */
    public function getClausesCsvFilename(Contract $contract): string
    {
        return $this->getBaseFilename($contract) . '_clauses.csv';
    }

    /**
     * Get filename for deadlines CSV.
     */
    public function getDeadlinesCsvFilename(Contract $contract): string
    {
        return $this->getBaseFilename($contract) . '_deadlines.csv';
    }

    /**
     * Get filename for ZIP export.
     */
    public function getZipFilename(Contract $contract): string
    {
        return $this->getBaseFilename($contract) . '_export.zip';
    }

    /**
     * Get base filename for exports.
     */
    private function getBaseFilename(Contract $contract): string
    {
        $title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $contract->title);
        $date = now()->format('Y-m-d');

        return "Contractly_{$title}_{$date}";
    }

    /**
     * Sanitize text for CSV output.
     */
    private function sanitizeForCsv(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // Remove or replace problematic characters
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
