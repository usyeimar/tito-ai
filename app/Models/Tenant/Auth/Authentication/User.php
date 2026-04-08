<?php

namespace App\Models\Tenant\Auth\Authentication;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\System\SystemProfilePicture;
use App\Support\Search\TextFilterTokens;
use Database\Factories\Tenant\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\ResourceSyncing\ResourceSyncing;
use Stancl\Tenancy\ResourceSyncing\Syncable;

class User extends Authenticatable implements Syncable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUlids,  Notifiable, ResourceSyncing;

    protected string $guard_name = 'tenant';

    protected $fillable = [
        'global_id',
        'name',
        'email',
        'email_verified_at',
        'is_active',
        'password',
        'remember_token',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Get the user provider name for Passport.
     */
    public function getProviderName(): string
    {
        return 'tenant_users';
    }

    public function getCentralModelName(): string
    {
        return CentralUser::class;
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

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getKey(),
            'name' => $this->name,
            'name_empty' => TextFilterTokens::isEmpty($this->name),
            'name_prefixes' => TextFilterTokens::prefixes($this->name),
            'name_suffixes' => TextFilterTokens::suffixes($this->name),
            'name_ngrams' => TextFilterTokens::ngrams($this->name),
            'email' => $this->email,
            'email_empty' => TextFilterTokens::isEmpty($this->email),
            'email_prefixes' => TextFilterTokens::prefixes($this->email),
            'email_suffixes' => TextFilterTokens::suffixes($this->email),
            'email_ngrams' => TextFilterTokens::ngrams($this->email),
            'is_active' => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->email,
            ]))),
        ];
    }
}
