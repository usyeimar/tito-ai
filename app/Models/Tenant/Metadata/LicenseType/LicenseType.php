<?php

namespace App\Models\Tenant\Metadata\LicenseType;

use App\Enums\ModuleType;
use App\Support\Search\TextFilterTokens;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property ModuleType $module_type
 * @property bool $is_active
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Licenseable $pivot
 */
class LicenseType extends Model
{
    use HasFactory,  HasSlug, HasUlids;

    protected $table = 'metadata_license_types';

    public const array MODULE_TYPES = [
        ModuleType::CONTACTS,
        ModuleType::COMPANIES,
        ModuleType::VENDOR_COMPANIES,
    ];

    protected $fillable = [
        'name',
        'description',
        'module_type',
        'is_active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'module_type' => ModuleType::class,
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('module_type', $this->module_type))
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getKey(),
            'name' => $this->name,
            'name_empty' => TextFilterTokens::isEmpty($this->name),
            'name_prefixes' => TextFilterTokens::prefixes($this->name),
            'name_suffixes' => TextFilterTokens::suffixes($this->name),
            'name_ngrams' => TextFilterTokens::ngrams($this->name),
            'description' => $this->description,
            'module_type' => $this->module_type->value,
            'slug' => $this->slug,
            'is_active' => (bool) $this->is_active,
            'position' => $this->position,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
