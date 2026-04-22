<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Leads;

use App\Models\Tenant\Commons\Concerns\HasAddresses;
use App\Models\Tenant\Commons\Concerns\HasEmails;
use App\Models\Tenant\Commons\Concerns\HasFiles;
use App\Models\Tenant\Commons\Concerns\HasPhones;
use App\Models\Tenant\Commons\Concerns\HasProfilePicture;
use App\Models\Tenant\Metadata\Category\Category;
use App\Models\Tenant\Metadata\Concerns\HasCategory;
use App\Models\Tenant\Metadata\Concerns\HasIndustries;
use App\Models\Tenant\Metadata\Concerns\HasPriority;
use App\Models\Tenant\Metadata\Concerns\HasSource;
use App\Models\Tenant\Metadata\Concerns\HasStatus;
use App\Models\Tenant\Metadata\Concerns\HasTags;
use App\Models\Tenant\Metadata\Industry\Industry;
use App\Models\Tenant\Metadata\Priority\Priority;
use App\Models\Tenant\Metadata\Source\Source;
use App\Models\Tenant\Metadata\Status\Status;
use App\Models\Tenant\Metadata\Tag\Tag;
use App\Support\Search\TextFilterTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Lead extends Model
{
    use HasAddresses, HasCategory, HasEmails, HasFactory, HasFiles,  HasIndustries,  HasPhones, HasPriority, HasProfilePicture, HasSource, HasStatus, HasTags, HasUlids,  Searchable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'lead_number',
        'normalized_display_name',
        'external_ref',
        'category_id',
        'source_id',
        'status_id',
        'priority_id',
        'company_name',
        'company_website',
        'converted_at',
        'converted_to_company_id',
        'converted_to_contact_id',
        'converted_to_project_id',
        'conversion_payload_snapshot',
        'custom_fields',
        'notes',
        'tag_ids',
    ];

    public function setTagIdsAttribute($value): void {}

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'conversion_payload_snapshot' => 'array',
            'custom_fields' => 'array',
        ];
    }

    public function resolvedDisplayName(): string
    {
        $explicit = trim((string) $this->display_name);

        if ($explicit !== '') {
            return $explicit;
        }

        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['emails', 'phones', 'addresses', 'category', 'status', 'source', 'industries', 'priority', 'tags', 'primaryEmail', 'primaryPhone', 'primaryAddress']);

        $displayName = $this->resolvedDisplayName();

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

        $primaryAddress = $this->primaryAddress
            ? trim(implode(' ', array_filter([
                $this->primaryAddress->address_line,
                $this->primaryAddress->address_line_2,
                $this->primaryAddress->city,
                $this->primaryAddress->state_region,
                $this->primaryAddress->postal_code,
                $this->primaryAddress->country_code,
            ])))
            : null;

        return [
            'id' => (string) $this->getKey(),
            'category_id' => $this->category_id ? (string) $this->category_id : null,
            'category_slug' => $this->category?->slug,
            'category_name' => $this->category?->name,
            'status_id' => $this->status_id ? (string) $this->status_id : null,
            'status_slug' => $this->status?->slug,
            'status_name' => $this->status?->name,
            'source_id' => $this->source_id ? (string) $this->source_id : null,
            'source_slug' => $this->source?->slug,
            'source_name' => $this->source?->name,
            'industry_ids' => $industryIds,
            'industry_slugs' => $industrySlugs,
            'priority_id' => $this->priority_id ? (string) $this->priority_id : null,
            'priority_slug' => $this->priority?->slug,
            'priority_name' => $this->priority?->name,
            'tag_ids' => $tagIds,
            'tag_slugs' => $tagSlugs,
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
            'display_name' => $displayName,
            'display_name_empty' => TextFilterTokens::isEmpty($displayName),
            'display_name_prefixes' => TextFilterTokens::prefixes($displayName),
            'display_name_suffixes' => TextFilterTokens::suffixes($displayName),
            'display_name_ngrams' => TextFilterTokens::ngrams($displayName),
            'lead_number' => $this->lead_number,
            'lead_number_empty' => TextFilterTokens::isEmpty($this->lead_number),
            'lead_number_prefixes' => TextFilterTokens::prefixes($this->lead_number),
            'lead_number_suffixes' => TextFilterTokens::suffixes($this->lead_number),
            'lead_number_ngrams' => TextFilterTokens::ngrams($this->lead_number),
            'external_ref' => $this->external_ref,
            'external_ref_empty' => TextFilterTokens::isEmpty($this->external_ref),
            'external_ref_prefixes' => TextFilterTokens::prefixes($this->external_ref),
            'external_ref_suffixes' => TextFilterTokens::suffixes($this->external_ref),
            'external_ref_ngrams' => TextFilterTokens::ngrams($this->external_ref),
            'company_name' => $this->company_name,
            'company_name_empty' => TextFilterTokens::isEmpty($this->company_name),
            'company_name_prefixes' => TextFilterTokens::prefixes($this->company_name),
            'company_name_suffixes' => TextFilterTokens::suffixes($this->company_name),
            'company_name_ngrams' => TextFilterTokens::ngrams($this->company_name),
            'company_website' => $this->company_website,
            'company_website_empty' => TextFilterTokens::isEmpty($this->company_website),
            'company_website_prefixes' => TextFilterTokens::prefixes($this->company_website),
            'company_website_suffixes' => TextFilterTokens::suffixes($this->company_website),
            'company_website_ngrams' => TextFilterTokens::ngrams($this->company_website),
            'primary_email' => $this->primaryEmail?->email,
            'primary_phone' => $this->primaryPhone?->phone,
            'primary_address' => $primaryAddress,
            'converted_at' => $this->converted_at?->valueOf(),
            'converted_to_company_id' => $this->converted_to_company_id ? (string) $this->converted_to_company_id : null,
            'converted_to_contact_id' => $this->converted_to_contact_id ? (string) $this->converted_to_contact_id : null,
            'converted_to_project_id' => $this->converted_to_project_id ? (string) $this->converted_to_project_id : null,
            'created_at' => $this->created_at?->valueOf(),
            'updated_at' => $this->updated_at?->valueOf(),
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'search_blob' => trim(implode(' ', array_filter([
                $this->first_name,
                $this->last_name,
                $displayName,
                $this->lead_number,
                $this->external_ref,
                $this->company_name,
                $this->company_website,
                ...$emails,
                ...$phones,
                ...$addresses,
            ]))),
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['emails', 'phones', 'addresses', 'category', 'status', 'source', 'industries', 'priority', 'tags', 'primaryEmail', 'primaryPhone', 'primaryAddress']);
    }

    public static function getExposedRelations(): array
    {
        return [
            'category' => Category::class,
            'status' => Status::class,
            'source' => Source::class,
            'industries' => Industry::class,
            'priority' => Priority::class,
            'tags' => Tag::class,
        ];
    }

    public static function getObjectMetadata(): array
    {
        return [
            'name_singular' => 'lead',
            'name_plural' => 'leads',
            'label_singular' => 'Lead',
            'label_plural' => 'Leads',
            'icon' => 'IconUserPlus',
        ];
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
            'lead_number',
            'external_ref',
            'company_name',
            'company_website',
            'notes',
        ];
    }
}
