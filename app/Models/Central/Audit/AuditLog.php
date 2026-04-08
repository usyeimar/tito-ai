<?php

namespace App\Models\Central\Audit;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class AuditLog extends Model
{
    use CentralConnection, HasUlids;

    protected $table = 'audit_logs';

    protected $fillable = [
        'tenant_id',
        'actor_central_user_id',
        'tenant_user_id',
        'route',
        'method',
        'path',
        'status',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
