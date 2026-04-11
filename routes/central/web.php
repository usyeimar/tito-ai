<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workspaces', [\App\Http\Controllers\Central\Web\Tenancy\TenantController::class, 'index'])->name('workspaces');
    Route::post('workspaces', [\App\Http\Controllers\Central\Web\Tenancy\TenantController::class, 'store'])->name('workspaces.store');
    Route::get('workspaces/{tenant:slug}/enter', [\App\Http\Controllers\Central\Web\Tenancy\TenantController::class, 'enter'])->name('workspaces.enter');
});

require __DIR__.'/web/settings.php';
