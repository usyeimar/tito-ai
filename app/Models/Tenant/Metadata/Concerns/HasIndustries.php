<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Industry\Industriable;
use App\Models\Tenant\Metadata\Industry\Industry;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasIndustries
{
    public function industries(): MorphToMany
    {
        return $this->morphToMany(Industry::class, 'industriable', 'metadata_industriables')
            ->using(Industriable::class)
            ->withTimestamps();
    }

    public function attachIndustries(array $industryIds): void
    {
        $this->industries()->syncWithoutDetaching($this->withIndustryPivotIds($industryIds, $this->existingIndustryPivotIds($industryIds)));
    }

    public function detachIndustries(array $industryIds): void
    {
        $this->industries()->detach($industryIds);
    }

    public function syncIndustries(array $industryIds): void
    {
        $this->industries()->sync($this->withIndustryPivotIds($industryIds, $this->existingIndustryPivotIds($industryIds)));
    }

    /**
     * @param  array<int, string>  $industryIds
     * @return array<string, string>
     */
    private function existingIndustryPivotIds(array $industryIds): array
    {
        if ($industryIds === []) {
            return [];
        }

        $relation = $this->industries();

        return $relation
            ->whereIn($relation->getQualifiedRelatedKeyName(), $industryIds)
            ->withPivot('id')
            ->get()
            ->mapWithKeys(fn (Industry $industry): array => [(string) $industry->getKey() => (string) $industry->pivot->id])
            ->all();
    }

    /**
     * @param  array<int, string>  $industryIds
     * @param  array<string, string>  $existingPivotIds
     * @return array<string, array<string, string>>
     */
    private function withIndustryPivotIds(array $industryIds, array $existingPivotIds = []): array
    {
        $payload = [];

        foreach ($industryIds as $industryId) {
            $key = (string) $industryId;

            $payload[$key] = ['id' => $existingPivotIds[$key] ?? (string) Str::ulid()];
        }

        return $payload;
    }
}
