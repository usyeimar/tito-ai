<?php

namespace App\Models\Central\Auth\SocialLogin;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class SocialAccount extends Model
{
    use CentralConnection, HasUlids;

    protected $table = 'social_accounts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CentralUser::class, 'user_id');
    }
}
