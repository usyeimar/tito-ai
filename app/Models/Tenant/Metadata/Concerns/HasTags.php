<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Tag\Tag;
use App\Models\Tenant\Metadata\Tag\Taggable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'metadata_taggables')
            ->using(Taggable::class)
            ->withTimestamps();
    }

    public function attachTags(array $tagIds): void
    {
        $this->tags()->syncWithoutDetaching($this->withPivotIds($tagIds, $this->existingTagPivotIds($tagIds)));
    }

    public function detachTags(array $tagIds): void
    {
        $this->tags()->detach($tagIds);
    }

    public function syncTags(array $tagIds): void
    {
        $this->tags()->sync($this->withPivotIds($tagIds, $this->existingTagPivotIds($tagIds)));
    }

    /**
     * @param  array<int, string>  $tagIds
     * @return array<string, string>
     */
    private function existingTagPivotIds(array $tagIds): array
    {
        if ($tagIds === []) {
            return [];
        }

        $relation = $this->tags();

        return $relation
            ->whereIn($relation->getQualifiedRelatedKeyName(), $tagIds)
            ->withPivot('id')
            ->get()
            ->mapWithKeys(fn (Tag $tag): array => [(string) $tag->getKey() => (string) $tag->pivot->id])
            ->all();
    }

    /**
     * @param  array<int, string>  $tagIds
     * @param  array<string, string>  $existingPivotIds
     * @return array<string, array<string, string>>
     */
    private function withPivotIds(array $tagIds, array $existingPivotIds = []): array
    {
        $payload = [];

        foreach ($tagIds as $tagId) {
            $tagKey = (string) $tagId;

            $payload[$tagKey] = ['id' => $existingPivotIds[$tagKey] ?? (string) Str::ulid()];
        }

        return $payload;
    }
}
