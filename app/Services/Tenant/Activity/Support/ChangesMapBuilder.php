<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity\Support;

class ChangesMapBuilder
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<int, string>|null  $allowedFields
     * @return array<string, array{from:mixed,to:mixed}>
     */
    public function build(array $before, array $after, ?array $allowedFields = null): array
    {
        $fields = $allowedFields ?? array_values(array_unique([...array_keys($before), ...array_keys($after)]));
        $changes = [];

        foreach ($fields as $field) {
            $from = $before[$field] ?? null;
            $to = $after[$field] ?? null;

            if ($this->normalize($from) === $this->normalize($to)) {
                continue;
            }

            $changes[$field] = [
                'from' => $from,
                'to' => $to,
            ];
        }

        return $changes;
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }
}
