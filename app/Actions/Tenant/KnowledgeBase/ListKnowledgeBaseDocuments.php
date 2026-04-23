<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListKnowledgeBaseDocuments
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<KnowledgeBaseDocument>
     */
    public function __invoke(KnowledgeBase $knowledgeBase, array $filters = []): LengthAwarePaginator
    {
        $baseQuery = KnowledgeBaseDocument::query()
            ->whereHas('category', fn (Builder $q) => $q->where('knowledge_base_id', $knowledgeBase->id));

        return QueryBuilder::for($baseQuery)
            ->allowedFilters(
                AllowedFilter::exact('knowledge_base_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('indexing_status'),
                AllowedFilter::partial('title'),
            )
            ->allowedSorts('title', 'status', 'created_at', 'updated_at')
            ->defaultSort('-created_at')
            ->paginateFromFilters($filters);
    }
}
