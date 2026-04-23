<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Jobs\Tenant\KnowledgeBase\DeindexKnowledgeBaseDocument;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;

final class DeleteKnowledgeBaseDocument
{
    public function __invoke(KnowledgeBaseDocument $document): void
    {
        $vectorStoreId = $document->category?->knowledgeBase?->vector_store_id;
        $vectorStoreFileId = $document->vector_store_file_id;

        $document->delete();

        if ($vectorStoreId && $vectorStoreFileId) {
            DeindexKnowledgeBaseDocument::dispatch($vectorStoreId, $vectorStoreFileId);
        }
    }
}
