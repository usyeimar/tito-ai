<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity\Support;

use Illuminate\Database\Eloquent\Model;

class SubjectLabelResolver
{
    public function __construct(
        private readonly KnownMorphTypes $knownTypes,
    ) {}

    public function resolve(Model $model): ?string
    {
        $type = $this->knownTypes->typeForModel($model);

        if ($type === null) {
            return (string) $model->getKey();
        }

        $parts = [];

        foreach ($this->knownTypes->labelFields($type) as $field) {
            $value = data_get($model, $field);

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $label = trim(implode(' ', $parts));

        if ($label !== '') {
            return $label;
        }

        return (string) $model->getKey();
    }
}
