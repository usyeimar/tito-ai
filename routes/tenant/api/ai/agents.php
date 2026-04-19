<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\API\Agent\AgentController;
use App\Http\Controllers\Tenant\API\Agent\AgentSessionWebhookController;
use App\Http\Controllers\Tenant\API\Agent\AgentTestCallController;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')->name('agents.')
    ->group(function () {
        Route::get('/', [AgentController::class, 'index']);
        Route::post('/', [AgentController::class, 'store']);
        Route::get('{agent}', [AgentController::class, 'show']);
        Route::patch('{agent}', [AgentController::class, 'update']);
        Route::delete('{agent}', [AgentController::class, 'destroy']);

        Route::post('{agent}/test-call', [AgentTestCallController::class, 'start'])
            ->name('test-call.start');
        Route::delete('{agent}/test-call/{session}', [AgentTestCallController::class, 'stop'])
            ->name('test-call.stop');
    });

// Session status endpoints (no auth required for polling)
Route::get('ai/runner/sessions/{channelId}/status', [AgentTestCallController::class, 'status'])
    ->name('ai.runner.session.status')
    ->withoutMiddleware(['auth:tenant-api', 'auth:api']);

Route::post('ai/runner/sessions/{channelId}/user-ended', [AgentTestCallController::class, 'userEnded'])
    ->name('ai.runner.session.user-ended')
    ->withoutMiddleware(['auth:tenant-api', 'auth:api']);

// Webhook endpoint para recibir eventos del runner por sesión (sin autenticación de usuario)
Route::post('ai/runner/webhook/{channelId}', [AgentSessionWebhookController::class, 'handle'])
    ->name('ai.runner.webhook')
    ->withoutMiddleware(['auth:tenant-api', 'auth:api']);
