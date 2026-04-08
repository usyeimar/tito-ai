<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\UpdateAgentDeploymentData;
use App\Models\Tenant\Agent\AgentDeployment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UpdateAgentDeployment
{
    public function __construct(
        protected AgentDeployment $deployment,
    ) {}

    public function __invoke(UpdateAgentDeploymentData $data): AgentDeployment
    {
        Gate::authorize('update', $this->deployment->agent);

        return DB::transaction(function () use ($data) {
            // Si se está habilitando este deployment, deshabilitar otros del mismo canal para el mismo agente
            if ($data->enabled === true) {
                $this->deployment->agent->deployments()
                    ->where('channel', $this->deployment->channel)
                    ->where('id', '!=', $this->deployment->id)
                    ->update(['enabled' => false]);
            }

            // Actualizar solo los campos que fueron proporcionados
            $updateData = [];

            if ($data->channel !== null) {
                $updateData['channel'] = $data->channel;
            }

            if ($data->enabled !== null) {
                $updateData['enabled'] = $data->enabled;
                // Si se está habilitando, establecer deployed_at
                if ($data->enabled === true) {
                    $updateData['deployed_at'] = now();
                }
                // Si se está deshabilitando, podemos mantener deployed_at como referencia histórica
            }

            if ($data->config !== null) {
                $updateData['config'] = $data->config;
            }

            if ($data->version !== null) {
                $updateData['version'] = $data->version;
            }

            if ($data->status !== null) {
                $updateData['status'] = $data->status;
            }

            if ($data->metadata !== null) {
                $updateData['metadata'] = $data->metadata;
            }

            $this->deployment->update($updateData);

            return $this->deployment->fresh();
        });
    }
}
