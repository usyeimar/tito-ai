<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Data\Tenant\Agent\CreateAgentData;
use App\Jobs\Tenant\Agent\SyncAgentToRedisJob;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CreateAgent
{
    public function __invoke(CreateAgentData $data): AgentData
    {
        return DB::transaction(function () use ($data): AgentData {

            $name = $data->name ?? 'Nuevo Agente '.now()->format('Y-m-d H:i');
            $slug = $data->slug ?? Str::slug($name).'-'.Str::random(4);

            $agent = Agent::create([
                'name' => $name,
                'slug' => $slug,
                'description' => $data->description,
                'language' => $data->language,
                'tags' => $data->tags ?? [],
                'timezone' => $data->timezone,
                'currency' => $data->currency,
                'number_format' => $data->number_format,
                'knowledge_base_id' => $data->knowledge_base_id,
            ]);

            $settings = $this->buildSettings($data, $agent);

            AgentSetting::create($settings);

            $agent->load(['settings']);

            // Sync agent to Redis for SIP bridge resolution
            $this->syncToRedis($agent);

            return AgentData::fromAgent($agent);
        });
    }

    /**
     * Build settings for the agent, applying defaults when from_scratch is true
     * or when no configurations are provided.
     *
     * @return array<string, mixed>
     */
    private function buildSettings(CreateAgentData $data, Agent $agent): array
    {
        $needsDefaults = $data->from_scratch
            || empty($data->brain_config)
            || empty($data->runtime_config);

        if (! $needsDefaults) {
            return [
                'agent_id' => $agent->id,
                'brain_config' => $data->brain_config ?? [],
                'runtime_config' => $data->runtime_config ?? [],
                'architecture_config' => $data->architecture_config ?? [],
                'capabilities_config' => $data->capabilities_config ?? [],
                'observability_config' => $data->observability_config ?? [],
            ];
        }

        return [
            'agent_id' => $agent->id,
            'brain_config' => $data->brain_config ?: [
                'llm' => [
                    'provider' => 'openai',
                    'model' => 'gpt-4o-mini',
                    'config' => [
                        'temperature' => 0.5,
                        'max_tokens' => 1024,
                        'top_p' => 0.9,
                    ],
                    'instructions' => 'You are a helpful assistant.',
                ],
                'localization' => [
                    'default_locale' => $data->language,
                    'timezone' => $data->timezone,
                    'currency' => $data->currency,
                    'number_format' => $data->number_format,
                ],
                'context' => [
                    'strategy' => 'none',
                    'max_tokens' => 4000,
                    'min_messages' => 4,
                    'enabled' => false,
                ],
            ],
            'runtime_config' => $data->runtime_config ?: [
                'stt' => [
                    'provider' => 'deepgram',
                    'model' => 'nova-2',
                    'language' => $data->language,
                ],
                'tts' => [
                    'provider' => 'cartesia',
                    'voice_id' => '79a125e8-cd45-4c13-8a67-188112f4dd22',
                ],
                'transport' => [
                    'provider' => 'livekit',
                ],
                'behavior' => [
                    'interruptibility' => true,
                    'initial_action' => 'SPEAK_FIRST',
                    'streaming' => true,
                ],
            ],
            'architecture_config' => $data->architecture_config ?: [],
            'capabilities_config' => $data->capabilities_config ?: [],
            'observability_config' => $data->observability_config ?: [],
        ];
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
            // Don't fail agent creation if sync fails, just log it
            Log::warning('Failed to dispatch agent sync job', [
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
