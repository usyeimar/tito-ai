<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity\Support;

use Illuminate\Database\Eloquent\Model;

class KnownMorphTypes
{
    /**
     * @return array<string, class-string<Model>>
     */
    public function all(): array
    {
        $types = (array) config('activity-log.types', []);
        $known = [];

        foreach ($types as $type => $definition) {
            $modelClass = (string) data_get($definition, 'model', '');

            if ($type === '' || $modelClass === '' || ! class_exists($modelClass)) {
                continue;
            }

            /** @var class-string<Model> $modelClass */
            $known[(string) $type] = $modelClass;
        }

        return $known;
    }

    public function modelClassForType(string $type): ?string
    {
        $known = $this->all();

        return $known[$type] ?? null;
    }

    public function typeForModel(Model $model): ?string
    {
        foreach ($this->all() as $type => $modelClass) {
            if ($model instanceof $modelClass) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function labelFields(string $type): array
    {
        return array_values(array_filter(
            array_map(static fn (mixed $field): string => trim((string) $field), (array) data_get(config('activity-log.types.'.$type, []), 'label_fields', [])),
            static fn (string $field): bool => $field !== '',
        ));
    }
}
