<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListKnowledgeBases
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<KnowledgeBase>
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(KnowledgeBase::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('description'),
                AllowedFilter::exact('is_public'),
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $term = '%'.$value.'%';
                    $query->where(function (Builder $q) use ($term): void {
                        $q->where('name', 'ilike', $term)
                            ->orWhere('description', 'ilike', $term);
                    });
                }),
            )
            ->allowedSorts('name', 'created_at', 'updated_at')
            ->defaultSort('name')
            ->paginateFromFilters($filters);
    }
}
