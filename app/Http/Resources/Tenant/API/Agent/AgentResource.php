<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\API\Agent;

use App\Data\Tenant\Agent\AgentData;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var Agent $agent */
        $agent = $this->resource;

        return AgentData::fromAgent($agent)->toArray();
    }
}
