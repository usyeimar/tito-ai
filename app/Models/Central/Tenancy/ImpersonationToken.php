<?php

namespace App\Models\Central\Tenancy;

use Stancl\Tenancy\Database\Models\ImpersonationToken as BaseImpersonationToken;

class ImpersonationToken extends BaseImpersonationToken
{
    protected $table = 'tenant_user_impersonation_tokens';

    protected $fillable = [
        'token',
        'tenant_id',
        'user_id',
        'remember',
        'auth_guard',
        'redirect_url',
        'impersonator_central_user_id',
    ];

    public static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model): void {
            if (! $model->created_at) {
                $model->created_at = $model->freshTimestamp();
            }
        });
    }
}
