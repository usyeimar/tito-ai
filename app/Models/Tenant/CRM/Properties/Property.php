<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Properties;

use App\Models\Tenant\Commons\Addresses\Address;
use App\Models\Tenant\Commons\Concerns\HasAddresses;
use App\Models\Tenant\Commons\Concerns\HasEmails;
use App\Models\Tenant\Commons\Concerns\HasFiles;
use App\Models\Tenant\Commons\Concerns\HasPhones;
use App\Models\Tenant\Commons\Emails\Email;
use App\Models\Tenant\Commons\Files\File;
use App\Models\Tenant\Commons\Phones\Phone;
use App\Models\Tenant\CRM\Companies\Company;
use App\Models\Tenant\CRM\Contacts\ContactAssignment;
use App\Models\Tenant\Metadata\Concerns\HasLicenseTypes;
use App\Models\Tenant\Metadata\Concerns\HasSource;
use App\Models\Tenant\Metadata\Concerns\HasTags;
use App\Models\Tenant\Metadata\Concerns\HasType;
use App\Models\Tenant\Metadata\LicenseType\LicenseType;
use App\Models\Tenant\Metadata\Source\Source;
use App\Models\Tenant\Metadata\Tag\Tag;
use App\Models\Tenant\Metadata\Type\Type;
use App\Support\Search\SearchSync;
use App\Support\Search\TextFilterTokens;
use Carbon\CarbonImmutable;
use Database\Factories\Tenant\CRM\Properties\PropertyFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string|null $company_id
 * @property string $name
 * @property string $normalized_name
 * @property string|null $legal_name
 * @property string|null $description
 * @property string|null $notes
 * @property bool $is_active
 * @property string|null $type_id
 * @property string|null $source_id
 * @property string|null $parcel_id
 * @property string|null $state_registration_number
 * @property string|null $state_registration_state
 * @property string|null $state_registration_status
 * @property Carbon|null $state_registration_recorded_at
 * @property Carbon|null $state_registration_expires_at
 * @property string|null $state_registration_notes
 * @property string|null $property_address_id
 * @property string|null $company_address_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company|null $company
 * @property-read Type|null $type
 * @property-read Source|null $source
 * @property-read Address|null $propertyAddress
 * @property-read Address|null $companyAddress
 * @property-read Email|null $primaryEmail
 * @property-read Phone|null $primaryPhone
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, Email> $emails
 * @property-read Collection<int, Phone> $phones
 * @property-read Collection<int, File> $files
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, LicenseType> $licenseTypes
 * @property-read Collection<int, ContactAssignment> $contactAssignments
 */
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasAddresses,
        HasEmails,
        HasFactory,
        HasFiles,
        HasLicenseTypes,
        HasPhones,
        HasSource,
        HasTags,
        HasType,
        HasUlids,
        Searchable,
        SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'normalized_name',
        'legal_name',
        'description',
        'notes',
        'is_active',
        'type_id',
        'source_id',
        'parcel_id',
        'state_registration_number',
        'state_registration_state',
        'state_registration_status',
        'state_registration_recorded_at',
        'state_registration_expires_at',
        'state_registration_notes',
        'property_address_id',
        'company_address_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'state_registration_recorded_at' => 'datetime',
            'state_registration_expires_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function propertyAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'property_address_id');
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function companyAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'company_address_id');
    }

    /**
     * @return MorphMany<ContactAssignment, $this>
     */
    public function contactAssignments(): MorphMany
    {
        return $this->morphMany(ContactAssignment::class, 'assignable');
    }

    /**
     * @return MorphMany<ContactAssignment, $this>
     */
    public function activeContactAssignments(): MorphMany
    {
        return $this->contactAssignments()->whereNull('deleted_at');
    }

    public function resolvedDisplayName(): string
    {
        return (string) $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing([
            'company',
            'type',
            'source',
            'tags',
            'licenseTypes',
            'emails',
            'phones',
            'addresses',
            'propertyAddress',
            'companyAddress',
        ]);

        $emails = $this->emails
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        $phones = $this->phones
            ->pluck('phone')
            ->filter()
            ->values()
            ->all();

        $addresses = $this->addresses
            ->map(static fn (Address $address): string => trim(implode(' ', array_filter([
                $address->address_line,
                $address->address_line_2,
                $address->city,
                $address->state_region,
                $address->postal_code,
                $address->country_code,
            ]))))
            ->filter()
            ->values()
            ->all();

        $tagIds = $this->tags
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $tagSlugs = $this->tags
            ->pluck('slug')
            ->filter()
            ->map(static fn (mixed $slug): string => (string) $slug)
            ->sort()
            ->values()
            ->all();

        $licenseTypeIds = $this->licenseTypes
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $licenseTypeSlugs = $this->licenseTypes
            ->pluck('slug')
            ->filter()
            ->map(static fn (mixed $slug): string => (string) $slug)
            ->sort()
            ->values()
            ->all();

        return [
            'id' => (string) $this->getKey(),
            'company_id' => $this->company_id ? (string) $this->company_id : null,
            'company_slug' => $this->company?->normalized_name,
            'company_name' => $this->company?->name,
            'type_id' => $this->type_id ? (string) $this->type_id : null,
            'type_slug' => $this->type?->slug,
            'type_name' => $this->type?->name,
            'source_id' => $this->source_id ? (string) $this->source_id : null,
            'source_slug' => $this->source?->slug,
            'source_name' => $this->source?->name,
            'tag_ids' => $tagIds,
            'tag_slugs' => $tagSlugs,
            'license_type_ids' => $licenseTypeIds,
            'license_type_slugs' => $licenseTypeSlugs,
            'name' => $this->name,
            'name_empty' => TextFilterTokens::isEmpty($this->name),
            'name_prefixes' => TextFilterTokens::prefixes($this->name),
            'name_suffixes' => TextFilterTokens::suffixes($this->name),
            'name_ngrams' => TextFilterTokens::ngrams($this->name),
            'legal_name' => $this->legal_name,
            'legal_name_empty' => TextFilterTokens::isEmpty($this->legal_name),
            'legal_name_prefixes' => TextFilterTokens::prefixes($this->legal_name),
            'legal_name_suffixes' => TextFilterTokens::suffixes($this->legal_name),
            'legal_name_ngrams' => TextFilterTokens::ngrams($this->legal_name),
            'description' => $this->description,
            'description_empty' => TextFilterTokens::isEmpty($this->description),
            'description_prefixes' => TextFilterTokens::prefixes($this->description),
            'description_suffixes' => TextFilterTokens::suffixes($this->description),
            'description_ngrams' => TextFilterTokens::ngrams($this->description),
            'parcel_id' => $this->parcel_id,
            'parcel_id_empty' => TextFilterTokens::isEmpty($this->parcel_id),
            'parcel_id_prefixes' => TextFilterTokens::prefixes($this->parcel_id),
            'parcel_id_suffixes' => TextFilterTokens::suffixes($this->parcel_id),
            'parcel_id_ngrams' => TextFilterTokens::ngrams($this->parcel_id),
            'notes' => $this->notes,
            'notes_empty' => TextFilterTokens::isEmpty($this->notes),
            'notes_prefixes' => TextFilterTokens::prefixes($this->notes),
            'notes_suffixes' => TextFilterTokens::suffixes($this->notes),
            'notes_ngrams' => TextFilterTokens::ngrams($this->notes),
            'is_active' => (bool) $this->is_active,
            'state_registration_number' => $this->state_registration_number,
            'state_registration_state' => $this->state_registration_state,
            'state_registration_status' => $this->state_registration_status,
            'state_registration_recorded_at' => $this->timestampValue($this->state_registration_recorded_at),
            'state_registration_expires_at' => $this->dayStartTimestampValue($this->state_registration_expires_at),
            'created_at' => $this->timestampValue($this->created_at),
            'updated_at' => $this->timestampValue($this->updated_at),
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->legal_name,
                $this->description,
                $this->parcel_id,
                $this->notes,
                $this->state_registration_number,
                $this->state_registration_state,
                $this->state_registration_status,
                ...$emails,
                ...$phones,
                ...$addresses,
            ]))),
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'company',
            'type',
            'source',
            'tags',
            'licenseTypes',
            'emails',
            'phones',
            'addresses',
            'propertyAddress',
            'companyAddress',
        ]);
    }

    protected static function booted(): void
    {
        static::saved(static function (self $property): void {
            SearchSync::afterCommit($property);
        });
        static::deleted(static function (self $property): void {
            SearchSync::afterCommit($property);
        });
        static::restored(static function (self $property): void {
            SearchSync::afterCommit($property);
        });
        static::forceDeleted(static function (self $property): void {
            DB::afterCommit(static fn () => $property->unsearchable());
        });
    }

    private function timestampValue(mixed $value): ?int
    {
        if (! $value instanceof DateTimeInterface) {
            return null;
        }

        return (int) CarbonImmutable::instance($value)->valueOf();
    }

    private function dayStartTimestampValue(mixed $value): ?int
    {
        if (! $value instanceof DateTimeInterface) {
            return null;
        }

        return (int) CarbonImmutable::instance($value)->startOfDay()->valueOf();
    }
}
