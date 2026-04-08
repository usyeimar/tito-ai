<?php

namespace App\Models\Tenant\Commons\Files;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileUploadIdempotencyKey extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'action',
        'idempotency_key',
        'request_hash',
        'response_status',
        'response_body',
        'upload_session_id',
        'file_id',
        'expires_at',
        'actor_id',
    ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_body' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(FileUploadSession::class, 'upload_session_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
