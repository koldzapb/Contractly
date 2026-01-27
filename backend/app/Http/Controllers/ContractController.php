<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
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
            $request->integer('per_page', 15)
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
            $request->validated('title')
        );

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
}
