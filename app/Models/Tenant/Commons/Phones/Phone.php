<?php

namespace App\Models\Tenant\Commons\Phones;

use App\Enums\ModuleType;
use App\Enums\PhoneLabel;
use App\Models\Tenant\Commons\Concerns\CommonsConfigurable;
use App\Support\Search\SearchSync;
use App\Traits\HasWorkflows;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $phoneable_type
 * @property string $phoneable_id
 * @property string $phone
 * @property string $country_code
 * @property string|null $extension
 * @property PhoneLabel|null $label
 * @property bool $is_primary
 */
class Phone extends Model implements CommonsConfigurable
{
    use HasFactory, HasUlids, HasWorkflows;

    public const array PHONEABLE_TYPES = [
        ModuleType::LEADS,
        ModuleType::CONTACTS,
        ModuleType::COMPANIES,
        ModuleType::PROPERTIES,
        ModuleType::PROJECTS,
        ModuleType::VENDOR_COMPANIES,
    ];

    protected $fillable = [
        'phoneable_type',
        'phoneable_id',
        'phone',
        'country_code',
        'extension',
        'label',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'label' => PhoneLabel::class,
        ];
    }

    public function getMutationConfig(): array
    {
        return [
            'type_column' => 'phoneable_type',
            'id_column' => 'phoneable_id',
            'module' => 'phone',
            'fields' => ['phone', 'country_code', 'extension', 'label', 'is_primary'],
        ];
    }

    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Define explicitly which relationships should be exposed to the Workflow Builder.
     */
    public static function getExposedRelations(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        $reindex = static function (self $phone): void {
            $phone->loadMissing('phoneable');

            SearchSync::afterCommit($phone->phoneable);
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
