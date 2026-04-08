<?php

namespace App\Models\Tenant\Commons\Files;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileUploadPart extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'upload_session_id',
        'part_number',
        'etag',
        'size_bytes',
        'checksum_sha256',
    ];

    protected function casts(): array
    {
        return [
            'part_number' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(FileUploadSession::class, 'upload_session_id');
    }
}
