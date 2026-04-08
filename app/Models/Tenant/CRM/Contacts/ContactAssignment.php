<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Contacts;

use App\Support\Search\SearchSync;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $contact_id
 * @property string $assignable_type
 * @property string $assignable_id
 * @property bool $is_primary
 * @property string|null $relationship_role
 * @property string|null $title
 * @property bool $is_decision_maker
 * @property bool $is_on_site_contact
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Contact|null $contact
 * @property-read Model|null $assignable
 */
class ContactAssignment extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'assignable_type',
        'assignable_id',
        'is_primary',
        'relationship_role',
        'title',
        'is_decision_maker',
        'is_on_site_contact',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_decision_maker' => 'boolean',
            'is_on_site_contact' => 'boolean',
        ];
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return MorphTo<Model, $this> */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        $reindex = static function (self $assignment): void {
            $assignment->loadMissing(['contact', 'assignable']);

            SearchSync::afterCommit([$assignment->contact, $assignment->assignable]);
        };

        static::saved($reindex);
        static::deleted($reindex);
        static::restored($reindex);
        static::forceDeleted($reindex);
    }
}
