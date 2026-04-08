<?php

namespace App\Services\Central\Auth\Password;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PasswordConfirmationService
{
    private const int SENSITIVE_CONFIRM_TTL_MINUTES = 10;

    public function ensureRecentPasswordConfirmation(CentralUser $user): void
    {
        if (! Cache::has($this->confirmCacheKey($user))) {
            throw ValidationException::withMessages([
                'password' => ['Please confirm your password to continue.'],
            ]);
        }
    }

    public function markPasswordConfirmed(CentralUser $user): void
    {
        Cache::put(
            $this->confirmCacheKey($user),
            true,
            now()->addMinutes(self::SENSITIVE_CONFIRM_TTL_MINUTES)
        );
    }

    private function confirmCacheKey(CentralUser $user): string
    {
        return "auth:confirmed:{$user->id}";
    }
}
