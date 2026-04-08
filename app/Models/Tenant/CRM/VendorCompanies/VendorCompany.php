<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\VendorCompanies;

use App\Models\Tenant\Commons\Addresses\Address;
use App\Models\Tenant\Commons\Concerns\HasAddresses;
use App\Models\Tenant\Commons\Concerns\HasEmails;
use App\Models\Tenant\Commons\Concerns\HasFiles;
use App\Models\Tenant\Commons\Concerns\HasPhones;
use App\Models\Tenant\Commons\Concerns\HasProfilePicture;
use App\Models\Tenant\Commons\Emails\Email;
use App\Models\Tenant\Commons\EntityProfilePicture;
use App\Models\Tenant\Commons\Phones\Phone;
use App\Models\Tenant\CRM\Contacts\ContactAssignment;
use App\Models\Tenant\Metadata\Category\Category;
use App\Models\Tenant\Metadata\Concerns\HasCategory;
use App\Models\Tenant\Metadata\Concerns\HasIndustries;
use App\Models\Tenant\Metadata\Concerns\HasLicenseTypes;
use App\Models\Tenant\Metadata\Concerns\HasSource;
use App\Models\Tenant\Metadata\Concerns\HasTags;
use App\Models\Tenant\Metadata\Concerns\HasType;
use App\Models\Tenant\Metadata\Industry\Industry;
use App\Models\Tenant\Metadata\LicenseType\LicenseType;
use App\Models\Tenant\Metadata\Source\Source;
use App\Models\Tenant\Metadata\Tag\Tag;
use App\Models\Tenant\Metadata\Type\Type;
use App\Support\Search\SearchSync;
use App\Support\Search\TextFilterTokens;
use App\Traits\HasWorkflows;
use Database\Factories\Tenant\CRM\VendorCompanies\VendorCompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string|null $name
 * @property string|null $normalized_name
 * @property string|null $legal_name
 * @property string|null $external_ref
 * @property string|null $category_id
 * @property string|null $type_id
 * @property string|null $source_id
 * @property string|null $website
 * @property string|null $domain
 * @property array<string, mixed>|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Category|null $category
 * @property-read Type|null $type
 * @property-read Source|null $source
 * @property-read EntityProfilePicture|null $profilePicture
 * @property-read Email|null $primaryEmail
 * @property-read Phone|null $primaryPhone
 * @property-read Address|null $primaryAddress
 * @property-read EloquentCollection<int, Email> $emails
 * @property-read EloquentCollection<int, Phone> $phones
 * @property-read EloquentCollection<int, Address> $addresses
 * @property-read EloquentCollection<int, Industry> $industries
 * @property-read EloquentCollection<int, Tag> $tags
 * @property-read EloquentCollection<int, LicenseType> $licenseTypes
 * @property-read EloquentCollection<int, ContactAssignment> $contactAssignments
 *
 * @use HasFactory<VendorCompanyFactory>
 */
class VendorCompany extends Model
{
    /** @var list<string> */
    private const SEARCH_RELATIONS = ['emails', 'phones', 'addresses', 'category', 'type', 'source', 'industries', 'tags', 'licenseTypes'];

    /** @use HasFactory<VendorCompanyFactory> */
    use HasAddresses,
        HasCategory,
        HasEmails,
        HasFactory,
        HasFiles,
        HasIndustries,
        HasLicenseTypes,
        HasPhones,
        HasProfilePicture,
        HasSource,
        HasTags,
        HasType,
        HasUlids,
        HasWorkflows,
        Searchable,
        SoftDeletes;

    protected $fillable = [
        'name',
        'normalized_name',
        'legal_name',
        'external_ref',
        'category_id',
        'type_id',
        'source_id',
        'website',
        'domain',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    /**
     * @return MorphMany<ContactAssignment, $this>
     */
    public function contactAssignments(): MorphMany
    {
        return $this->morphMany(ContactAssignment::class, 'assignable');
    }

    /**
     * @return array<string, class-string<Model>>
     */
    public static function getExposedRelations(): array
    {
        return [
            'category' => Category::class,
            'type' => Type::class,
            'source' => Source::class,
            'industries' => Industry::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(self::SEARCH_RELATIONS);

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

        $industryIds = $this->industries
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $industrySlugs = $this->industries
            ->pluck('slug')
            ->filter()
            ->map(static fn (mixed $slug): string => (string) $slug)
            ->sort()
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
            'category_id' => $this->category_id ? (string) $this->category_id : null,
            'category_slug' => $this->category?->slug,
            'category_name' => $this->category?->name,
            'type_id' => $this->type_id ? (string) $this->type_id : null,
            'type_slug' => $this->type?->slug,
            'type_name' => $this->type?->name,
            'source_id' => $this->source_id ? (string) $this->source_id : null,
            'source_slug' => $this->source?->slug,
            'source_name' => $this->source?->name,
            'industry_ids' => $industryIds,
            'industry_slugs' => $industrySlugs,
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
            'external_ref' => $this->external_ref,
            'external_ref_empty' => TextFilterTokens::isEmpty($this->external_ref),
            'external_ref_prefixes' => TextFilterTokens::prefixes($this->external_ref),
            'external_ref_suffixes' => TextFilterTokens::suffixes($this->external_ref),
            'external_ref_ngrams' => TextFilterTokens::ngrams($this->external_ref),
            'domain' => $this->domain,
            'domain_empty' => TextFilterTokens::isEmpty($this->domain),
            'domain_prefixes' => TextFilterTokens::prefixes($this->domain),
            'domain_suffixes' => TextFilterTokens::suffixes($this->domain),
            'domain_ngrams' => TextFilterTokens::ngrams($this->domain),
            'website' => $this->website,
            'website_empty' => TextFilterTokens::isEmpty($this->website),
            'website_prefixes' => TextFilterTokens::prefixes($this->website),
            'website_suffixes' => TextFilterTokens::suffixes($this->website),
            'website_ngrams' => TextFilterTokens::ngrams($this->website),
            'created_at' => $this->created_at?->valueOf(),
            'updated_at' => $this->updated_at?->valueOf(),
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->legal_name,
                $this->external_ref,
                $this->domain,
                $this->website,
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
        return $query->with(self::SEARCH_RELATIONS);
    }

    protected static function booted(): void
    {
        $reindexAssignedContacts = static function (self $company): void {
            $company->loadMissing('contactAssignments.contact');

            SearchSync::afterCommit($company->contactAssignments->pluck('contact')->all());
        };

        static::saved($reindexAssignedContacts);
        static::deleted($reindexAssignedContacts);
        static::restored($reindexAssignedContacts);
        static::forceDeleted($reindexAssignedContacts);
    }

    /**
     * @return array<int, string>
     */
    public static function getTemplateVariables(): array
    {
        return [
            'name',
            'legal_name',
            'external_ref',
            'domain',
            'website',
        ];
    }
}
