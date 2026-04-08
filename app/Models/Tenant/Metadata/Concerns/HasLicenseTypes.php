<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Enums\LicenseStatus;
use App\Models\Tenant\Metadata\LicenseType\Licenseable;
use App\Models\Tenant\Metadata\LicenseType\LicenseType;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasLicenseTypes
{
    public function licenseTypes(): MorphToMany
    {
        return $this->morphToMany(LicenseType::class, 'licenseable', 'metadata_licenseables')
            ->using(Licenseable::class)
            ->withPivot('license', 'issued_at', 'expires_at', 'issuing_authority', 'status', 'notes')
            ->withTimestamps();
    }

    /**
     * @param  array<int, array{license_type_id: string, license?: ?string, issued_at?: ?string, expires_at?: ?string, issuing_authority?: ?string, status?: string, notes?: ?string}>  $items
     */
    public function syncLicenseTypes(array $items): void
    {
        $this->licenseTypes()->sync($this->licensePayload($items));
    }

    /**
     * @param  array<int, array{license_type_id: string, license?: ?string, issued_at?: ?string, expires_at?: ?string, issuing_authority?: ?string, status?: string, notes?: ?string}>  $items
     */
    public function attachLicenseTypes(array $items): void
    {
        $this->licenseTypes()->syncWithoutDetaching($this->licensePayload($items));
    }

    public function detachLicenseTypes(array $licenseTypeIds): void
    {
        $this->licenseTypes()->detach($licenseTypeIds);
    }

    /**
     * @param  array<int, array{license_type_id: string, license?: ?string, issued_at?: ?string, expires_at?: ?string, issuing_authority?: ?string, status?: string, notes?: ?string}>  $items
     * @return array<string, array<string, string|null>>
     */
    private function licensePayload(array $items): array
    {
        $licenseTypeIds = array_values(array_unique(array_map(
            static fn (array $item): string => (string) $item['license_type_id'],
            $items,
        )));

        $existingPivotIds = $this->existingLicenseTypePivotIds($licenseTypeIds);
        $payload = [];

        foreach ($items as $item) {
            $licenseTypeId = (string) $item['license_type_id'];

            $payload[$licenseTypeId] = [
                'id' => $existingPivotIds[$licenseTypeId] ?? (string) Str::ulid(),
                'license' => $item['license'] ?? null,
                'issued_at' => $item['issued_at'] ?? null,
                'expires_at' => $item['expires_at'] ?? null,
                'issuing_authority' => $item['issuing_authority'] ?? null,
                'status' => $item['status'] ?? LicenseStatus::ACTIVE->value,
                'notes' => $item['notes'] ?? null,
            ];
        }

        return $payload;
    }

    /**
     * @param  array<int, string>  $licenseTypeIds
     * @return array<string, string>
     */
    private function existingLicenseTypePivotIds(array $licenseTypeIds): array
    {
        if ($licenseTypeIds === []) {
            return [];
        }

        $relation = $this->licenseTypes();

        return $relation
            ->whereIn($relation->getQualifiedRelatedKeyName(), $licenseTypeIds)
            ->withPivot('id')
            ->get()
            ->mapWithKeys(fn (LicenseType $licenseType): array => [(string) $licenseType->getKey() => (string) $licenseType->pivot->id])
            ->all();
    }
}
