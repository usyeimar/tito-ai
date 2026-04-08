<?php

namespace App\Http\Controllers\Central\API\Auth\Token;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\Auth\RefreshTokenRequest;
use App\Services\Central\Auth\Token\PassportTokenService;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __construct(
        private readonly PassportTokenService $tokenService,
        private readonly TokenCookieService $cookieService,
    ) {}

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $payload = $this->tokenService->refreshCentralToken($request->validated('refresh_token'));

        if ($this->cookieService->shouldUseCookies($request)) {
            $refreshToken = $payload['refresh_token'] ?? null;
            $accessToken = $payload['access_token'] ?? null;
            unset($payload['refresh_token']);
            unset($payload['access_token'], $payload['token_type']);

            $response = response()->json($payload);

            if (is_string($accessToken) && $accessToken !== '') {
                $response->headers->setCookie(
                    $this->cookieService->centralAccessCookie($accessToken)
                );
            }

            if (is_string($refreshToken) && $refreshToken !== '') {
                $response->headers->setCookie(
                    $this->cookieService->centralRefreshCookie($refreshToken)
                );
            }

            return $response;
        }

        return response()->json($payload);
    }

    public function tokens(Request $request): JsonResponse
    {
        $result = $this->tokenService->listTokens($request->user());

        return response()->json($result);
    }

    public function revokeTokens(Request $request): JsonResponse
    {
        $result = $this->tokenService->revokeTokens($request->user());

        return response()->json($result);
    }

    public function revokeToken(Request $request, string $token): JsonResponse
    {
        $result = $this->tokenService->revokeToken($request->user(), $token);

        return response()->json($result);
    }
}
