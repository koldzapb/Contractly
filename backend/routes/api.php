<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ContractChatController;
use App\Http\Controllers\ContractComparisonController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\QuickAnalysisController;
use App\Http\Controllers\ReminderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint (no rate limiting)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Guest routes (unauthenticated) - auth rate limit
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);
});

// Authenticated routes - general API rate limit
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', LogoutController::class);

    // Dashboard
    Route::get('/dashboard', DashboardController::class);

    // Quick Text Analysis (ephemeral, no storage)
    Route::post('/analyze-text', [QuickAnalysisController::class, 'analyze'])
        ->middleware('throttle:chat'); // Same rate limit as AI chat

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    });

    // Contract Comparison
    Route::post('/contracts/compare', [ContractComparisonController::class, 'compare']);

    // Contracts
    Route::prefix('contracts')->group(function () {
        Route::get('/', [ContractController::class, 'index']);
        Route::get('/{id}', [ContractController::class, 'show']);
        Route::put('/{id}', [ContractController::class, 'update']);
        Route::delete('/{id}', [ContractController::class, 'destroy']);
        Route::post('/{id}/retry', [ContractController::class, 'retryAnalysis']);

        // Document intelligence endpoints
        Route::post('/{id}/analyze-anyway', [ContractController::class, 'analyzeAnyway']);
        Route::get('/{id}/pii', [ContractController::class, 'getPii']);
        Route::post('/{id}/redact', [ContractController::class, 'applyRedactions']);
        Route::post('/{id}/skip-redaction', [ContractController::class, 'skipRedaction']);

        // Upload endpoint - stricter rate limit
        Route::post('/', [ContractController::class, 'store'])
            ->middleware('throttle:uploads');

        // Status polling - more lenient rate limit
        Route::get('/{id}/status', [ContractController::class, 'status'])
            ->withoutMiddleware('throttle:api')
            ->middleware('throttle:polling');

        // Contract Chat - AI rate limit
        Route::get('/{id}/chat', [ContractChatController::class, 'index']);
        Route::delete('/{id}/chat', [ContractChatController::class, 'destroy']);
        Route::post('/{id}/chat', [ContractChatController::class, 'store'])
            ->middleware('throttle:chat');

        // Export endpoints
        Route::get('/{id}/export/pdf', [ExportController::class, 'pdf']);
        Route::get('/{id}/export/clauses', [ExportController::class, 'clauses']);
        Route::get('/{id}/export/deadlines', [ExportController::class, 'deadlines']);
        Route::get('/{id}/export/all', [ExportController::class, 'all']);
    });

    // Reminders
    Route::prefix('reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index']);
        Route::post('/', [ReminderController::class, 'store']);
        Route::get('/{id}', [ReminderController::class, 'show']);
        Route::put('/{id}', [ReminderController::class, 'update']);
        Route::delete('/{id}', [ReminderController::class, 'destroy']);
        Route::post('/{id}/cancel', [ReminderController::class, 'cancel']);
    });
});
