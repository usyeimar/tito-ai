<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListKnowledgeBaseCategories
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<KnowledgeBaseCategory>
     */
    public function __invoke(KnowledgeBase $knowledgeBase, array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for($knowledgeBase->categories())
            ->allowedFilters(
                AllowedFilter::partial('name'),
            )
            ->allowedSorts('name', 'display_order', 'created_at')
            ->defaultSort('display_order')
            ->paginateFromFilters($filters);
    }
}
