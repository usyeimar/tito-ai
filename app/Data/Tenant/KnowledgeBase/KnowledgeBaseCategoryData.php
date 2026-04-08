<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Data;

class KnowledgeBaseCategoryData extends Data
{
    public function __construct(
        public int $id,
        public int $knowledge_base_id,
        public ?int $parent_id,
        public string $name,
        public string $slug,
        public int $display_order,
    ) {}
}
