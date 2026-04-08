<?php

namespace App\Models\Tenant\Commons\Emails;

use App\Enums\EmailLabel;
use App\Enums\ModuleType;
use App\Models\Tenant\Commons\Concerns\CommonsConfigurable;
use App\Support\Search\SearchSync;
use App\Traits\HasWorkflows;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $emailable_type
 * @property string $emailable_id
 * @property string $email
 * @property EmailLabel|null $label
 * @property bool $is_primary
 */
class Email extends Model implements CommonsConfigurable
{
    use HasFactory, HasUlids, HasWorkflows;

    public const array EMAILABLE_TYPES = [
        ModuleType::LEADS,
        ModuleType::CONTACTS,
        ModuleType::COMPANIES,
        ModuleType::PROPERTIES,
        ModuleType::PROJECTS,
        ModuleType::VENDOR_COMPANIES,
    ];

    protected $fillable = [
        'emailable_type',
        'emailable_id',
        'email',
        'label',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'label' => EmailLabel::class,
        ];
    }

    public function getMutationConfig(): array
    {
        return [
            'type_column' => 'emailable_type',
            'id_column' => 'emailable_id',
            'module' => 'email',
            'fields' => ['email', 'label', 'is_primary'],
        ];
    }

    public function emailable(): MorphTo
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
        $reindex = static function (self $email): void {
            $email->loadMissing('emailable');

            SearchSync::afterCommit($email->emailable);
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
