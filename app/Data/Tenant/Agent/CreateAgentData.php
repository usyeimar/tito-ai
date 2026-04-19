<?php

declare(strict_types=1);

namespace App\Data\Tenant\Agent;

use Spatie\LaravelData\Data;

class CreateAgentData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $description = null,
        public string $language = 'es-CO',
        public ?array $tags = [],
        public string $timezone = 'UTC',
        public string $currency = 'COP',
        public string $number_format = 'es_CO',
        public ?string $knowledge_base_id = null,
        public ?array $brain_config = [],
        public ?array $runtime_config = [],
        public ?array $architecture_config = [],
        public ?array $capabilities_config = [],
        public ?array $observability_config = [],
        public bool $from_scratch = false,
    ) {}
}
