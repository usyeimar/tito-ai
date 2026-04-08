<?php

namespace App\Services\Tenant\Auth\Authentication;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function me(Request $request): ?User
    {
        return $request->user();
    }

    public function login(Request $request, array $data): array
    {
        $user = User::where('email', Str::lower($data['email']))->first();

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'API Token')->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
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
