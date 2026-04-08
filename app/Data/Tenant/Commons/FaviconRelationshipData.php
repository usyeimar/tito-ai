<?php

namespace App\Data\Tenant\Commons;

use App\Models\Tenant\Commons\EntityFavicon;
use Illuminate\Http\Resources\MissingValue;

final class FaviconRelationshipData
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
    ) {}

    public static function optional(EntityFavicon|MissingValue|null $favicon): ?array
    {
        if ($favicon === null || $favicon instanceof MissingValue) {
            return null;
        }

        return [
            'id' => $favicon->id,
            'url' => route('tenant.entity-favicon.show', [
                'tenant' => tenant('slug'),
                'entityFavicon' => $favicon->id,
            ]),
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
        ];
    }
}
