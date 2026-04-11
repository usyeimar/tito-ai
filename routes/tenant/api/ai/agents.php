<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\API\Agent\AgentController;
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
