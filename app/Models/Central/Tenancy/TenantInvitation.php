<?php

namespace App\Models\Central\Tenancy;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantInvitation extends Model
{
    use CentralConnection, HasUlids;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_ACCEPTED = 'accepted';

    public const string STATUS_DECLINED = 'declined';

    public const string STATUS_REVOKED = 'revoked';

    public const string STATUS_EXPIRED = 'expired';

    protected $table = 'tenant_invitations';

    protected $fillable = [
        'tenant_id',
        'email',
        'token_hash',
        'invited_by_central_user_id',
        'status',
        'expires_at',
        'last_sent_at',
        'accepted_at',
        'declined_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(CentralUser::class, 'invited_by_central_user_id', 'id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markExpiredIfNeeded(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            return;
        }

        if (! $this->isExpired()) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_EXPIRED,
        ])->save();
    }

    public function setExpiry(Carbon $expiresAt): void
    {
        $this->forceFill(['expires_at' => $expiresAt]);
    }
}
