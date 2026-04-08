<?php

namespace App\Models\Tenant\Commons\Concerns;

use App\Models\Tenant\Commons\EntityFavicon;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasFavicon
{
    public function favicon(): MorphOne
    {
        return $this->morphOne(EntityFavicon::class, 'entity');
    }
}
