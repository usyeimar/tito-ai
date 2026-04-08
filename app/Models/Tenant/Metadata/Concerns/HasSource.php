<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Source\Source;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasSource
{
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
