<?php

namespace App\Services\Central\Auth\Authentication\Api;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;

class AuthService
{
    public function register(Request $request, array $data): array
    {
        $user = CentralUser::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
        ]);

        $token = $user->createToken($data['device_name'] ?? 'API Token')->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Passport::tokensExpireIn()?->totalSeconds,
            'email_verification_required' => ! $user->hasVerifiedEmail(),
        ];
    }

    public function login(Request $request, array $data): array
    {
        $user = CentralUser::where('email', Str::lower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->two_factor_enabled) {
            return [
                'kind' => 'tfa_required',
                'user' => $user,
                'tfa_required' => true,
            ];
        }

        $token = $user->createToken($data['device_name'] ?? 'API Token')->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Passport::tokensExpireIn()?->totalSeconds,
            'email_verification_required' => ! $user->hasVerifiedEmail(),
            'tenants' => $user->tenants()->get(),
        ];
    }

    public function logout(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $user->token()?->revoke();
        }

        return ['message' => 'Logged out.'];
    }
}
