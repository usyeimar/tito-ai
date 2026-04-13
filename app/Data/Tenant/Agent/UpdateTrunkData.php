<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Data;

class UpdateTrunkData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $agent_id = null,
        public ?string $workspace_slug = null,
        public ?string $mode = null,
        public ?int $max_concurrent_calls = null,
        public ?array $codecs = null,
        public ?string $status = null,
        public ?array $inbound_auth = null,
        public ?array $routes = null,
        public ?string $sip_host = null,
        public ?int $sip_port = null,
        public ?array $register_config = null,
        public ?array $outbound = null,
    ) {}
}
