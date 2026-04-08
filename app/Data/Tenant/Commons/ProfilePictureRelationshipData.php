<?php

namespace App\Data\Tenant\Commons;

use Illuminate\Http\Resources\MissingValue;

final class ProfilePictureRelationshipData
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
    ) {}

    public static function optional(mixed $picture): ?array
    {
        if ($picture === null || $picture instanceof MissingValue) {
            return null;
        }

        return [
            'id' => $picture->id,
            'url' => route('tenant.entity-profile-picture.show', [
                'tenant' => tenant('slug'),
                'entityProfilePicture' => $picture->id,
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
