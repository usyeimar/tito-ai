<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Data;

class CreateAgentDeploymentData extends Data
{
    public function __construct(
        public string $channel,
        public bool $enabled,
        public array $config,
        public string $version = '1.0.0',
        public ?string $status = 'active',
        public array $metadata = [],
    ) {}
}
