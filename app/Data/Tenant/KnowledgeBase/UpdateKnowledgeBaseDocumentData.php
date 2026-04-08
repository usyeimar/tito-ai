<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class UpdateKnowledgeBaseDocumentData extends Data
{
    public function __construct(
        #[Rule('sometimes|required|string|max:255')]
        public ?string $title,
        #[Rule('sometimes|required|string')]
        public ?string $content,
        #[Rule('string')]
        public ?string $status,
    ) {}
}
