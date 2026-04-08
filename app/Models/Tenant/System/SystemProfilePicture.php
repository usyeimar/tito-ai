<?php

namespace App\Models\Tenant\System;

use App\Models\Central\System\SystemProfilePicture as CentralSystemProfilePicture;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\ResourceSyncing\ResourceSyncing;
use Stancl\Tenancy\ResourceSyncing\Syncable;

class SystemProfilePicture extends Model implements Syncable
{
    use HasFactory, HasUlids, ResourceSyncing;

    protected $table = 'system_profile_pictures';

    protected $fillable = [
        'global_id',
        'user_global_id',
        'path',
    ];

    public function getCentralModelName(): string
    {
        return CentralSystemProfilePicture::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return [
            'global_id',
            'user_global_id',
            'path',
        ];
    }
}
