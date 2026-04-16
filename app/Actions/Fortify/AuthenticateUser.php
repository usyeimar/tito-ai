<?php

namespace App\Actions\Fortify;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Token\PassportTokenService;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\Request;

class AuthenticateUser
{
    public function __construct(
        private readonly PassportTokenService $tokenService,
        private readonly TokenCookieService $cookieService,
    ) {}

    /**
     * Authenticate a user and issue central API cookies.
     *
     * @return array{access_token: string, refresh_token: string}
     */
    public function authenticate(Request $request, CentralUser $user): array
    {
        $loginToken = $user->createPassportLoginToken();

        $tokenPayload = $this->tokenService->issueCentralTokensWithPassword(
            $user->email,
            $loginToken
        );

        return [
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'],
        ];
    }
}
