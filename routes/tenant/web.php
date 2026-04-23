<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Web\Agent\AgentPageController;
use App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController;
use App\Http\Middleware\HydrateTenantAuth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Stancl\Tenancy\Features\UserImpersonation;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Web (Inertia) Routes
|--------------------------------------------------------------------------
|
| These routes are mounted under `/{tenant}/...` via the path identification
| middleware and serve Inertia React pages for the workspace area.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
    HydrateTenantAuth::class,
])->prefix('{tenant}')->group(function (): void {
    Route::get('/impersonate/{token}', function (string $token) {
        return UserImpersonation::makeResponse($token);
    })->name('tenant.impersonate');
});

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
    HydrateTenantAuth::class,
    'auth:tenant',
    'has-access-to-workspace',
    CreateFreshApiToken::using('tenant'),
])->prefix('{tenant}')->group(function (): void {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard');
    })->name('tenant.dashboard');

    Route::prefix('agents')->name('tenant.agents.')->group(function (): void {
        Route::get('/', [AgentPageController::class, 'index'])->name('index');
        Route::get('{agent}', [AgentPageController::class, 'show'])->name('show');
    });

    Route::prefix('knowledge')->name('tenant.knowledge.')->group(function (): void {
        Route::get('/', [KnowledgeBasePageController::class, 'index'])->name('index');
    });
});
