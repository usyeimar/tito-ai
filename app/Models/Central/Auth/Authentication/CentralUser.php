<?php

namespace App\Models\Central\Auth\Authentication;

use App\Models\Central\Auth\SocialLogin\SocialAccount;
use App\Models\Central\System\SystemProfilePicture;
use App\Models\Tenant\Auth\Authentication\User;
use App\Notifications\Auth\VerifyUpdatedEmail;
use Database\Factories\Central\Auth\Authentication\CentralUserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\ResourceSyncing\ResourceSyncing;
use Stancl\Tenancy\ResourceSyncing\SyncMaster;

class CentralUser extends Authenticatable implements MustVerifyEmail, OAuthenticatable, SyncMaster, WebAuthnAuthenticatable
{
    /** @use HasFactory<CentralUserFactory> */
    use CentralConnection, HasApiTokens, HasFactory, HasRoles, HasUlids, MustVerifyEmailTrait, Notifiable, ResourceSyncing, WebAuthnAuthentication;

    protected string $guard_name = 'web';

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'global_id',
        'name',
        'email',
        'email_verified_at',
        'email_verification_sent_at',
        'password',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted:string',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function sendEmailVerificationNotificationOnce(string $reason = 'signup'): bool
    {
        if ($this->hasVerifiedEmail() || $this->email_verification_sent_at !== null) {
            return false;
        }

        $this->sendVerificationNotification($reason);
        $this->markEmailVerificationSent();

        return true;
    }

    public function resendEmailVerificationNotification(string $reason = 'signup'): bool
    {
        if ($this->hasVerifiedEmail()) {
            Log::info('Cannot resend verification email - already verified', [
                'user_id' => $this->id,
                'email' => $this->email,
            ]);

            return false;
        }

        try {
            $this->sendVerificationNotification($reason);
            $this->markEmailVerificationSent();

            Log::info('Verification email notification sent', [
                'user_id' => $this->id,
                'email' => $this->email,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email notification', [
                'user_id' => $this->id,
                'email' => $this->email,
                'reason' => $reason,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_sent_at' => null,
        ])->save();
    }

    public function markEmailAsUnverified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => null,
            'email_verification_sent_at' => null,
        ])->save();
    }

    public function getTenantModelName(): string
    {
        return User::class;
    }

    public function getCentralModelName(): string
    {
        return static::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return [
            'global_id',
            'name',
            'email',
            'email_verified_at',
            'password',
        ];
    }

    public function profilePicture(): HasOne
    {
        return $this->hasOne(SystemProfilePicture::class, 'user_global_id', 'global_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'user_id');
    }

    /**
     * Get the user provider name for Passport.
     *
     * Overridden because the default implementation only recognizes
     * providers with the 'eloquent' driver, but this model uses
     * the 'eloquent-webauthn' driver.
     */
    public function getProviderName(): string
    {
        return 'central_users';
    }

    public function findForPassport(string $username): ?self
    {
        return static::query()
            ->where('email', Str::lower($username))
            ->first();
    }

    public function validateForPassportPasswordGrant(string $password): bool
    {
        if (Hash::check($password, (string) $this->password)) {
            return true;
        }

        $token = Cache::pull($this->passportLoginTokenCacheKey());
        if (! is_string($token) || $token === '') {
            return false;
        }

        return Hash::check($password, $token);
    }

    public function createPassportLoginToken(int $ttlSeconds = 60): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->passportLoginTokenCacheKey(),
            Hash::make($token),
            now()->addSeconds(max(10, $ttlSeconds)),
        );

        return $token;
    }

    private function passportLoginTokenCacheKey(): string
    {
        return "passport:login:{$this->getKey()}";
    }

    private function markEmailVerificationSent(): void
    {
        $this->forceFill([
            'email_verification_sent_at' => now(),
        ])->save();
    }

    private function sendVerificationNotification(string $reason): void
    {
        if ($reason === 'update') {
            $this->notify(new VerifyUpdatedEmail);

            return;
        }

        $this->sendEmailVerificationNotification();
    }
}
