<?php

namespace App\Services\Central\Auth\SocialLogin;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\Auth\SocialLogin\SocialAccount;
use App\Services\Central\Auth\Authentication\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use SocialiteProviders\Microsoft\Provider;

readonly class SocialLoginService
{
    public function __construct(
        private AuthService $authService,
    ) {}

    public function loginWithGoogle(Request $request, string $accessToken, ?string $deviceName): array
    {
        if ((string) config('services.google.client_id') === '') {
            throw ValidationException::withMessages([
                'access_token' => ['OAuth client is not configured.'],
            ]);
        }

        try {
            /** @var GoogleProvider $googleProvider */
            $googleProvider = Socialite::driver('google');
            $socialiteUser = $googleProvider->userFromToken($accessToken);
        } catch (\Throwable $e) {
            Log::warning('Google social login access token validation failed.', [
                'provider' => 'google',
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'access_token' => ['Invalid access token.'],
            ]);
        }

        $raw = is_array($socialiteUser->getRaw()) ? $socialiteUser->getRaw() : [];
        $emailVerified = $this->parseBoolean($raw['verified_email'] ?? $raw['email_verified'] ?? null);
        $email = Str::lower((string) ($socialiteUser->getEmail() ?? ''));
        $name = (string) ($socialiteUser->getName() ?? $email);

        return $this->handleLogin($request, [
            'provider' => 'google',
            'provider_user_id' => (string) ($socialiteUser->getId() ?? ''),
            'email' => $email,
            'email_verified' => $emailVerified,
            'name' => $name !== '' ? $name : $email,
        ], $deviceName, 'access_token');
    }

    public function loginWithMicrosoft(Request $request, string $accessToken, ?string $deviceName): array
    {
        if ((string) config('services.microsoft.client_id') === '') {
            throw ValidationException::withMessages([
                'access_token' => ['OAuth client is not configured.'],
            ]);
        }

        try {
            /** @var Provider $microsoftProvider */
            $microsoftProvider = Socialite::driver('microsoft');
            $socialiteUser = $microsoftProvider->userFromToken($accessToken);
        } catch (\Throwable $e) {
            Log::warning('Microsoft social login access token validation failed.', [
                'provider' => 'microsoft',
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'access_token' => ['Invalid access token.'],
            ]);
        }

        $raw = is_array($socialiteUser->getRaw()) ? $socialiteUser->getRaw() : [];
        $email = (string) ($socialiteUser->getEmail() ?? $raw['mail'] ?? $raw['userPrincipalName'] ?? '');
        $email = Str::lower($email);
        $name = (string) ($socialiteUser->getName() ?? $email);

        return $this->handleLogin($request, [
            'provider' => 'microsoft',
            'provider_user_id' => (string) ($socialiteUser->getId() ?? ''),
            'email' => $email,
            'email_verified' => true,
            'name' => $name !== '' ? $name : $email,
        ], $deviceName, 'access_token');
    }

    /**
     * @param  array{provider:string,provider_user_id:string,email:string,email_verified:bool,name:string}  $data
     */
    private function handleLogin(Request $request, array $data, ?string $deviceName, string $tokenField = 'id_token'): array
    {
        if ($data['provider_user_id'] === '' || $data['email'] === '') {
            throw ValidationException::withMessages([
                $tokenField => ['Social account email is required.'],
            ]);
        }

        $account = SocialAccount::query()
            ->where('provider', $data['provider'])
            ->where('provider_user_id', $data['provider_user_id'])
            ->first();

        $user = $account?->user;

        if (! $user) {
            if (! $data['email_verified']) {
                $existing = CentralUser::query()
                    ->where('email', $data['email'])
                    ->first();

                if ($existing && ! $existing->hasVerifiedEmail()) {
                    $existing->sendEmailVerificationNotificationOnce();
                }

                throw ValidationException::withMessages([
                    $tokenField => ['Email address is not verified.'],
                ]);
            }

            $user = CentralUser::query()
                ->where('email', $data['email'])
                ->first();

            if (! $user) {
                $user = CentralUser::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'email_verified_at' => now(),
                    'password' => Str::random(64),
                ]);
            } elseif (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            if ($user->socialAccounts()->where('provider', $data['provider'])->exists()) {
                throw ValidationException::withMessages([
                    $tokenField => ['Social account already linked.'],
                ]);
            }

            $account = SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $data['provider'],
                'provider_user_id' => $data['provider_user_id'],
                'email' => $data['email'],
            ]);
        }

        if ($user->two_factor_enabled) {
            return $this->authService->startTfaChallenge($user);
        }

        $authPayload = $this->authService->authenticate($request, $user, $deviceName);

        return [
            'kind' => 'auth',
            'user' => $user,
            'tenants' => $user->tenants()->get(),
            ...$authPayload,
        ];
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $parsed ?? false;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return false;
    }
}
