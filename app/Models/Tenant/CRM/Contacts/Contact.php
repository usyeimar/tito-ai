<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Contacts;

use App\Models\Tenant\Commons\Addresses\Address;
use App\Models\Tenant\Commons\Concerns\HasAddresses;
use App\Models\Tenant\Commons\Concerns\HasEmails;
use App\Models\Tenant\Commons\Concerns\HasFiles;
use App\Models\Tenant\Commons\Concerns\HasPhones;
use App\Models\Tenant\Commons\Concerns\HasProfilePicture;
use App\Models\Tenant\Commons\Emails\Email;
use App\Models\Tenant\Commons\EntityProfilePicture;
use App\Models\Tenant\Commons\Phones\Phone;
use App\Models\Tenant\CRM\Companies\Company;
use App\Models\Tenant\Metadata\Category\Category;
use App\Models\Tenant\Metadata\Concerns\HasCategory;
use App\Models\Tenant\Metadata\Concerns\HasIndustries;
use App\Models\Tenant\Metadata\Concerns\HasLicenseTypes;
use App\Models\Tenant\Metadata\Concerns\HasSource;
use App\Models\Tenant\Metadata\Concerns\HasTags;
use App\Models\Tenant\Metadata\Industry\Industry;
use App\Models\Tenant\Metadata\LicenseType\LicenseType;
use App\Models\Tenant\Metadata\Source\Source;
use App\Models\Tenant\Metadata\Tag\Tag;
use App\Support\Search\SearchSync;
use App\Support\Search\TextFilterTokens;
use Carbon\Carbon;
use Database\Factories\Tenant\CRM\Contacts\ContactFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $display_name
 * @property string|null $category_id
 * @property string|null $company_id
 * @property string|null $source_id
 * @property array<string, mixed>|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Email|null $primaryEmail
 * @property-read Phone|null $primaryPhone
 * @property-read Address|null $primaryAddress
 * @property-read EntityProfilePicture|null $profilePicture
 * @property-read Category|null $category
 * @property-read Company|null $company
 * @property-read Source|null $source
 * @property-read Collection<int, Industry> $industries
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, LicenseType> $licenseTypes
 * @property-read Collection<int, Email> $emails
 * @property-read Collection<int, Phone> $phones
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, ContactAssignment> $assignments
 */
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasAddresses,
        HasCategory,
        HasEmails,
        HasFactory,
        HasFiles,
        HasLicenseTypes,
        HasPhones,
        HasProfilePicture,
        HasSource,
        HasTags,
        HasUlids,
        Searchable,
        SoftDeletes;

    use HasIndustries {
        attachIndustries as private attachIndustriesFromTrait;
        detachIndustries as private detachIndustriesFromTrait;
        syncIndustries as private syncIndustriesFromTrait;
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'category_id',
        'company_id',
        'source_id',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    /** @return HasMany<ContactAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ContactAssignment::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function resolvedDisplayName(): ?string
    {
        $explicit = trim((string) $this->display_name);

        if ($explicit !== '') {
            return $explicit;
        }

        $derived = trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));

        return $derived !== '' ? $derived : null;
    }

    /** @param array<int, string> $industryIds */
    public function attachIndustries(array $industryIds): void
    {
        $this->attachIndustriesFromTrait($industryIds);
        $this->fireModelEvent('industriesChanged', false);
    }

    /** @param array<int, string> $industryIds */
    public function detachIndustries(array $industryIds): void
    {
        $this->detachIndustriesFromTrait($industryIds);
        $this->fireModelEvent('industriesChanged', false);
    }

    /** @param array<int, string> $industryIds */
    public function syncIndustries(array $industryIds): void
    {
        $this->syncIndustriesFromTrait($industryIds);
        $this->fireModelEvent('industriesChanged', false);
    }

    /**
     * Define explicitly which relationships should be exposed to the Workflow Builder.
     *
     * @return array<string, class-string>
     */
    public static function getExposedRelations(): array
    {
        return [
            'category' => Category::class,
            'company' => Company::class,
            'source' => Source::class,
            'industries' => Industry::class,
            'assignments' => ContactAssignment::class,
        ];
    }

    /** @return array<string, string> */
    public static function getObjectMetadata(): array
    {
        return [
            'name_singular' => 'contact',
            'name_plural' => 'contacts',
            'label_singular' => 'Contact',
            'label_plural' => 'Contacts',
            'icon' => 'IconUser',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['emails', 'phones', 'assignments', 'industries', 'tags', 'licenseTypes', 'source', 'category', 'company']);

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

        $assignmentProjectIds = $this->assignments
            ->where('assignable_type', 'project')
            ->pluck('assignable_id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $assignmentPropertyIds = $this->assignments
            ->where('assignable_type', 'property')
            ->pluck('assignable_id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $assignmentVendorCompanyIds = $this->assignments
            ->where('assignable_type', 'vendor_company')
            ->pluck('assignable_id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        return [
            'id' => (string) $this->getKey(),
            'category_id' => $this->category_id ? (string) $this->category_id : null,
            'category_slug' => $this->category?->slug,
            'category_name' => $this->category?->name,
            'company_id' => $this->company_id ? (string) $this->company_id : null,
            'company_slug' => $this->company?->normalized_name,
            'company_name' => $this->company?->name,
            'source_id' => $this->source_id ? (string) $this->source_id : null,
            'source_slug' => $this->source?->slug,
            'source_name' => $this->source?->name,
            'industry_ids' => $industryIds,
            'industry_slugs' => $industrySlugs,
            'tag_ids' => $tagIds,
            'tag_slugs' => $tagSlugs,
            'license_type_ids' => $licenseTypeIds,
            'license_type_slugs' => $licenseTypeSlugs,
            'project_assignment_ids' => $assignmentProjectIds,
            'property_assignment_ids' => $assignmentPropertyIds,
            'vendor_company_assignment_ids' => $assignmentVendorCompanyIds,
            'first_name' => $this->first_name,
            'first_name_empty' => TextFilterTokens::isEmpty($this->first_name),
            'first_name_prefixes' => TextFilterTokens::prefixes($this->first_name),
            'first_name_suffixes' => TextFilterTokens::suffixes($this->first_name),
            'first_name_ngrams' => TextFilterTokens::ngrams($this->first_name),
            'last_name' => $this->last_name,
            'last_name_empty' => TextFilterTokens::isEmpty($this->last_name),
            'last_name_prefixes' => TextFilterTokens::prefixes($this->last_name),
            'last_name_suffixes' => TextFilterTokens::suffixes($this->last_name),
            'last_name_ngrams' => TextFilterTokens::ngrams($this->last_name),
            'display_name' => $this->display_name,
            'display_name_empty' => TextFilterTokens::isEmpty($this->display_name),
            'display_name_prefixes' => TextFilterTokens::prefixes($this->display_name),
            'display_name_suffixes' => TextFilterTokens::suffixes($this->display_name),
            'display_name_ngrams' => TextFilterTokens::ngrams($this->display_name),
            'created_at' => $this->created_at?->valueOf(),
            'updated_at' => $this->updated_at?->valueOf(),
            'emails' => $emails,
            'phones' => $phones,
            'search_blob' => trim(implode(' ', array_filter([
                $this->first_name,
                $this->last_name,
                $this->display_name,
                ...$emails,
                ...$phones,
            ]))),
        ];
    }

    /** @param Builder<self> $query
     * @return Builder<self> */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['emails', 'phones', 'assignments', 'industries', 'tags', 'licenseTypes', 'source', 'category', 'company']);
    }

    protected static function booted(): void
    {
        $reindexAssignedRecords = static function (self $contact): void {
            $contact->loadMissing(['assignments.assignable', 'company']);

            SearchSync::afterCommit([
                ...$contact->assignments->pluck('assignable')->all(),
                $contact->company,
            ]);
        };

        static::registerModelEvent('industriesChanged', static function (self $contact): void {
            SearchSync::afterCommit($contact);
        });

        static::saved($reindexAssignedRecords);
        static::deleted($reindexAssignedRecords);
        static::restored($reindexAssignedRecords);
        static::forceDeleted($reindexAssignedRecords);
    }

    /**
     * @return array<int, string>
     */
    public static function getTemplateVariables(): array
    {
        return [
            'first_name',
            'last_name',
            'display_name',
        ];
    }
}
