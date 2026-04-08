<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Data;

class KnowledgeBaseData extends Data
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $is_public,
    ) {}
}
