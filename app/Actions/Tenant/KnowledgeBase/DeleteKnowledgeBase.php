<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Stores;
use Throwable;

final class DeleteKnowledgeBase
{
    public function __invoke(KnowledgeBase $knowledgeBase): void
    {
        $vectorStoreId = $knowledgeBase->vector_store_id;

        $knowledgeBase->delete();

        if ($vectorStoreId) {
            try {
                Stores::delete($vectorStoreId);
            } catch (Throwable $e) {
                Log::warning('Failed to delete vector store for knowledge base.', [
                    'vector_store_id' => $vectorStoreId,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }
}
