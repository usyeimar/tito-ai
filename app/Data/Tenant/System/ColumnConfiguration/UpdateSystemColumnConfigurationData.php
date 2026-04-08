<?php

namespace App\Data\Tenant\System\ColumnConfiguration;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateSystemColumnConfigurationData extends Data
{
    public function __construct(
        public array|Optional $data = new Optional,
    ) {}

    public static function rules(): array
    {
        return [
            'data' => ['sometimes', 'array'],
        ];
    }
}
