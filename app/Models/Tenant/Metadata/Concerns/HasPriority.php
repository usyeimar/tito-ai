<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Priority\Priority;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPriority
{
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }
}
