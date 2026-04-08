<?php

declare(strict_types=1);

namespace App\Models\Tenant\Activity;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityEvent extends Model
{
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'subject_label',
        'event_type',
        'actor_type',
        'actor_id',
        'actor_label',
        'origin',
        'request_id',
        'workflow_actor_type',
        'workflow_actor_id',
        'workflow_actor_label',
        'workflow_run_id',
        'changes',
        'metadata',
        'occurred_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function relations(): HasMany
    {
        return $this->hasMany(ActivityEventRelation::class, 'activity_event_id');
    }
}
