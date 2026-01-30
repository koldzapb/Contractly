<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\ExportService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
        private ReportService $reportService,
        private ExportService $exportService,
    ) {}

    /**
     * Generate and download a PDF report for a contract.
     */
    public function pdf(Request $request, string $id): Response|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (!$contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        if (!$contract->isCompleted()) {
            return response()->json([
                'message' => 'Contract analysis must be completed before generating a report.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ], 422);
        }

        $pdfContent = $this->reportService->generatePdfReport($contract);
        $filename = $this->reportService->getPdfFilename($contract);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    /**
     * Export clauses as CSV.
     */
    public function clauses(Request $request, string $id): Response|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (!$contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        if (!$contract->isCompleted()) {
            return response()->json([
                'message' => 'Contract analysis must be completed before exporting.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ], 422);
        }

        $csvContent = $this->exportService->generateClausesCsv($contract);
        $filename = $this->exportService->getClausesCsvFilename($contract);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($csvContent),
        ]);
    }

    /**
     * Export deadlines as CSV.
     */
    public function deadlines(Request $request, string $id): Response|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (!$contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        if (!$contract->isCompleted()) {
            return response()->json([
                'message' => 'Contract analysis must be completed before exporting.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ], 422);
        }

        $csvContent = $this->exportService->generateDeadlinesCsv($contract);
        $filename = $this->exportService->getDeadlinesCsvFilename($contract);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($csvContent),
        ]);
    }

    /**
     * Export all data as a ZIP file.
     */
    public function all(Request $request, string $id): Response|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (!$contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        if (!$contract->isCompleted()) {
            return response()->json([
                'message' => 'Contract analysis must be completed before exporting.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ], 422);
        }

        $zipContent = $this->exportService->generateAllExportsZip($contract);
        $filename = $this->exportService->getZipFilename($contract);

        return response($zipContent, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($zipContent),
        ]);
    }
}
