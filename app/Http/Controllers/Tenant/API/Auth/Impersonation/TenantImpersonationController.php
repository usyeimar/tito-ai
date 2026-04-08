<?php

namespace App\Http\Controllers\Tenant\API\Auth\Impersonation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\API\Auth\Authentication\TenantUserResource;
use App\Models\Central\Tenancy\ImpersonationToken;
use App\Models\Tenant\Auth\Authentication\User;
use App\Services\Central\Auth\Token\PassportTokenService;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Token;

class TenantImpersonationController extends Controller
{
    public function __construct(
        private readonly PassportTokenService $tokenService,
        private readonly TokenCookieService $cookieService,
    ) {}

    public function start(Request $request, ?string $token = null): JsonResponse
    {
        $tokenValue = $token ?? (string) $request->input('impersonation_token');
        if (! is_string($tokenValue) || $tokenValue === '') {
            abort(422, 'Impersonation token is required.');
        }

        $impersonationToken = ImpersonationToken::findOrFail($tokenValue);
        $redirectUrl = $impersonationToken->redirect_url;

        $user = User::query()
            ->with(['roles', 'profilePicture'])
            ->findOrFail($impersonationToken->user_id);
        if (! $user->is_active) {
            abort(403, 'Your access to this workspace has been disabled. Please contact your administrator.');
        }

        $tokenPayload = $this->tokenService->issueTenantTokensFromImpersonation($tokenValue);

        $data = [
            'user' => new TenantUserResource($user),
            'tenant_slug' => (string) tenant()->slug,
            'redirect_url' => $redirectUrl,
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'],
            'expires_in' => $tokenPayload['expires_in'],
            'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
        ];

        $response = response()->json([
            'message' => 'Impersonation successful.',
            'data' => $data,
        ]);

        if ($this->cookieService->shouldUseCookies($request)) {
            $refreshToken = $tokenPayload['refresh_token'] ?? null;
            $accessToken = $tokenPayload['access_token'] ?? null;
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

            unset($data['refresh_token']);
            unset($data['access_token'], $data['token_type']);
            $response->setData([
                'message' => 'Impersonation successful.',
                'data' => $data,
            ]);
        }

        return $response;
    }

    public function end(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $token = $user->token();
            if ($token instanceof Token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            }
        }

        $response = response()->json([
            'message' => 'Impersonation ended.',
        ]);

        if ($this->cookieService->shouldUseCookies($request)) {
            $tenantSlug = (string) tenant()->slug;
            $response->headers->setCookie(
                $this->cookieService->forgetTenantAccessCookie($tenantSlug)
            );
            $response->headers->setCookie(
                $this->cookieService->forgetTenantRefreshCookie($tenantSlug)
            );
        }

        return $response;
    }
}
