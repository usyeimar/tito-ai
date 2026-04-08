<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\CreateAgentDeploymentData;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentDeployment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class CreateAgentDeployment
{
    public function __construct(
        protected Agent $agent,
    ) {}

    public function __invoke(CreateAgentDeploymentData $data): AgentDeployment
    {
        Gate::authorize('update', $this->agent);

        return DB::transaction(function () use ($data) {
            // Si se está creando un nuevo deployment, desactivar otros del mismo canal
            if ($data->enabled) {
                $this->agent->deployments()
                    ->where('channel', $data->channel)
                    ->where('id', '!=', $this->agent->deployments()->where('channel', $data->channel)->first()?->id ?? '0')
                    ->update(['enabled' => false]);
            }

            return $this->agent->deployments()->create([
                'agent_id' => $this->agent->id,
                'channel' => $data->channel,
                'enabled' => $data->enabled,
                'config' => $data->config,
                'version' => $data->version,
                'deployed_at' => $data->enabled ? now() : null,
                'status' => $data->status,
                'metadata' => $data->metadata,
            ]);
        });
    }
}
