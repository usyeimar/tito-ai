<?php

namespace App\Data\Tenant\System\ColumnConfiguration;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

class CreateSystemUserColumnConfigurationData extends Data
{
    public function __construct(
        public string $module,
        public array $data,
    ) {}

    public static function rules(): array
    {
        return [
            'module' => ['required', 'string', Rule::in(config('column-configuration.allowed_modules'))],
            'data' => ['required', 'array'],
        ];
    }
}
