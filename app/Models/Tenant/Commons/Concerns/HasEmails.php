<?php

namespace App\Models\Tenant\Commons\Concerns;

use App\Models\Tenant\Commons\Emails\Email;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEmails
{
    public function emails(): MorphMany
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    public function primaryEmail(): MorphOne
    {
        return $this->morphOne(Email::class, 'emailable')
            ->where('is_primary', true);
    }
}
