<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListKnowledgeBaseDocuments
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<KnowledgeBaseDocument>
     */
    public function __invoke(KnowledgeBase $knowledgeBase, array $filters = []): LengthAwarePaginator
    {
        return KnowledgeBaseDocument::query()
            ->whereHas('category', fn ($q) => $q->where('knowledge_base_id', $knowledgeBase->id))
            ->when(! empty($filters['filter']['knowledge_base_category_id']), function ($q) use ($filters): void {
                $q->where('knowledge_base_category_id', $filters['filter']['knowledge_base_category_id']);
            })
            ->orderByDesc('created_at')
            ->paginateFromFilters($filters);
    }
}
