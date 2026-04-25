<?php

declare(strict_types=1);

namespace App\Services\Tenant\Agent\Runner;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Translates a tenant Agent (with its settings + tools) into the
 * AgentConfig payload expected by the FastAPI runner.
 *
 * @see services/runners/app/schemas/agent.py
 */
final class AgentConfigBuilder
{
    /**
     * @param  array<array{name: string, value: string}>  $variables
     * @return array<string, mixed>
     */
    public function build(Agent $agent, array $variables = []): array
    {
        $cacheKey = "agent_config:{$agent->id}:{$agent->updated_at?->timestamp}";

        return Cache::remember($cacheKey, 300, fn () => $this->buildFresh($agent, $variables));
    }

    /**
     * Invalidate cached config for an agent.
     */
    public function invalidate(Agent $agent): void
    {
        $pattern = "agent_config:{$agent->id}:*";
        Cache::forget("agent_config:{$agent->id}:{$agent->updated_at?->timestamp}");
    }

    /**
     * @param  array<array{name: string, value: string}>  $variables
     * @return array<string, mixed>
     */
    private function buildFresh(Agent $agent, array $variables = []): array
    {
        $agent->loadMissing(['settings', 'tools']);

        $brain = (array) ($agent->settings?->brain_config ?? []);
        $runtime = (array) ($agent->settings?->runtime_config ?? []);
        $architecture = (array) ($agent->settings?->architecture_config ?? []);
        $capabilities = (array) ($agent->settings?->capabilities_config ?? []);
        $observability = (array) ($agent->settings?->observability_config ?? []);

        $tenantId = (string) (tenant('id') ?? 'central');

        return array_filter([
            'version' => '1.0.0',
            'agent_id' => (string) $agent->id,
            'tenant_id' => $tenantId,
            'callback_url' => $this->sessionCallbackUrl($tenantId),
            'metadata' => [
                'name' => (string) $agent->name,
                'slug' => (string) $agent->slug,
                'description' => (string) ($agent->description ?? ''),
                'tags' => array_values((array) ($agent->tags ?? [])),
                'language' => (string) $agent->language,
            ],
            'brain' => $this->brainPayload($brain, $agent, $variables),
            'runtime_profiles' => $this->runtimePayload($runtime, $agent),
            'architecture' => $architecture !== [] ? $architecture : null,
            'capabilities' => $this->capabilitiesPayload($capabilities, $agent),
            'orchestration' => $this->orchestrationPayload($capabilities),
            'compliance' => $this->compliancePayload($observability),
            'observability' => $this->observabilityPayload($observability),
        ], fn ($v) => $v !== null);
    }

