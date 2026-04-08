<?php

namespace App\Models\Tenant\Commons;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class File extends Model
{
    protected $fillable = ['name', 'path', 'disk', 'mime_type', 'size'];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
