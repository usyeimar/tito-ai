<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Data\Tenant\Agent\UpdateAgentData;
use App\Jobs\Tenant\Agent\SyncAgentToRedisJob;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UpdateAgent
{
    public function __invoke(Agent $agent, UpdateAgentData $data): AgentData
    {
        return DB::transaction(function () use ($agent, $data): AgentData {
            $originalSlug = $agent->slug;

            $agent->fill(array_filter([
                'name' => $data->name,
                'slug' => $data->slug,
                'description' => $data->description,
                'language' => $data->language,
                'tags' => $data->tags,
                'timezone' => $data->timezone,
                'currency' => $data->currency,
                'number_format' => $data->number_format,
                'knowledge_base_id' => $data->knowledge_base_id,
            ], static fn ($value) => $value !== null));

            $agent->save();

            $settingsPayload = array_filter([
                'brain_config' => $data->brain_config,
                'runtime_config' => $data->runtime_config,
                'architecture_config' => $data->architecture_config,
                'capabilities_config' => $data->capabilities_config,
                'observability_config' => $data->observability_config,
            ], static fn ($value) => $value !== null);

            if ($settingsPayload !== []) {
                AgentSetting::updateOrCreate(
                    ['agent_id' => $agent->id],
                    $settingsPayload,
                );
            }

            $agent->load(['settings']);

            // Sync agent to Redis when configuration changes
            $this->syncToRedis($agent);

            return AgentData::fromAgent($agent);
        });
    }

    /**
     * Dispatch job to sync agent configuration to Redis.
     * This allows the SIP bridge to resolve agent configs when calls arrive from Asterisk.
     */
    private function syncToRedis(Agent $agent): void
    {
        try {
            SyncAgentToRedisJob::dispatch((string) $agent->id);
        } catch (\Throwable $e) {
            // Don't fail agent update if sync fails, just log it
            Log::warning('Failed to dispatch agent sync job', [
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
