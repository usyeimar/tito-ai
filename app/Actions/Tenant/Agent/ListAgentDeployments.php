<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentDeployment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListAgentDeployments
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AgentDeployment>
     */
    public function __invoke(Agent $agent, array $filters = []): LengthAwarePaginator
    {
        return $agent->deployments()
            ->orderByDesc('enabled')
            ->orderBy('channel')
            ->paginateFromFilters($filters);
    }
}
