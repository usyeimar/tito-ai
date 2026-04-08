<?php

use App\Http\Controllers\Tenant\Commons\Files\FileFoldersController;
use App\Http\Controllers\Tenant\Commons\Files\FilesController;
use App\Http\Controllers\Tenant\Commons\Files\FileUploadsController;
use Illuminate\Support\Facades\Route;

Route::prefix('files')->name('commons.files.')->group(function () {
    Route::get('restrictions', [FilesController::class, 'restrictions'])->name('restrictions');
    Route::get('tree', [FileFoldersController::class, 'tree'])->name('tree');
    Route::get('/', [FilesController::class, 'index'])->name('index');

    Route::prefix('uploads')->name('uploads.')->group(function () {
        Route::post('/', [FileUploadsController::class, 'initiate'])->middleware('throttle:tenant.files.upload.initiate')->name('initiate');
        Route::post('{uploadSession}/parts/sign', [FileUploadsController::class, 'signParts'])->middleware('throttle:tenant.files.upload.sign_parts')->name('sign-parts');
        Route::get('{uploadSession}', [FileUploadsController::class, 'show'])->middleware('throttle:tenant.files.upload.resume')->name('show');
        Route::post('{uploadSession}/complete', [FileUploadsController::class, 'complete'])->middleware('throttle:tenant.files.upload.complete')->name('complete');
        Route::delete('{uploadSession}', [FileUploadsController::class, 'abort'])->middleware('throttle:tenant.files.upload.abort')->name('abort');
    });

    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('move', [FilesController::class, 'bulkMove'])->name('move');
        Route::post('delete', [FilesController::class, 'bulkDelete'])->name('delete');
        Route::post('restore', [FilesController::class, 'bulkRestore'])->name('restore');
        Route::post('force', [FilesController::class, 'bulkForceDelete'])->name('force-delete');
    });

    Route::match(['put', 'patch'], '{file}', [FilesController::class, 'update'])->name('update');
    Route::post('{file}/move', [FilesController::class, 'move'])->name('move');
    Route::delete('{file}', [FilesController::class, 'destroy'])->name('destroy');
    Route::post('{file}/restore', [FilesController::class, 'restore'])->name('restore');
    Route::delete('{file}/force', [FilesController::class, 'forceDelete'])->name('force-delete');
    Route::get('{file}/download', [FilesController::class, 'download'])->middleware('throttle:tenant.files.download')->name('download');

    Route::prefix('folders')->name('folders.')->group(function () {
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('move', [FileFoldersController::class, 'bulkMove'])->name('move');
            Route::post('delete', [FileFoldersController::class, 'bulkDelete'])->name('delete');
            Route::post('restore', [FileFoldersController::class, 'bulkRestore'])->name('restore');
            Route::post('force', [FileFoldersController::class, 'bulkForceDelete'])->name('force-delete');
        });

        Route::post('/', [FileFoldersController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{folder}', [FileFoldersController::class, 'update'])->name('update');
        Route::post('{folder}/move', [FileFoldersController::class, 'move'])->name('move');
        Route::get('{folder}/breadcrumbs', [FileFoldersController::class, 'breadcrumbs'])->name('breadcrumbs');
        Route::delete('{folder}', [FileFoldersController::class, 'destroy'])->name('destroy');
        Route::post('{folder}/restore', [FileFoldersController::class, 'restore'])->name('restore');
        Route::delete('{folder}/force', [FileFoldersController::class, 'forceDelete'])->name('force-delete');
    });
});
