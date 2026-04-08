<?php

namespace App\Models\Tenant\Commons\Files;

use App\Enums\ModuleType;
use App\Support\Search\SearchSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    public const EXCLUDE_PENDING_DELETION_SCOPE = 'exclude_pending_deletion';

    public const array FILEABLE_TYPES = [
        ModuleType::LEADS,
        ModuleType::CONTACTS,
        ModuleType::COMPANIES,
        ModuleType::PROPERTIES,
        ModuleType::PROJECTS,
        ModuleType::VENDOR_COMPANIES,
    ];

    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'folder_id',
        'name',
        'original_filename',
        'storage_path',
        'disk',
        'mime_type',
        'file_size',
        'checksum_sha256',
        'pending_deletion_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'pending_deletion_at' => 'datetime',
        ];
    }

    public function scopeWithPendingDeletion(Builder $query): Builder
    {
        return $query->withoutGlobalScope(self::EXCLUDE_PENDING_DELETION_SCOPE);
    }

    public function scopeOnlyPendingDeletion(Builder $query): Builder
    {
        return $query
            ->withPendingDeletion()
            ->whereNotNull($query->qualifyColumn('pending_deletion_at'));
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(self::EXCLUDE_PENDING_DELETION_SCOPE, static fn (Builder $query): Builder => $query->whereNull($query->qualifyColumn('pending_deletion_at')));

        $reindex = static function (self $file): void {
            $file->loadMissing('fileable');

            SearchSync::afterCommit($file->fileable);
        };

        static::saved($reindex);
        static::deleted($reindex);
        static::restored($reindex);
        static::forceDeleted($reindex);
    }
}
