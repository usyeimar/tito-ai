<?php

namespace App\Models\Tenant\Commons\Concerns;

use App\Models\Tenant\Commons\EntityProfilePicture;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasProfilePicture
{
    public function profilePicture(): MorphOne
    {
        return $this->morphOne(EntityProfilePicture::class, 'entity');
    }
}
