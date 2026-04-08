<?php

namespace App\Models\Tenant\Commons\Files;

use App\Enums\FileUploadSessionStatus;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileUploadSession extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'folder_id',
        'name',
        'original_filename',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'disk',
        'object_key',
        'provider_upload_id',
        'chunk_size_bytes',
        'part_count',
        'status',
        'expires_at',
        'completed_at',
        'aborted_at',
        'failed_at',
        'failure_code',
        'failure_reason',
        'client_upload_id',
        'file_id',
        'actor_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => FileUploadSessionStatus::class,
            'size_bytes' => 'integer',
            'chunk_size_bytes' => 'integer',
            'part_count' => 'integer',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'aborted_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(FileUploadPart::class, 'upload_session_id');
    }
}
