<?php

namespace App\Models\Tenant\Commons\Files;

use App\Support\Search\SearchSync;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileFolder extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'parent_id',
        'name',
    ];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    protected static function booted(): void
    {
        $reindex = static function (self $folder): void {
            $folder->loadMissing('fileable');

            SearchSync::afterCommit($folder->fileable);
        };

        static::saved($reindex);
        static::deleted($reindex);
        static::restored($reindex);
        static::forceDeleted($reindex);
    }
}
