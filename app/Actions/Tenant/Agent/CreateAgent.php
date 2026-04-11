<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Data\Tenant\Agent\CreateAgentData;
use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\AgentSetting;
use Illuminate\Support\Facades\DB;

final class CreateAgent
{
    public function __invoke(CreateAgentData $data): AgentData
    {
        return DB::transaction(function () use ($data): AgentData {
            $agent = Agent::create([
                'name' => $data->name,
                'slug' => $data->slug,
                'description' => $data->description,
                'language' => $data->language,
                'tags' => $data->tags ?? [],
                'timezone' => $data->timezone,
                'currency' => $data->currency,
                'number_format' => $data->number_format,
                'knowledge_base_id' => $data->knowledge_base_id,
            ]);

            AgentSetting::create([
                'agent_id' => $agent->id,
                'brain_config' => $data->brain_config ?? [],
                'runtime_config' => $data->runtime_config ?? [],
                'architecture_config' => $data->architecture_config ?? [],
                'capabilities_config' => $data->capabilities_config ?? [],
                'observability_config' => $data->observability_config ?? [],
            ]);

            return AgentData::fromAgent($agent->fresh(['settings']));
        });
    }
}
