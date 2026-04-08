<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class UpdateKnowledgeBaseCategoryData extends Data
{
    public function __construct(
        #[Rule('sometimes|required|string|max:255')]
        public ?string $name,
        #[Rule('sometimes|required|integer')]
        public ?int $display_order,
    ) {}
}
