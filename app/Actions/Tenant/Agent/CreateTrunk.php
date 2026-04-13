<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\CreateTrunkData;
use App\Jobs\Tenant\Agent\SyncTrunkToRedisJob;
use App\Models\Tenant\Agent\Trunk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CreateTrunk
{
    public function __invoke(CreateTrunkData $data): Trunk
    {
        return DB::transaction(function () use ($data): Trunk {
            $trunk = Trunk::create([
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
            ]);

            // Sync trunk to Redis for SIP bridge resolution
            $this->syncToRedis($trunk);

            return $trunk;
        });
    }

    /**
     * Dispatch job to sync trunk configuration to Redis.
     * This allows the SIP bridge to resolve trunk configs when calls arrive from Asterisk.
     */
    private function syncToRedis(Trunk $trunk): void
    {
        try {
            SyncTrunkToRedisJob::dispatch((string) $trunk->id);
        } catch (\Throwable $e) {
            // Don't fail trunk creation if sync fails, just log it
            Log::warning('Failed to dispatch trunk sync job', [
                'trunk_id' => $trunk->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
