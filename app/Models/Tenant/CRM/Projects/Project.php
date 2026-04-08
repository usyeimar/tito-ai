<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Projects;

use App\Models\Tenant\Assignments\Assignment;
use App\Models\Tenant\Assignments\Concerns\HasAssignments;
use App\Models\Tenant\Commons\Concerns\HasAddresses;
use App\Models\Tenant\Commons\Concerns\HasEmails;
use App\Models\Tenant\Commons\Concerns\HasFiles;
use App\Models\Tenant\Commons\Concerns\HasPhones;
use App\Models\Tenant\Commons\Concerns\HasProfilePicture;
use App\Models\Tenant\CRM\Companies\Company;
use App\Models\Tenant\CRM\Contacts\ContactAssignment;
use App\Models\Tenant\Metadata\Concerns\HasIndustries;
use App\Models\Tenant\Metadata\Concerns\HasLicenseTypes;
use App\Models\Tenant\Metadata\Concerns\HasStatus;
use App\Models\Tenant\Metadata\Concerns\HasTags;
use App\Models\Tenant\Metadata\Concerns\HasType;
use App\Models\Tenant\Metadata\Industry\Industry;
use App\Models\Tenant\Metadata\LicenseType\LicenseType;
use App\Models\Tenant\Metadata\Status\Status;
use App\Models\Tenant\Metadata\Type\Type;
use App\Support\Search\SearchSync;
use App\Support\Search\TextFilterTokens;
use App\Traits\HasObjectsMetadata;
use App\Traits\HasWorkflows;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Project extends Model
{
    use HasAddresses,
        HasAssignments,
        HasEmails,
        HasFactory,
        HasFiles,
        HasIndustries,
        HasLicenseTypes,
        HasObjectsMetadata,
        HasPhones,
        HasProfilePicture,
        HasStatus,
        HasTags,
        HasType,
        HasUlids,
        HasWorkflows,
        Searchable,
        SoftDeletes;

    public static function getExposedRelations(): array
    {
        return [
            'assignments' => Assignment::class,
            'primaryUserAssignment' => Assignment::class,
            'status' => Status::class,
            'type' => Type::class,
            'company' => Company::class,
            'industries' => Industry::class,
            'licenseTypes' => LicenseType::class,
        ];
    }

    public static function getObjectMetadata(): array
    {
        return [
            'name_singular' => 'project',
            'name_plural' => 'projects',
            'label_singular' => 'Project',
            'label_plural' => 'Projects',
            'icon' => 'IconLayoutGrid',
        ];
    }

    protected $fillable = [
        'name',
        'project_number',
        'normalized_name',
        'external_ref',
        'is_active',
        'company_id',
        'type_id',
        'status_id',
        'start_date',
        'end_date',
        'deadline',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'deadline' => 'date',
            'is_active' => 'boolean',
            'custom_fields' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contactAssignments(): MorphMany
    {
        return $this->morphMany(ContactAssignment::class, 'assignable');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['company', 'type', 'status', 'industries', 'tags', 'licenseTypes', 'emails', 'phones', 'addresses', 'contactAssignments']);

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
            ->map(static fn ($address): string => trim(implode(' ', array_filter([
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

        $contactIds = $this->contactAssignments
            ->pluck('contact_id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        $contactAssignmentIds = $this->contactAssignments
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->sort()
            ->values()
            ->all();

        return [
            'id' => (string) $this->getKey(),
            'company_id' => (string) $this->company_id,
            'company_slug' => $this->company?->normalized_name,
            'company_name' => $this->company?->name,
            'type_id' => $this->type_id ? (string) $this->type_id : null,
            'type_slug' => $this->type?->slug,
            'type_name' => $this->type?->name,
            'status_id' => $this->status_id ? (string) $this->status_id : null,
            'status_slug' => $this->status?->slug,
            'status_name' => $this->status?->name,
            'industry_ids' => $industryIds,
            'industry_slugs' => $industrySlugs,
            'tag_ids' => $tagIds,
            'tag_slugs' => $tagSlugs,
            'license_type_ids' => $licenseTypeIds,
            'license_type_slugs' => $licenseTypeSlugs,
            'contact_ids' => $contactIds,
            'contact_assignment_ids' => $contactAssignmentIds,
            'is_active' => (bool) $this->is_active,
            'start_date' => $this->start_date?->valueOf(),
            'end_date' => $this->end_date?->valueOf(),
            'deadline' => $this->deadline?->valueOf(),
            'name' => $this->name,
            'name_empty' => TextFilterTokens::isEmpty($this->name),
            'name_prefixes' => TextFilterTokens::prefixes($this->name),
            'name_suffixes' => TextFilterTokens::suffixes($this->name),
            'name_ngrams' => TextFilterTokens::ngrams($this->name),
            'project_number' => $this->project_number,
            'project_number_empty' => TextFilterTokens::isEmpty($this->project_number),
            'project_number_prefixes' => TextFilterTokens::prefixes($this->project_number),
            'project_number_suffixes' => TextFilterTokens::suffixes($this->project_number),
            'project_number_ngrams' => TextFilterTokens::ngrams($this->project_number),
            'external_ref' => $this->external_ref,
            'external_ref_empty' => TextFilterTokens::isEmpty($this->external_ref),
            'external_ref_prefixes' => TextFilterTokens::prefixes($this->external_ref),
            'external_ref_suffixes' => TextFilterTokens::suffixes($this->external_ref),
            'external_ref_ngrams' => TextFilterTokens::ngrams($this->external_ref),
            'custom_fields' => $this->custom_fields,
            'created_at' => $this->created_at?->valueOf(),
            'updated_at' => $this->updated_at?->valueOf(),
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->project_number,
                $this->external_ref,
                ...$emails,
                ...$phones,
                ...$addresses,
            ]))),
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['company', 'type', 'status', 'industries', 'tags', 'licenseTypes', 'emails', 'phones', 'addresses', 'contactAssignments']);
    }

    protected static function booted(): void
    {
        $reindexAssignedContacts = static function (self $project): void {
            $project->loadMissing('contactAssignments.contact');

            SearchSync::afterCommit($project->contactAssignments->pluck('contact')->all());
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
            'project_number',
            'external_ref',
        ];
    }

    public function resolvedDisplayName(): string
    {
        return (string) $this->name;
    }
}
