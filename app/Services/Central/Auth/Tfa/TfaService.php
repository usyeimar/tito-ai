<?php

namespace App\Services\Central\Auth\Tfa;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Authentication\Authenticator;
use App\Services\Central\Auth\Password\PasswordConfirmationService;
use App\Services\Concerns\EnsuresVerifiedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TfaService
{
    use EnsuresVerifiedEmail;

    private const int TFA_TTL_MINUTES = 10;

    private const int TFA_MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly PasswordConfirmationService $passwordConfirmationService,
    ) {}

    public function verifyTfa(Request $request, array $data): array
    {
        $payload = Cache::get($this->tfaCacheKey($data['tfa_token']));
        if (! is_array($payload) || empty($payload['user_id'])) {
            throw ValidationException::withMessages([
                'tfa_token' => ['The two-factor session has expired.'],
            ]);
        }

        $user = CentralUser::find($payload['user_id']);
        if (! $user || ! $user->two_factor_enabled || ! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => ['Two-factor authentication is not configured.'],
            ]);
        }

        $google2fa = new Google2FA;
        $valid = $google2fa->verifyKey($user->two_factor_secret, $data['code'], 1);

        if (! $valid && ! $this->consumeRecoveryCode($user, $data['code'])) {
            $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;

            if (($payload['attempts'] ?? 0) >= self::TFA_MAX_ATTEMPTS) {
                Cache::forget($this->tfaCacheKey($data['tfa_token']));
                abort(429, 'Too many invalid codes. Please login again.');
            }

            Cache::put($this->tfaCacheKey($data['tfa_token']), $payload, $this->tfaPayloadExpiresAt($payload));

            throw ValidationException::withMessages([
                'code' => ['The provided two-factor code is invalid.'],
            ]);
        }

        Cache::forget($this->tfaCacheKey($data['tfa_token']));

        $authPayload = $this->authenticator->authenticate($request, $user, $data['device_name'] ?? null);

        return [
            'kind' => 'auth',
            'user' => $user,
            ...$authPayload,
            'tenants' => $user->tenants()->get(),
        ];
    }

    public function challengeTfa(CentralUser $user, string $password): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $this->passwordConfirmationService->markPasswordConfirmed($user);

        return ['message' => 'Challenge passed.'];
    }

    public function enableTfa(CentralUser $user): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $otpauthUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
        ];
    }

    public function confirmTfa(CentralUser $user, string $code): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);

        if (! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => ['Two-factor authentication is not initialized.'],
            ]);
        }

        $google2fa = new Google2FA;
        $valid = $google2fa->verifyKey($user->two_factor_secret, $code, 1);

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => ['The provided two-factor code is invalid.'],
            ]);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $recoveryCodeEntries = $this->recoveryCodeEntries($recoveryCodes);

        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodeEntries,
        ])->save();

        return [
            'recovery_codes' => $recoveryCodeEntries,
        ];
    }

    public function getRecoveryCodes(CentralUser $user): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);

        if (! $user->two_factor_enabled) {
            throw ValidationException::withMessages([
                'two_factor' => ['Two-factor authentication is not enabled.'],
            ]);
        }

        $storedCodes = $user->two_factor_recovery_codes ?? [];

        if (! is_array($storedCodes) || $storedCodes === []) {
            return [
                'recovery_codes' => [],
            ];
        }

        return [
            'recovery_codes' => array_values($storedCodes),
        ];
    }

    public function regenerateRecoveryCodes(CentralUser $user): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);

        if (! $user->two_factor_enabled) {
            throw ValidationException::withMessages([
                'two_factor' => ['Two-factor authentication is not enabled.'],
            ]);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $recoveryCodeEntries = $this->recoveryCodeEntries($recoveryCodes);

        $user->forceFill([
            'two_factor_recovery_codes' => $recoveryCodeEntries,
        ])->save();

        return [
            'recovery_codes' => $recoveryCodeEntries,
        ];
    }

    public function disableTfa(CentralUser $user): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required for management and destructive actions. ');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return [
            'message' => 'Two-factor authentication disabled.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function startTfaChallenge(CentralUser $user): array
    {
        $tfaToken = Str::random(64);
        $expiresAt = now()->addMinutes(self::TFA_TTL_MINUTES);

        Cache::put($this->tfaCacheKey($tfaToken), [
            'user_id' => $user->id,
            'attempts' => 0,
            'expires_at' => $expiresAt->getTimestamp(),
        ], $expiresAt);

        return [
            'kind' => 'tfa_required',
            'user' => $user,
            'tenants' => $user->tenants()->get(),
            'tfa_required' => true,
            'tfa_token' => $tfaToken,
            ...$this->verificationPayload($user),
        ];
    }

    private function tfaCacheKey(string $token): string
    {
        return "tfa:{$token}";
    }

    private function tfaPayloadExpiresAt(array $payload): \DateTimeInterface
    {
        if (array_key_exists('expires_at', $payload) && is_int($payload['expires_at'])) {
            return now()->setTimestamp($payload['expires_at']);
        }

        return now()->addMinutes(self::TFA_TTL_MINUTES);
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(10)).'-'.Str::upper(Str::random(10));
        }

        return $codes;
    }

    private function consumeRecoveryCode(CentralUser $user, string $code): bool
    {
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        if (! is_array($recoveryCodes) || $recoveryCodes === []) {
            return false;
        }

        $normalized = (string) Str::upper(trim($code));

        foreach ($recoveryCodes as $index => $recoveryCode) {
            if (is_array($recoveryCode) && array_key_exists('code', $recoveryCode)) {
                $entryCode = (string) Str::upper(trim((string) ($recoveryCode['code'] ?? '')));
                $usedAt = $recoveryCode['used_at'] ?? null;

                if ($entryCode === '' || $usedAt) {
                    continue;
                }

                if (hash_equals($entryCode, $normalized)) {
                    $recoveryCode['used_at'] = now()->toISOString();
                    $recoveryCodes[$index] = $recoveryCode;

                    $user->forceFill([
                        'two_factor_recovery_codes' => array_values($recoveryCodes),
                    ])->save();

                    return true;
                }

                continue;
            }

            if (! is_string($recoveryCode)) {
                continue;
            }

            $matches = hash_equals((string) Str::upper($recoveryCode), $normalized);

            if ($matches) {
                unset($recoveryCodes[$index]);

                $user->forceFill([
                    'two_factor_recovery_codes' => array_values($recoveryCodes),
                ])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<int, array{code:string,used_at:?string}>
     */
    private function recoveryCodeEntries(array $codes): array
    {
        return array_map(static fn (string $code): array => [
            'code' => $code,
            'used_at' => null,
        ], $codes);
    }

    /**
     * @return array<string, mixed>
     */
    private function verificationPayload(CentralUser $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [];
        }

        return [
            'email_verification_required' => true,
            'verification_sent' => $user->sendEmailVerificationNotificationOnce(),
        ];
    }
}
