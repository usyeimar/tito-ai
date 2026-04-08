<?php

namespace App\Models\Central\Tenancy;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\ResourceSyncing\TenantMorphPivot;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasUlids;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'data',
        'logotype_path',
        'logomark_path',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'data',
            'logotype_path',
            'logomark_path',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $tenant): void {
            if ($tenant->slug !== null) {
                $tenant->slug = Str::slug($tenant->slug);
            }

            if (! $tenant->slug) {
                $base = data_get($tenant->data, 'name', 'tenant');
                $baseSlug = Str::slug((string) $base) ?: 'tenant';
                $tenant->slug = static::makeUniqueSlug($baseSlug, $tenant);

                return;
            }

            $tenant->slug = static::makeUniqueSlug($tenant->slug, $tenant);
        });
    }

    protected static function makeUniqueSlug(string $slug, self $tenant): string
    {
        $candidate = $slug;
        $suffix = 2;

        while (static::query()
            ->where('slug', $candidate)
            ->when($tenant->exists, fn ($q) => $q->whereKeyNot($tenant->getKey()))
            ->exists()
        ) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            CentralUser::class,
            'tenant_resources',
            'tenant_resources',
            'tenant_id',
            'resource_global_id',
            'id',
            'global_id',
        )->using(TenantMorphPivot::class);
    }

    /**
     * Get the Scout prefix for the tenant.
     */
    public function getScoutPrefixAttribute(): string
    {
        $prefix = config('scout.prefix') ?: '';
        $tenantId = (string) $this->id;

        if (str_ends_with($prefix, $tenantId.'_')) {
            return $prefix;
        }

        return $prefix.$tenantId.'_';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getKey(),
            'name' => $this->name,
            'slug' => $this->slug,
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->slug,
            ]))),
        ];
    }
}
