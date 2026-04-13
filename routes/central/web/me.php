<?php

declare(strict_types=1);

use App\Http\Controllers\Central\Web\Me\ProfileController;
use App\Http\Controllers\Central\Web\Me\SecurityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Me Routes (User Account Management)
|--------------------------------------------------------------------------
|
| These routes handle the user's own account management pages like
| profile settings and security settings.
|
*/

Route::middleware(['auth', 'verified'])->prefix('me')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('me.profile');
    Route::get('/security', [SecurityController::class, 'show'])->name('me.security');
});
