<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AiAnalysisException;
use App\Http\Requests\AnalyzeTextRequest;
use App\Services\QuickAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class QuickAnalysisController extends Controller
{
    public function __construct(
        private QuickAnalysisService $analysisService,
    ) {}

    /**
     * Analyze pasted contract text without storing in database.
     */
    public function analyze(AnalyzeTextRequest $request): JsonResponse
    {
        try {
            $result = $this->analysisService->analyze(
                $request->validated('text'),
                $request->validated('title'),
            );

            return response()->json([
                'data' => $result->toArray(),
            ]);
        } catch (AiAnalysisException $e) {
            Log::error('Quick analysis failed', [
                'error' => $e->getMessage(),
                'text_length' => strlen($request->validated('text')),
            ]);

            return response()->json([
                'message' => 'Analysis failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
