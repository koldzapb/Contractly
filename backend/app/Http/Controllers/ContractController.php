<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Jobs\AnalyzeContractJob;
use App\Models\Contract;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\ContractUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
        private ContractUploadService $uploadService,
    ) {}

    /**
     * List all contracts for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $contracts = $this->contracts->paginateForUser(
            $request->user(),
            $request->integer('per_page', 15),
        );

        return ContractResource::collection($contracts);
    }

    /**
     * Upload a new contract.
     */
    public function store(StoreContractRequest $request): JsonResponse
    {
        $contract = $this->uploadService->upload(
            $request->file('file'),
            $request->user(),
            $request->validated('title'),
        );

        // Dispatch the analysis job
        AnalyzeContractJob::dispatch($contract);

        return ContractResource::make($contract)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get a single contract.
     */
    public function show(Request $request, string $id): ContractResource|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        // Load relationships if requested
        if ($request->boolean('with_analysis')) {
            $contract->load(['analysis.clauses', 'analysis.deadlines']);
        }

        return ContractResource::make($contract);
    }

    /**
     * Update a contract's title.
     */
    public function update(UpdateContractRequest $request, string $id): ContractResource|JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        $this->contracts->update($contract, [
            'title' => $request->validated('title'),
        ]);

        return ContractResource::make($contract->fresh());
    }

    /**
     * Delete a contract.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        $this->uploadService->delete($contract);

        return response()->json([
            'message' => 'Contract deleted successfully.',
        ]);
    }

    /**
     * Get contract status (for polling during analysis).
     */
    public function status(Request $request, string $id): JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $contract->id,
                'status' => $contract->status->value,
                'error_message' => $contract->isFailed() ? $contract->error_message : null,
            ],
        ]);
    }

    /**
     * Retry analysis for a failed contract.
     */
    public function retryAnalysis(Request $request, string $id): JsonResponse
    {
        $contract = $this->contracts->findForUser($id, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        // Only allow retry for failed contracts
        if ($contract->status !== ContractStatus::FAILED) {
            return response()->json([
                'message' => 'Only failed contracts can be retried.',
            ], 422);
        }

        // Delete existing analysis if any
        $contract->load('analysis.clauses', 'analysis.deadlines');
        $analysis = $contract->analysis;
        if ($analysis instanceof \App\Models\ContractAnalysis) {
            $analysis->clauses()->delete();
            $analysis->deadlines()->delete();
            $analysis->delete();
        }

        // Reset status and dispatch new job
        $this->contracts->update($contract, [
            'status' => ContractStatus::PENDING,
            'error_message' => null,
        ]);

        AnalyzeContractJob::dispatch($contract->fresh());

        return response()->json([
            'message' => 'Analysis retry started.',
            'data' => [
                'id' => $contract->id,
                'status' => 'pending',
            ],
        ]);
    }
}
