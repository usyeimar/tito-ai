<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Web\Agent\AgentPageController;
use App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController;
use Illuminate\Support\Facades\Route;
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
])->prefix('{tenant}')->group(function (): void {
    Route::get('/impersonate/{token}', function (string $token) {
        return \Stancl\Tenancy\Features\UserImpersonation::makeResponse($token);
    })->name('tenant.impersonate');
});

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
    'auth:tenant',
    \Laravel\Passport\Http\Middleware\CreateFreshApiToken::using('tenant'),
])->prefix('{tenant}')->group(function (): void {
    Route::get('/dashboard', function () {
        return \Inertia\Inertia::render('dashboard');
    })->name('tenant.dashboard');

    Route::prefix('agents')->name('tenant.agents.')->group(function (): void {
        Route::get('/', [AgentPageController::class, 'index'])->name('index');
        Route::get('{agent}', [AgentPageController::class, 'show'])->name('show');
    });

    Route::prefix('knowledge')->name('tenant.knowledge.')->group(function (): void {
        Route::get('/', [KnowledgeBasePageController::class, 'index'])->name('index');
    });
});
