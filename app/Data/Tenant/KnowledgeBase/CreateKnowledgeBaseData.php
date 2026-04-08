<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class CreateKnowledgeBaseData extends Data
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,
        #[Rule('nullable|string')]
        public ?string $description,
        #[Rule('boolean')]
        public bool $is_public,
    ) {}
}
