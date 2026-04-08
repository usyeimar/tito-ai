<?php

namespace App\Models\Tenant\Concerns;

use App\Models\Tenant\Commons\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFiles
{
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