    /**
     * @param  array<string, mixed>  $brain
     * @param  array<array{name: string, value: string}>  $variables
     * @return array<string, mixed>
     */
    private function brainPayload(array $brain, Agent $agent, array $variables = []): array
    {
        $llm = (array) Arr::get($brain, 'llm', []);

        $instructions = (string) Arr::get($llm, 'instructions', Arr::get($brain, 'system_prompt', 'You are a helpful assistant.'));

        foreach ($variables as $variable) {
            $instructions = str_replace(
                '{'.$variable['name'].'}',
                $variable['value'],
                $instructions,
            );
        }

        $payload = [
            'llm' => [
                'provider' => (string) Arr::get($llm, 'provider', Arr::get($brain, 'provider', 'openai')),
                'model' => (string) Arr::get($llm, 'model', Arr::get($brain, 'model', 'gpt-4o-mini')),
                'config' => [
                    'temperature' => (float) Arr::get($llm, 'config.temperature', Arr::get($brain, 'temperature', 0.5)),
                    'max_tokens' => (int) Arr::get($llm, 'config.max_tokens', Arr::get($brain, 'max_tokens', 4096)),
                    'top_p' => (float) Arr::get($llm, 'config.top_p', Arr::get($brain, 'top_p', 0.9)),
                ],
                'instructions' => $instructions,
            ],
            'localization' => [
                'default_locale' => (string) $agent->language,
                'timezone' => (string) $agent->timezone,
                'currency' => (string) $agent->currency,
                'number_format' => (string) $agent->number_format,
            ],
            'context' => Arr::get($brain, 'context', [
                'strategy' => 'none',
                'max_tokens' => 4000,
                'min_messages' => 4,
                'enabled' => false,
            ]),
        ];

        $knowledgeBase = Arr::get($brain, 'knowledge_base');
        if ($knowledgeBase !== null) {
            $payload['knowledge_base'] = $knowledgeBase;
        } elseif ($agent->knowledge_base_id !== null) {
            $payload['knowledge_base'] = ['id' => $agent->knowledge_base_id];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $runtime
     * @return array<string, mixed>
     */
    private function runtimePayload(array $runtime, Agent $agent): array
    {
        $stt = (array) Arr::get($runtime, 'stt', []);
        $tts = (array) Arr::get($runtime, 'tts', []);
        $behavior = (array) Arr::get($runtime, 'behavior', []);
        $vad = Arr::get($runtime, 'vad');
        $sessionLimits = Arr::get($runtime, 'session_limits');

        // Transport is NOT sent — the runner decides which provider
        // (livekit/daily) to use based on its own configuration.

        $payload = [
            'stt' => array_filter([
                'provider' => (string) Arr::get($stt, 'provider', 'deepgram'),
                'model' => (string) Arr::get($stt, 'model', 'nova-2'),
                'language' => (string) Arr::get($stt, 'language', $agent->language),
                'latency_mode' => Arr::get($stt, 'latency_mode'),
            ], fn ($v) => $v !== null),
            'tts' => array_filter([
                'provider' => (string) Arr::get($tts, 'provider', 'cartesia'),
                'voice_id' => (string) Arr::get($tts, 'voice_id', '79a125e8-cd45-4c13-8a67-188112f4dd22'),
                'model_id' => Arr::get($tts, 'model_id'),
                'speed' => Arr::get($tts, 'speed'),
                'latency_mode' => Arr::get($tts, 'latency_mode'),
            ], fn ($v) => $v !== null),
            'behavior' => array_filter([
                'interruptibility' => (bool) Arr::get($behavior, 'interruptibility', true),
                'initial_action' => (string) Arr::get($behavior, 'initial_action', 'SPEAK_FIRST'),
                'streaming' => (bool) Arr::get($behavior, 'streaming', true),
                'ambient_sound' => Arr::get($behavior, 'ambient_sound'),
                'thinking_sound' => Arr::get($behavior, 'thinking_sound'),
                'user_mute_strategies' => Arr::get($behavior, 'user_mute_strategies'),
                'turn_detection_strategy' => Arr::get($behavior, 'turn_detection_strategy'),
                'turn_detection_timeout_ms' => Arr::get($behavior, 'turn_detection_timeout_ms'),
                'smart_turn_stop_secs' => Arr::get($behavior, 'smart_turn_stop_secs'),
            ], fn ($v) => $v !== null),
        ];

        if (is_array($vad) && $vad !== []) {
            $payload['vad'] = array_filter([
                'provider' => (string) Arr::get($vad, 'provider', 'silero'),
                'params' => Arr::get($vad, 'params'),
            ], fn ($v) => $v !== null);
        }

        if (is_array($sessionLimits) && $sessionLimits !== []) {
            $payload['session_limits'] = array_filter([
                'max_duration_seconds' => Arr::get($sessionLimits, 'max_duration_seconds'),
                'inactivity_timeout' => Arr::get($sessionLimits, 'inactivity_timeout'),
            ], fn ($v) => $v !== null);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $capabilities
     * @return array<string, mixed>
     */
    private function capabilitiesPayload(array $capabilities, Agent $agent): array
    {
        $configuredTools = (array) Arr::get($capabilities, 'tools', []);

        if ($configuredTools === []) {
            $configuredTools = $agent->tools
                ->map(fn ($tool) => array_filter([
                    'name' => $tool->name,
                    'description' => $tool->description,
                    'parameters' => $tool->parameters ?? null,
                    'processing_message' => $tool->processing_message ?? null,
                    'disabled' => ! ($tool->is_active ?? true),
                ], fn ($v) => $v !== null))
                ->all();
        }

        return [
            'tools' => array_values($configuredTools),
        ];
    }

    /**
     * @param  array<string, mixed>  $capabilities
     * @return array<string, mixed>|null
     */
    private function orchestrationPayload(array $capabilities): ?array
    {
        $orchestration = (array) Arr::get($capabilities, 'orchestration', []);

        if ($orchestration === []) {
            return null;
        }

        return array_filter([
            'routing_logic' => Arr::get($orchestration, 'routing_logic'),
            'session_context' => Arr::get($orchestration, 'session_context', []),
        ], fn ($v) => $v !== null);
    }

    /**
     * @param  array<string, mixed>  $observability
     * @return array<string, mixed>|null
     */
    private function compliancePayload(array $observability): ?array
    {
        $compliance = (array) Arr::get($observability, 'compliance', []);

        if ($compliance === []) {
            return null;
        }

        return [
            'pii_redaction' => (bool) Arr::get($compliance, 'pii_redaction', false),
            'record_audio' => (bool) Arr::get($compliance, 'record_audio', false),
        ];
    }

    /**
     * @param  array<string, mixed>  $observability
     * @return array<string, mixed>|null
     */
    private function observabilityPayload(array $observability): ?array
    {
        if ($observability === []) {
            return null;
        }

        return [
            'log_level' => (string) Arr::get($observability, 'log_level', 'INFO'),
            'metrics_enabled' => (bool) Arr::get($observability, 'metrics_enabled', false),
        ];
    }

    /**
     * Generate the base callback URL for receiving runner events.
     */
    private function sessionCallbackUrl(string $tenantId): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return "{$baseUrl}/api/ai/runner/webhook";
    }
}
