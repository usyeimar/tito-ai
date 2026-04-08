<?php

use App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('column-configurations')->group(function () {
    Route::get('/', [SystemColumnConfigurationsController::class, 'index']);
    Route::post('/', [SystemColumnConfigurationsController::class, 'store']);
    Route::get('{systemColumnConfiguration}', [SystemColumnConfigurationsController::class, 'show']);
    Route::match(['put', 'patch'], '{systemColumnConfiguration}', [SystemColumnConfigurationsController::class, 'update']);
    Route::delete('{systemColumnConfiguration}', [SystemColumnConfigurationsController::class, 'destroy']);
});
