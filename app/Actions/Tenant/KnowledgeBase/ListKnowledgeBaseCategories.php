<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListKnowledgeBaseCategories
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<KnowledgeBaseCategory>
     */
    public function __invoke(KnowledgeBase $knowledgeBase, array $filters = []): LengthAwarePaginator
    {
        return $knowledgeBase->categories()
            ->orderBy('display_order')
            ->paginateFromFilters($filters);
    }
}
