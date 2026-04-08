<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Models\Tenant\Metadata\Category\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCategory
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
