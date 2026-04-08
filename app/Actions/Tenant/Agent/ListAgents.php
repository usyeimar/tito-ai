<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Support\Collection;

final class ListAgents
{
    /** @return Collection<int, AgentData> */
    public function __invoke(): Collection
    {
        return Agent::with(['settings', 'tools', 'deployments'])
            ->orderBy('name')
            ->get()
            ->map(fn (Agent $agent) => AgentData::fromAgent($agent));
    }
}
