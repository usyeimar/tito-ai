<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentTool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListAgentTools
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AgentTool>
     */
    public function __invoke(Agent $agent, array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for($agent->tools())
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('is_active'),
            )
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->paginateFromFilters($filters);
    }
}
