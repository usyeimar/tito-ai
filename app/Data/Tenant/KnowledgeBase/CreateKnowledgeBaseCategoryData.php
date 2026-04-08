<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class CreateKnowledgeBaseCategoryData extends Data
{
    public function __construct(
        #[Rule('required|exists:knowledge_bases,id')]
        public int $knowledge_base_id,
        #[Rule('nullable|exists:knowledge_base_categories,id')]
        public ?int $parent_id,
        #[Rule('required|string|max:255')]
        public string $name,
        #[Rule('integer')]
        public int $display_order,
    ) {}
}
