<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ContractChatController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
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

    // Contracts
    Route::prefix('contracts')->group(function () {
        Route::get('/', [ContractController::class, 'index']);
        Route::get('/{id}', [ContractController::class, 'show']);
        Route::put('/{id}', [ContractController::class, 'update']);
        Route::delete('/{id}', [ContractController::class, 'destroy']);
        Route::post('/{id}/retry', [ContractController::class, 'retryAnalysis']);

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
