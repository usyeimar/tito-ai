<?php

namespace App\Models\Tenant\Commons;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $entity_type
 * @property string $entity_id
 * @property string $path
 */
class EntityProfilePicture extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'entity_profile_pictures';

    protected $fillable = ['entity_type', 'entity_id', 'path'];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
