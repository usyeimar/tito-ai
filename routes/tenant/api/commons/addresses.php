<?php

use App\Http\Controllers\Tenant\Commons\Addresses\AddressesController;
use Illuminate\Support\Facades\Route;

Route::prefix('addresses')->name('commons.addresses.')->group(function () {
    Route::get('labels', [AddressesController::class, 'labels'])->name('labels');
    Route::get('/', [AddressesController::class, 'index'])->name('index');
    Route::post('/', [AddressesController::class, 'store'])->name('store');
    Route::get('{address}', [AddressesController::class, 'show'])->name('show');
    Route::patch('{address}', [AddressesController::class, 'update'])->name('update');
    Route::post('{address}/make-primary', [AddressesController::class, 'makePrimary'])->name('make-primary');
    Route::delete('{address}', [AddressesController::class, 'destroy'])->name('destroy');
});
