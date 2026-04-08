<?php

namespace App\Models\Tenant\Metadata\Industry;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Industriable extends MorphPivot
{
    use HasUlids;

    protected $table = 'metadata_industriables';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'industry_id',
        'industriable_type',
        'industriable_id',
    ];
}
