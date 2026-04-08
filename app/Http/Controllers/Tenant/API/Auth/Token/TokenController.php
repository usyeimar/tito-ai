<?php

namespace App\Http\Controllers\Tenant\API\Auth\Token;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\Auth\RefreshTokenRequest;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\Auth\Authentication\User as TenantUser;
use App\Services\Central\Auth\Token\PassportTokenService;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;

class TokenController extends Controller
{
    public function __construct(
        private readonly PassportTokenService $tokenService,
        private readonly TokenCookieService $cookieService,
    ) {}

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tenantRefreshToken = $request->validated('refresh_token');
        $shouldUseCookies = $this->cookieService->shouldUseCookies($request);

        $centralUserId = null;
        if ($shouldUseCookies) {
            $centralUserId = $this->resolveCentralUserIdFromTenantRefreshToken($tenantRefreshToken);
        }

        $payload = $this->tokenService->refreshTenantToken($tenantRefreshToken);

        if ($shouldUseCookies) {
            $refreshToken = $payload['refresh_token'] ?? null;
            $accessToken = $payload['access_token'] ?? null;
            unset($payload['refresh_token']);
            unset($payload['access_token'], $payload['token_type']);

            $response = response()->json($payload);

            if (is_string($refreshToken) && $refreshToken !== '') {
                $tenantSlug = (string) tenant()->slug;
                $response->headers->setCookie(
                    $this->cookieService->tenantRefreshCookie($refreshToken, $tenantSlug)
                );
            }

            if (is_string($accessToken) && $accessToken !== '') {
                $tenantSlug = (string) tenant()->slug;
                $response->headers->setCookie(
                    $this->cookieService->tenantAccessCookie($accessToken, $tenantSlug)
                );
            }

            $this->refreshCentralSessionCookies($response, $centralUserId);

            return $response;
        }

        return response()->json($payload);
    }

    private function resolveCentralUserIdFromTenantRefreshToken(string $tenantRefreshToken): ?string
    {
        $accessTokenId = Passport::refreshToken()
            ->newQuery()
            ->whereKey($tenantRefreshToken)
            ->value('access_token_id');

        if (! is_string($accessTokenId) || $accessTokenId === '') {
            return null;
        }

        $accessToken = Passport::token()
            ->newQuery()
            ->whereKey($accessTokenId)
            ->first(['user_id', 'impersonator_central_user_id']);

        if (! $accessToken) {
            return null;
        }

        $impersonatorCentralUserId = $accessToken->impersonator_central_user_id;
        if (is_string($impersonatorCentralUserId) && $impersonatorCentralUserId !== '') {
            return $impersonatorCentralUserId;
        }

        $tenantUserId = $accessToken->user_id;
        if (! is_string($tenantUserId) || $tenantUserId === '') {
            return null;
        }

        $tenantUserGlobalId = TenantUser::query()
            ->whereKey($tenantUserId)
            ->value('global_id');

        if (! is_string($tenantUserGlobalId) || $tenantUserGlobalId === '') {
            return null;
        }

        return tenancy()->central(function () use ($tenantUserGlobalId): ?string {
            $centralUserId = CentralUser::query()
                ->where('global_id', $tenantUserGlobalId)
                ->value('id');

            return is_string($centralUserId) && $centralUserId !== ''
                ? $centralUserId
                : null;
        });
    }

    private function refreshCentralSessionCookies(JsonResponse $response, ?string $centralUserId): void
    {
        if (! is_string($centralUserId) || $centralUserId === '') {
            return;
        }

        try {
            $centralTokenPayload = tenancy()->central(function () use ($centralUserId): ?array {
                $centralUser = CentralUser::query()->find($centralUserId);
                if (! $centralUser) {
                    return null;
                }

                return $this->tokenService->issueCentralTokensForUser($centralUser);
            });

            if (! is_array($centralTokenPayload)) {
                return;
            }

            $centralRefreshToken = $centralTokenPayload['refresh_token'] ?? null;
            $centralAccessToken = $centralTokenPayload['access_token'] ?? null;

            if (is_string($centralRefreshToken) && $centralRefreshToken !== '') {
                $response->headers->setCookie(
                    $this->cookieService->centralRefreshCookie($centralRefreshToken)
                );
            }

            if (is_string($centralAccessToken) && $centralAccessToken !== '') {
                $response->headers->setCookie(
                    $this->cookieService->centralAccessCookie($centralAccessToken)
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Unable to refresh central session during tenant refresh.', [
                'central_user_id' => $centralUserId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
