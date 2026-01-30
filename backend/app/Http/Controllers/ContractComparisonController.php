<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompareContractsRequest;
use App\Http\Resources\ContractComparisonResource;
use App\Services\ContractComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ContractComparisonController extends Controller
{
    public function __construct(
        private ContractComparisonService $comparisonService,
    ) {}

    /**
     * Compare two contracts.
     */
    public function compare(CompareContractsRequest $request): JsonResponse|ContractComparisonResource
    {
        try {
            $result = $this->comparisonService->compare(
                contractIdA: $request->validated('contract_id_a'),
                contractIdB: $request->validated('contract_id_b'),
                user: Auth::user(),
            );

            return ContractComparisonResource::make($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
