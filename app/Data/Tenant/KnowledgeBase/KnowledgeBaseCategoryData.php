<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Concerns\WithDeprecatedCollectionMethod;
use Spatie\LaravelData\Data;

class KnowledgeBaseCategoryData extends Data
{
    use WithDeprecatedCollectionMethod;

    public function __construct(
        public string $id,
        public string $knowledge_base_id,
        public ?string $parent_id,
        public string $name,
        public string $slug,
        public int $display_order,
    ) {}
}
