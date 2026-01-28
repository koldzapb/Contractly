<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReminderRequest;
use App\Http\Requests\UpdateReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\ContractDeadline;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReminderController extends Controller
{
    public function __construct(
        private ReminderRepositoryInterface $reminders,
        private ReminderService $reminderService,
    ) {}

    /**
     * List all reminders for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $reminders = $this->reminders->paginateForUser(
            $request->user(),
            $request->integer('per_page', 15),
        );

        return ReminderResource::collection($reminders);
    }

    /**
     * Create a new reminder.
     */
    public function store(StoreReminderRequest $request): JsonResponse
    {
        $deadline = ContractDeadline::findOrFail($request->validated('contract_deadline_id'));

        $reminder = $this->reminderService->createForDeadline(
            $deadline,
            $request->user(),
            $request->validated('days_before'),
            $request->validated('title'),
        );

        $reminder->load(['contract', 'deadline']);

        return ReminderResource::make($reminder)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get a single reminder.
     */
    public function show(Request $request, string $id): ReminderResource|JsonResponse
    {
        $reminder = $this->reminders->findForUser($id, $request->user());

        if (! $reminder) {
            return response()->json([
                'message' => 'Reminder not found.',
            ], 404);
        }

        $reminder->load(['contract', 'deadline']);

        return ReminderResource::make($reminder);
    }

    /**
     * Update a reminder.
     */
    public function update(UpdateReminderRequest $request, string $id): ReminderResource|JsonResponse
    {
        $reminder = $this->reminders->findForUser($id, $request->user());

        if (! $reminder) {
            return response()->json([
                'message' => 'Reminder not found.',
            ], 404);
        }

        // Only allow updates to pending reminders
        if (! $reminder->isPending()) {
            return response()->json([
                'message' => 'Only pending reminders can be updated.',
            ], 422);
        }

        $reminder = $this->reminderService->update($reminder, $request->validated());
        $reminder->load(['contract', 'deadline']);

        return ReminderResource::make($reminder);
    }

    /**
     * Delete a reminder.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $reminder = $this->reminders->findForUser($id, $request->user());

        if (! $reminder) {
            return response()->json([
                'message' => 'Reminder not found.',
            ], 404);
        }

        $this->reminderService->delete($reminder);

        return response()->json([
            'message' => 'Reminder deleted successfully.',
        ]);
    }

    /**
     * Cancel a pending reminder.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $reminder = $this->reminders->findForUser($id, $request->user());

        if (! $reminder) {
            return response()->json([
                'message' => 'Reminder not found.',
            ], 404);
        }

        if (! $reminder->isPending()) {
            return response()->json([
                'message' => 'Only pending reminders can be cancelled.',
            ], 422);
        }

        $this->reminderService->cancel($reminder);

        $reminder->load(['contract', 'deadline']);

        return ReminderResource::make($reminder->fresh())
            ->response()
            ->setStatusCode(200);
    }
}
