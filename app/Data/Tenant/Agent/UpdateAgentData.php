<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Data;

class UpdateAgentData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $slug,
        public ?string $description,
        public ?string $language,
        public ?array $tags,
        public ?string $timezone,
        public ?string $currency,
        public ?string $number_format,
        public ?string $knowledge_base_id,
        public ?array $brain_config,
        public ?array $runtime_config,
        public ?array $architecture_config,
        public ?array $capabilities_config,
        public ?array $observability_config,
    ) {}
}
