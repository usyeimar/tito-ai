<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Support\Collection;

final class ListAgents
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, AgentData>
     */
    public function __invoke(array $filters = []): Collection
    {
        $query = Agent::with(['settings', 'tools', 'deployments'])
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', $search)
                    ->orWhere('slug', 'ilike', $search)
                    ->orWhere('description', 'ilike', $search);
            });
        }

        return $query->get()->map(fn (Agent $agent) => AgentData::fromAgent($agent));
    }
}
