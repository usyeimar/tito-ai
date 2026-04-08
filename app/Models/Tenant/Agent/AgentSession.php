<?php

namespace App\Models\Tenant\Agent;

use App\Models\Tenant\Concerns\HasFiles;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentSession extends Model
{
    use HasFactory, HasFiles, HasUlids;

    protected $fillable = ['agent_id', 'status', 'started_at', 'ended_at'];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(AgentSessionTranscript::class);
    }

    public function audio(): HasMany
    {
        return $this->hasMany(AgentSessionAudio::class);
    }
}
