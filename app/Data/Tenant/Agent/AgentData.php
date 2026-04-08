<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use Spatie\LaravelData\Data;

class AgentData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $language,
        public ?array $tags,
        public string $timezone,
        public string $currency,
        public string $number_format,
        public ?string $knowledge_base_id,
        public ?array $brain_config,
        public ?array $runtime_config,
        public ?array $architecture_config,
        public ?array $capabilities_config,
        public ?array $observability_config,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromAgent(Agent $agent): self
    {
        $agent->loadMissing('settings');
        $settings = $agent->settings;

        return new self(
            id: $agent->id,
            name: $agent->name,
            slug: $agent->slug,
            description: $agent->description,
            language: $agent->language,
            tags: $agent->tags,
            timezone: $agent->timezone,
            currency: $agent->currency,
            number_format: $agent->number_format,
            knowledge_base_id: $agent->knowledge_base_id,
            brain_config: $settings?->brain_config,
            runtime_config: $settings?->runtime_config,
            architecture_config: $settings?->architecture_config,
            capabilities_config: $settings?->capabilities_config,
            observability_config: $settings?->observability_config,
            created_at: $agent->created_at?->toIso8601String(),
            updated_at: $agent->updated_at?->toIso8601String(),
        );
    }
}
