<?php

namespace App\Data\Tenant\System\ColumnConfiguration;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class SystemColumnConfigurationData extends Data
{
    public function __construct(
        public string $id,
        public string $module,
        public array $data,
        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
    ) {}
}
