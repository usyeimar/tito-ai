<?php

namespace App\Models\Tenant\Commons\Bulk;

use App\Enums\BulkTaskItemStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkTaskItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'task_id',
        'position',
        'item_id',
        'status',
        'code',
        'detail',
        'http_status',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'status' => BulkTaskItemStatus::class,
            'position' => 'integer',
            'http_status' => 'integer',
            'result' => 'array',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(BulkTask::class, 'task_id');
    }
}
