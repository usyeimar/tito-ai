<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Status\Status;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasStatus
{
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
