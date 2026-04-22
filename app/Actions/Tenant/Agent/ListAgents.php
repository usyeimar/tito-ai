<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListAgents
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Agent>
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Agent::class)
            ->with(['settings', 'tools', 'deployments'])
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('slug'),
                AllowedFilter::partial('description'),
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $term = '%'.$value.'%';
                    $query->where(function (Builder $q) use ($term): void {
                        $q->where('name', 'ilike', $term)
                            ->orWhere('slug', 'ilike', $term)
                            ->orWhere('description', 'ilike', $term);
                    });
                }),
            )
            ->allowedSorts('name', 'created_at', 'updated_at')
            ->defaultSort('name')
            ->paginateFromFilters($filters);
    }
}
