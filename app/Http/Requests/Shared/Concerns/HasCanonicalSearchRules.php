<?php

declare(strict_types=1);

namespace App\Http\Requests\Shared\Concerns;

trait HasCanonicalSearchRules
{
    /**
     * @return array<string, mixed>
     */
    protected function canonicalSearchRules(int $maxPerPage = 100): array
    {
        return [
            'q' => ['nullable', 'string', 'max:256'],
            'filter' => ['nullable', 'array'],
            'sort' => ['nullable', 'string', 'max:1024'],
            'include' => ['nullable', 'string', 'max:1024'],
            'page' => ['nullable', 'array'],
            'page.number' => ['nullable', 'integer', 'min:1'],
            'page.size' => ['nullable', 'integer', 'min:1', 'max:'.$maxPerPage],
        ];
    }
}
