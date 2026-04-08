<?php

namespace App\Http\Controllers\Central\API\Auth\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\Authentication\LoginRequest;
use App\Http\Requests\Central\API\Auth\Authentication\RegisterRequest;
use App\Http\Resources\Central\API\Auth\Authentication\AuthResource;
use App\Services\Central\Auth\Authentication\AuthService;
use App\Services\Central\Auth\Token\TokenCookieService;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly TokenCookieService $cookieService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request, $request->validated());

        return AuthResource::make($result)
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): AuthResource
    {
        $result = $this->authService->login($request, $request->validated());

        return AuthResource::make($result);
    }

    public function me(Request $request): AuthResource
    {
        $user = $request->user();
        $expiresIn = $this->resolveExpiresIn($request);

        return AuthResource::make([
            'kind' => 'auth',
            'user' => $user,
            'tenants' => $user?->tenants()->get() ?? [],
            'email_verification_required' => $user && ! $user->hasVerifiedEmail(),
            'tfa_required' => false,
            'expires_in' => $expiresIn,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->logout($request);

        $response = response()->json($result);

        if ($this->cookieService->shouldUseCookies($request)) {
            $response->headers->setCookie(
                $this->cookieService->forgetCentralAccessCookie()
            );
            $response->headers->setCookie(
                $this->cookieService->forgetCentralRefreshCookie()
            );
        }

        return $response;
    }

    private function resolveExpiresIn(Request $request): ?int
    {
        $fallback = max(1, (int) config('passport_tokens.access_ttl_minutes', 60)) * 60;

        $token = $request->user()?->token();
        if (! $token) {
            return $fallback;
        }

        $expiresAt = $token->expires_at ?? null;
        if ($expiresAt instanceof CarbonInterface) {
            return max(0, now()->diffInSeconds($expiresAt, false));
        }

        if (is_string($expiresAt) && $expiresAt !== '') {
            return max(0, now()->diffInSeconds(now()->parse($expiresAt), false));
        }

        return $fallback;
    }
}
