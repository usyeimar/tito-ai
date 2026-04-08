<?php

use App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemUserColumnConfigurationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('user-column-configurations')->group(function () {
    Route::get('/', [SystemUserColumnConfigurationsController::class, 'index']);
    Route::post('/', [SystemUserColumnConfigurationsController::class, 'store']);
    Route::get('{systemUserColumnConfiguration}', [SystemUserColumnConfigurationsController::class, 'show']);
    Route::match(['put', 'patch'], '{systemUserColumnConfiguration}', [SystemUserColumnConfigurationsController::class, 'update']);
    Route::delete('{systemUserColumnConfiguration}', [SystemUserColumnConfigurationsController::class, 'destroy']);
});
