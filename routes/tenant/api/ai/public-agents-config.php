<?php

use App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/widget-config')->name('widget-config.')->group(function () {
    Route::get('/web/{agentSlug}', [WidgetConfigController::class, 'getWebWidgetConfig'])
        ->name('web');

    Route::get('/sip/{agentSlug}', [WidgetConfigController::class, 'getSipWidgetConfig'])
        ->name('sip');
});
