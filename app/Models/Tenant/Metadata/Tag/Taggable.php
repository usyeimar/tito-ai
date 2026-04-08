<?php

namespace App\Models\Tenant\Metadata\Tag;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Taggable extends MorphPivot
{
    use HasUlids;

    protected $table = 'metadata_taggables';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tag_id',
        'taggable_type',
        'taggable_id',
    ];
}
