<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\UpdateTrunkData;
use App\Jobs\Tenant\Agent\SyncTrunkToRedisJob;
use App\Models\Tenant\Agent\Trunk;
use Illuminate\Support\Facades\Log;

final class UpdateTrunk
{
    public function __invoke(Trunk $trunk, UpdateTrunkData $data): Trunk
    {
        $trunk->update(array_filter([
            'name' => $data->name,
            'agent_id' => $data->agent_id,
            'workspace_slug' => $data->workspace_slug,
            'mode' => $data->mode,
            'max_concurrent_calls' => $data->max_concurrent_calls,
            'codecs' => $data->codecs,
            'status' => $data->status,
            'inbound_auth' => $data->inbound_auth,
            'routes' => $data->routes,
            'sip_host' => $data->sip_host,
            'sip_port' => $data->sip_port,
            'register_config' => $data->register_config,
            'outbound' => $data->outbound,
        ], fn ($value) => $value !== null));

        // Sync trunk to Redis for SIP bridge resolution
        $this->syncToRedis($trunk);

        return $trunk;
    }

    /**
     * Dispatch job to sync trunk configuration to Redis.
     */
    private function syncToRedis(Trunk $trunk): void
    {
        try {
            SyncTrunkToRedisJob::dispatch((string) $trunk->id);
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch trunk sync job', [
                'trunk_id' => $trunk->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
