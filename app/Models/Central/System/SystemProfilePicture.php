<?php

namespace App\Models\Central\System;

use App\Models\Tenant\System\SystemProfilePicture as TenantSystemProfilePicture;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\ResourceSyncing\ResourceSyncing;
use Stancl\Tenancy\ResourceSyncing\SyncMaster;

class SystemProfilePicture extends Model implements SyncMaster
{
    use CentralConnection, HasFactory, HasUlids, ResourceSyncing;

    protected $table = 'system_profile_pictures';

    protected $fillable = [
        'global_id',
        'user_global_id',
        'path',
    ];

    public function getTenantModelName(): string
    {
        return TenantSystemProfilePicture::class;
    }

    public function getCentralModelName(): string
    {
        return static::class;
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
