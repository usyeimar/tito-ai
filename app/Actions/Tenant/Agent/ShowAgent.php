<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Models\Tenant\Agent\Agent;

final class ShowAgent
{
    public function __invoke(Agent $agent): AgentData
    {
        return AgentData::fromAgent($agent->load(['settings', 'tools', 'deployments']));
    }
}
