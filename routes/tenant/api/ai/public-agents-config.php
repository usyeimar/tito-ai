<?php

use App\Http\Controllers\Tenant\API\Agent\AgentConfigController;
use App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/widget-config')->name('widget-config.')->group(function () {
    Route::get('/web/{agentSlug}', [WidgetConfigController::class, 'getWebWidgetConfig'])
        ->name('web');

    Route::get('/sip/{agentSlug}', [WidgetConfigController::class, 'getSipWidgetConfig'])
        ->name('sip');
});

// Agent config endpoints for runners service (SIP bridge)
// These endpoints provide the full AgentConfig for the Python/FastAPI runners
Route::prefix('/api/agents')->name('agents.')->group(function () {
    Route::get('/{agentId}/config', [AgentConfigController::class, 'getConfigById'])
        ->name('config.by-id');

    Route::get('/by-slug/{agentSlug}/config', [AgentConfigController::class, 'getConfigBySlug'])
        ->name('config.by-slug');
});
