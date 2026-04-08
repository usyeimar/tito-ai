<?php

namespace App\Services\Central\Auth\Authentication;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Token\PassportTokenService;
use Illuminate\Http\Request;

class Authenticator
{
    public function __construct(
        private readonly PassportTokenService $tokenService,
    ) {}

    /**
     * Authenticate the given user and issue Passport tokens.
     *
     * @return array<string, mixed>
     */
    public function authenticate(Request $request, CentralUser $user, ?string $deviceName, ?string $passwordForGrant = null): array
    {
        $tokenPayload = $passwordForGrant !== null
            ? $this->tokenService->issueCentralTokensWithPassword($user->email, $passwordForGrant)
            : $this->tokenService->issueCentralTokensForUser($user);

        return [
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'],
            'expires_in' => $tokenPayload['expires_in'] ?? null,
            'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
            ...$this->verificationPayload($user),
        ];
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
