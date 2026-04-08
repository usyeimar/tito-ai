<?php

declare(strict_types=1);

namespace App\Models\Tenant\Knowledge;

use App\Models\Tenant\Agent\Agent;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    use HasUlids;

    protected $table = 'knowledge_bases';

    protected $fillable = [
        'name',
        'provider',
        'embedding_model',
        'provider_config',
    ];

    protected $casts = [
        'provider_config' => 'array',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
