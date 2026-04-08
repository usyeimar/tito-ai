<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\API\Agent\AgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')->name('agents.')
    ->group(function () {
        Route::get('/', [AgentController::class, 'index']);
        Route::post('/', [AgentController::class, 'store']);
        Route::get('{agent}', [AgentController::class, 'show']);
        Route::patch('{agent}', [AgentController::class, 'update']);
        Route::delete('{agent}', [AgentController::class, 'destroy']);
    });
