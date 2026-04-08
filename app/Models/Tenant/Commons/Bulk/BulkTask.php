<?php

namespace App\Models\Tenant\Commons\Bulk;

use App\Enums\BulkTaskStatus;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkTask extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'module',
        'resource',
        'action',
        'status',
        'requested_by',
        'client_request_id',
        'context',
        'requested_ids',
        'submitted_count',
        'processed_count',
        'success_count',
        'skipped_count',
        'failed_count',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BulkTaskStatus::class,
            'requested_ids' => 'array',
            'context' => 'array',
            'submitted_count' => 'integer',
            'processed_count' => 'integer',
            'success_count' => 'integer',
            'skipped_count' => 'integer',
            'failed_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BulkTaskItem::class, 'task_id');
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }
}
