<?php

namespace App\Models\Tenant\Commons\Addresses;

use App\Enums\AddressLabel;
use App\Enums\ModuleType;
use App\Models\Tenant\Commons\Concerns\CommonsConfigurable;
use App\Support\Search\SearchSync;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $addressable_type
 * @property string $addressable_id
 * @property string $address_line
 * @property string|null $address_line_2
 * @property string|null $city
 * @property string|null $state_region
 * @property string|null $country_code
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $postal_code
 * @property AddressLabel|null $label
 * @property string|null $notes
 * @property bool $is_primary
 */
class Address extends Model implements CommonsConfigurable
{
    use HasFactory, HasUlids;

    public const array ADDRESSABLE_TYPES = [
        ModuleType::LEADS,
        ModuleType::CONTACTS,
        ModuleType::COMPANIES,
        ModuleType::PROPERTIES,
        ModuleType::PROJECTS,
        ModuleType::VENDOR_COMPANIES,
    ];

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'address_line',
        'address_line_2',
        'city',
        'state_region',
        'country_code',
        'lat',
        'lng',
        'postal_code',
        'label',
        'notes',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'is_primary' => 'boolean',
            'label' => AddressLabel::class,
        ];
    }

    public function getMutationConfig(): array
    {
        return [
            'type_column' => 'addressable_type',
            'id_column' => 'addressable_id',
            'module' => 'address',
            'fields' => ['address_line', 'address_line_2', 'city', 'state_region', 'country_code', 'lat', 'lng', 'postal_code', 'label', 'notes', 'is_primary'],
        ];
    }

    public function addressable(): MorphTo
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
        $reindex = static function (self $address): void {
            $address->loadMissing('addressable');

            SearchSync::afterCommit($address->addressable);
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
