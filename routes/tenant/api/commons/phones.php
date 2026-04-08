<?php

use App\Http\Controllers\Tenant\Commons\Phones\PhonesController;
use Illuminate\Support\Facades\Route;

Route::prefix('phones')->name('commons.phones.')->group(function () {
    Route::get('labels', [PhonesController::class, 'labels'])->name('labels');
    Route::get('/', [PhonesController::class, 'index'])->name('index');
    Route::post('/', [PhonesController::class, 'store'])->name('store');
    Route::get('{phone}', [PhonesController::class, 'show'])->name('show');
    Route::patch('{phone}', [PhonesController::class, 'update'])->name('update');
    Route::post('{phone}/make-primary', [PhonesController::class, 'makePrimary'])->name('make-primary');
    Route::delete('{phone}', [PhonesController::class, 'destroy'])->name('destroy');
});
