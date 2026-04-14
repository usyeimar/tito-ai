<?php

namespace App\Data\Tenant\KnowledgeBase;

use Spatie\LaravelData\Concerns\WithDeprecatedCollectionMethod;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class KnowledgeBaseDocumentData extends Data
{
    use WithDeprecatedCollectionMethod;

    public function __construct(
        public string $id,
        public string $knowledge_base_category_id,
        public string $title,
        public string $slug,
        public string $content_format,
        public string $status,
        public string $author_id,
        public ?string $published_at,
        public string|Optional $content,
        public int|Optional $version_number,
    ) {}

    public static function fromModel($model): self
    {
        $latestVersion = $model->versions()->latest('version_number')->first();
        $publishedAt = $model->published_at;

        if ($publishedAt instanceof \DateTimeInterface) {
            $publishedAt = $publishedAt->toDateTimeString();
        }

        return new self(
            id: $model->id,
            knowledge_base_category_id: $model->knowledge_base_category_id,
            title: $model->title,
            slug: $model->slug,
            content_format: $model->content_format,
            status: $model->status,
            author_id: $model->author_id,
            published_at: $publishedAt,
            content: $latestVersion?->content ?? '',
            version_number: $latestVersion?->version_number ?? 0,
        );
    }
}
