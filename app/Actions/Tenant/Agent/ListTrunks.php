<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Trunk;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListTrunks
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Trunk>
     */
    public function __invoke(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Trunk::class)
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('mode'),
                AllowedFilter::exact('agent_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $term = '%'.$value.'%';
                    $query->where(function (Builder $q) use ($term): void {
                        $q->where('name', 'ilike', $term)
                            ->orWhere('sip_host', 'ilike', $term);
                    });
                }),
            )
            ->allowedSorts('name', 'status', 'mode', 'created_at', 'updated_at')
            ->defaultSort('name')
            ->paginateFromFilters($filters);
    }
}
