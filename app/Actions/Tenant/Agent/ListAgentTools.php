<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentTool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListAgentTools
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<AgentTool>
     */
    public function __invoke(Agent $agent, array $filters = []): LengthAwarePaginator
    {
        return $agent->tools()
            ->orderBy('name')
            ->paginateFromFilters($filters);
    }
}
