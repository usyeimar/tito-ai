<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Priority\Priority;
use App\Models\Tenant\Metadata\Source\Source;
use App\Models\Tenant\Metadata\Status\Status;
use App\Models\Tenant\Metadata\Type\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasMetadata
{
    use HasTags;

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function scopeWhereStatus(Builder $query, ?string $ids): Builder
    {
        return $this->whereMetadataIdList($query, 'status_id', $ids);
    }

    public function scopeWhereSource(Builder $query, ?string $ids): Builder
    {
        return $this->whereMetadataIdList($query, 'source_id', $ids);
    }

    public function scopeWherePriority(Builder $query, ?string $ids): Builder
    {
        return $this->whereMetadataIdList($query, 'priority_id', $ids);
    }

    public function scopeWhereType(Builder $query, ?string $ids): Builder
    {
        return $this->whereMetadataIdList($query, 'type_id', $ids);
    }

    protected function whereMetadataIdList(Builder $query, string $column, ?string $ids): Builder
    {
        if (! $ids) {
            return $query;
        }

        $values = array_filter(array_map('trim', explode(',', $ids)));

        if (! $values) {
            return $query;
        }

        return $query->whereIn($column, $values);
    }
}
