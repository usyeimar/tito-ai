<?php

declare(strict_types=1);

namespace App\Models\Tenant\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentSetting extends Model
{
    use HasUlids;

    protected $table = 'agent_settings';

    protected $fillable = [
        'agent_id',
        'brain_config',
        'runtime_config',
        'architecture_config',
        'capabilities_config',
        'observability_config',
    ];

    protected $casts = [
        'brain_config' => 'array',
        'runtime_config' => 'array',
        'architecture_config' => 'array',
        'capabilities_config' => 'array',
        'observability_config' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
