<?php

declare(strict_types=1);

namespace App\Models\Tenant\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTool extends Model
{
    use HasUlids;

    protected $table = 'agent_tools';

    protected $fillable = [
        'agent_id',
        'name',
        'type',
        'api_endpoint',
        'requires_confirmation',
        'timeout_ms',
        'disabled',
    ];

    protected $casts = [
        'requires_confirmation' => 'boolean',
        'disabled' => 'boolean',
        'timeout_ms' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
