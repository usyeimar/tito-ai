<?php

use App\Http\Controllers\Tenant\Commons\Emails\EmailsController;
use Illuminate\Support\Facades\Route;

Route::prefix('emails')->name('commons.emails.')->group(function () {
    Route::get('labels', [EmailsController::class, 'labels'])->name('labels');
    Route::get('/', [EmailsController::class, 'index'])->name('index');
    Route::post('/', [EmailsController::class, 'store'])->name('store');
    Route::get('{email}', [EmailsController::class, 'show'])->name('show');
    Route::patch('{email}', [EmailsController::class, 'update'])->name('update');
    Route::post('{email}/make-primary', [EmailsController::class, 'makePrimary'])->name('make-primary');
    Route::delete('{email}', [EmailsController::class, 'destroy'])->name('destroy');
});
