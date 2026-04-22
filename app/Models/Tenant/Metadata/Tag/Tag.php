<?php

namespace App\Models\Tenant\Metadata\Tag;

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
 * @property string $color Hex color (#3B82F6)
 * @property string|null $icon Google Material Icons name
 * @property ModuleType $module_type
 * @property bool $is_active
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Tag extends Model
{
    use HasFactory,  HasSlug, HasUlids;

    protected $table = 'metadata_tags';

    public const array MODULE_TYPES = [
        ModuleType::LEADS,
        ModuleType::COMPANIES,
        ModuleType::PROPERTIES,
        ModuleType::CONTACTS,
        ModuleType::PROJECTS,
        ModuleType::VENDOR_COMPANIES,
        ModuleType::EQUIPMENT,
        ModuleType::VEHICLES,
        ModuleType::MATERIALS,
        ModuleType::EMAIL_TEMPLATES,
        ModuleType::DOCUMENT_TEMPLATES,
    ];

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
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
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => (bool) $this->is_active,
            'position' => $this->position,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
