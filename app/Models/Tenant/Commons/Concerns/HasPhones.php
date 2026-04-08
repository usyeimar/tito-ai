<?php

namespace App\Models\Tenant\Commons\Concerns;

use App\Models\Tenant\Commons\Phones\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPhones
{
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    public function primaryPhone(): MorphOne
    {
        return $this->morphOne(Phone::class, 'phoneable')
            ->where('is_primary', true);
    }
}
