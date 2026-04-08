<?php

namespace App\Support;

class ModelLabels
{
    /**
     * @var array<string, string>
     */
    private const LABELS = [
        'Tenant' => 'Workspace',
    ];

    public static function labelForModel(?string $modelClass, string $default = 'Resource'): string
    {
        if (! $modelClass) {
            return $default;
        }

        $modelKey = class_basename($modelClass);

        return self::LABELS[$modelKey] ?? $default;
    }
}
