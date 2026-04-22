<?php

namespace App\Models\Tenant\Metadata\Category;

use App\Enums\ModuleType;
use App\Support\Search\TextFilterTokens;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory,  HasSlug, HasUlids;

    protected $table = 'metadata_categories';

    public const array MODULE_TYPES = [
        ModuleType::COMPANIES,
        ModuleType::CONTACTS,
        ModuleType::VENDOR_COMPANIES,
    ];

    protected $fillable = [
        'name',
        'description',
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
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'module_type' => $this->module_type->value,
            'is_active' => (bool) $this->is_active,
            'position' => (int) $this->position,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
