<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

class UpdateAgentDeploymentData extends Data
{
    public function __construct(
        #[MapOutputName('channel')]
        public ?string $channel = null,
        #[MapOutputName('enabled')]
        public ?bool $enabled = null,
        #[MapOutputName('config')]
        public ?array $config = null,
        #[MapOutputName('version')]
        public ?string $version = null,
        #[MapOutputName('status')]
        public ?string $status = null,
        #[MapOutputName('metadata')]
        public ?array $metadata = null,
    ) {}
}
