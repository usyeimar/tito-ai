<?php

namespace App\Models\Tenant\Commons;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntityFavicon extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'entity_favicons';

    protected $fillable = ['entity_type', 'entity_id', 'path', 'mime_type'];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
