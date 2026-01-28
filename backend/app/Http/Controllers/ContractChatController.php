<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AiAnalysisException;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\ContractChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractChatController extends Controller
{
    public function __construct(
        private ContractRepositoryInterface $contracts,
        private ContractChatService $chatService,
    ) {}

    /**
     * Get chat history for a contract.
     */
    public function index(Request $request, string $contractId): AnonymousResourceCollection|JsonResponse
    {
        $contract = $this->contracts->findForUser($contractId, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        $messages = $this->chatService->getHistory($contract);

        return ChatMessageResource::collection($messages);
    }

    /**
     * Send a message to the chat.
     */
    public function store(SendMessageRequest $request, string $contractId): ChatMessageResource|JsonResponse
    {
        $contract = $this->contracts->findForUser($contractId, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        // Check if contract has been analyzed
        if (! $contract->isCompleted()) {
            return response()->json([
                'message' => 'Contract analysis must be completed before chatting.',
                'code' => 'ANALYSIS_NOT_COMPLETE',
            ], 422);
        }

        try {
            $message = $this->chatService->sendMessage(
                $contract,
                $request->user(),
                $request->validated('message'),
            );

            return ChatMessageResource::make($message);
        } catch (AiAnalysisException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => 'AI_ERROR',
            ], 500);
        }
    }

    /**
     * Clear chat history for a contract.
     */
    public function destroy(Request $request, string $contractId): JsonResponse
    {
        $contract = $this->contracts->findForUser($contractId, $request->user());

        if (! $contract) {
            return response()->json([
                'message' => 'Contract not found.',
            ], 404);
        }

        $count = $this->chatService->clearHistory($contract);

        return response()->json([
            'message' => 'Chat history cleared.',
            'messages_deleted' => $count,
        ]);
    }
}
