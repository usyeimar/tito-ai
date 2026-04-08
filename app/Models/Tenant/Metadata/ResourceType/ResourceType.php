<?php

namespace App\Models\Tenant\Metadata\ResourceType;

use App\Enums\ModuleType;
use App\Enums\RateType;
use App\Models\Tenant\Commons\Concerns\HasProfilePicture;
use App\Support\Search\TextFilterTokens;
use App\Traits\HasObjectsMetadata;
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
 * @property string|null $suggested_quantity
 * @property string|null $minimum_quantity
 * @property string|null $maximum_quantity
 * @property string|null $suggested_rate
 * @property RateType|null $rate_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ResourceType extends Model
{
    use HasFactory, HasObjectsMetadata, HasProfilePicture, HasSlug, HasUlids;

    protected $table = 'metadata_resource_types';

    public const array MODULE_TYPES = [
        ModuleType::VEHICLES,
        ModuleType::EQUIPMENT,
        ModuleType::MATERIALS,
        ModuleType::USERS,
    ];

    protected $fillable = [
        'name',
        'description',
        'module_type',
        'is_active',
        'position',
        'suggested_quantity',
        'minimum_quantity',
        'maximum_quantity',
        'suggested_rate',
        'rate_type',
    ];

    protected function casts(): array
    {
        return [
            'module_type' => ModuleType::class,
            'is_active' => 'boolean',
            'position' => 'integer',
            'suggested_quantity' => 'decimal:4',
            'minimum_quantity' => 'decimal:4',
            'maximum_quantity' => 'decimal:4',
            'suggested_rate' => 'decimal:4',
            'rate_type' => RateType::class,
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
