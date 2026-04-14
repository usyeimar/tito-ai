<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Concerns\WithDeprecatedCollectionMethod;
use Spatie\LaravelData\Data;

class KnowledgeBaseData extends Data
{
    use WithDeprecatedCollectionMethod;

    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $is_public,
    ) {}
}
