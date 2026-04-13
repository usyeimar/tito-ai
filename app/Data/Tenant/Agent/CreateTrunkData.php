<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Data;

class CreateTrunkData extends Data
{
    public function __construct(
        public string $name,
        public ?string $agent_id = null,
        public string $workspace_slug = 'default',
        public string $mode = 'inbound',
        public int $max_concurrent_calls = 10,
        public ?array $codecs = ['ulaw', 'alaw'],
        public string $status = 'active',
        public ?array $inbound_auth = null,
        public ?array $routes = null,
        public ?string $sip_host = null,
        public int $sip_port = 5060,
        public ?array $register_config = null,
        public ?array $outbound = null,
    ) {}
}
