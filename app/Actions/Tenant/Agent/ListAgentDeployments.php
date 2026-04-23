<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentDeployment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListAgentDeployments
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AgentDeployment>
     */
    public function __invoke(Agent $agent, array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for($agent->deployments())
            ->allowedFilters(
                AllowedFilter::exact('channel'),
                AllowedFilter::exact('enabled'),
            )
            ->allowedSorts('channel', 'enabled', 'created_at')
            ->defaultSort('-enabled', 'channel')
            ->paginateFromFilters($filters);
    }
}
