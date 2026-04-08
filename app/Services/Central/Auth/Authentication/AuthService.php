<?php

namespace App\Services\Central\Auth\Authentication;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Tfa\TfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class AuthService
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly TfaService $tfaService,
    ) {}

    /**
     * Authenticate the given user and issue Passport tokens.
     *
     * @return array<string, mixed>
     */
    public function authenticate(Request $request, CentralUser $user, ?string $deviceName, ?string $passwordForGrant = null): array
    {
        return $this->authenticator->authenticate($request, $user, $deviceName, $passwordForGrant);
    }

    public function startTfaChallenge(CentralUser $user): array
    {
        return $this->tfaService->startTfaChallenge($user);
    }

    public function register(Request $request, array $data): array
    {
        $user = CentralUser::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
        ]);

        $verificationSent = $user->sendEmailVerificationNotificationOnce();

        return [
            'kind' => 'auth',
            'user' => $user,
            'email_verification_required' => true,
            'verification_sent' => $verificationSent,
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
            return $this->tfaService->startTfaChallenge($user);
        }

        $authPayload = $this->authenticate($request, $user, $data['device_name'] ?? null, $data['password']);

        return [
            'kind' => 'auth',
            'user' => $user,
            ...$authPayload,
            'tenants' => $user->tenants()->get(),
        ];
    }

    public function logout(Request $request): array
    {
        $user = $request->user();
        if (! $user) {
            return ['message' => 'Logged out.'];
        }

        $token = $user->token();
        if ($token instanceof AccessToken) {
            $token->revoke();
            $this->revokeRefreshTokenByAccessTokenId($token->oauth_access_token_id ?? null);

            return ['message' => 'Logged out.'];
        }

        if ($token instanceof Token) {
            $token->revoke();
            $this->revokeRefreshTokenByAccessTokenId($token->getKey());

            return ['message' => 'Logged out.'];
        }

        return ['message' => 'Logged out.'];
    }

    private function revokeRefreshTokenByAccessTokenId(?string $tokenId): void
    {
        if (! is_string($tokenId) || $tokenId === '') {
            return;
        }

        Passport::refreshToken()
            ->newQuery()
            ->where('access_token_id', $tokenId)
            ->update(['revoked' => true]);
    }
}
