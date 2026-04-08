<?php

namespace App\Services\Tenant\Commons\Files;

final class TenantAssetPathBuilder
{
    public function defaultDisk(): string
    {
        return (string) config('filesystems.default', 's3');
    }

    public function buildPath(
        string $module,
        string|array|null $ids = null,
        string|array|null $type = null,
        string|array|null $file = null,
    ): string {
        $segments = [
            ...$this->normalizeSegments($module, 'module', true),
            ...$this->normalizeSegments($ids),
            ...$this->normalizeSegments($type),
            ...$this->normalizeSegments($file),
        ];

        return implode('/', $segments);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeSegments(string|array|null $value, string $fallback = 'unknown', bool $required = false): array
    {
        if ($value === null || $value === []) {
            return $required ? [$fallback] : [];
        }

        $rawValues = is_array($value) ? $value : [$value];
        $segments = [];

        foreach ($rawValues as $rawValue) {
            $parts = explode('/', trim((string) $rawValue, '/'));

            foreach ($parts as $part) {
                $sanitized = $this->sanitizeSegment($part, '');

                if ($sanitized !== '') {
                    $segments[] = $sanitized;
                }
            }
        }

        if ($segments === [] && $required) {
            return [$fallback];
        }

        return $segments;
    }

    private function sanitizeSegment(string $value, string $fallback = 'unknown'): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', trim($value)) ?? '';

        if ($sanitized === '' || $sanitized === '.' || $sanitized === '..') {
            return $fallback;
        }

        return $sanitized;
    }
}
