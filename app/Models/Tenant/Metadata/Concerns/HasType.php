<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Type\Type;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasType
{
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
