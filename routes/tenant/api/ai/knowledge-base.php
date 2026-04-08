<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController;
use App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController;
use App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('knowledge-bases')->group(function () {
    Route::get('/', [KnowledgeBaseController::class, 'index']);
    Route::post('/', [KnowledgeBaseController::class, 'store']);
    Route::get('{knowledgeBase}', [KnowledgeBaseController::class, 'show']);
    Route::patch('{knowledgeBase}', [KnowledgeBaseController::class, 'update']);
    Route::delete('{knowledgeBase}', [KnowledgeBaseController::class, 'destroy']);

    // Categories
    Route::prefix('{knowledgeBase}/categories')->group(function () {
        Route::get('/', [KnowledgeBaseCategoryController::class, 'index']);
        Route::post('/', [KnowledgeBaseCategoryController::class, 'store']);
        Route::patch('{category}', [KnowledgeBaseCategoryController::class, 'update']);
        Route::delete('{category}', [KnowledgeBaseCategoryController::class, 'destroy']);
    });

    // Documents
    Route::prefix('{knowledgeBase}/documents')->group(function () {
        Route::get('/', [KnowledgeBaseDocumentController::class, 'index']);
        Route::post('/', [KnowledgeBaseDocumentController::class, 'store']);
        Route::get('{document}', [KnowledgeBaseDocumentController::class, 'show']);
        Route::patch('{document}', [KnowledgeBaseDocumentController::class, 'update']);
        Route::delete('{document}', [KnowledgeBaseDocumentController::class, 'destroy']);
    });
});
