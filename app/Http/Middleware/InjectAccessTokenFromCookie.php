<?php

namespace App\Http\Middleware;

use App\Services\Central\Auth\Token\TokenCookieService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InjectAccessTokenFromCookie
{
    public function handle(Request $request, Closure $next): mixed
    {
        $debug = app()->environment('local');

        if (! $this->isApiRequest($request)) {
            return $next($request);
        }

        // If Authorization header already present, skip
        if ($request->headers->has('Authorization')) {
            return $next($request);
        }

        $cookieService = app(TokenCookieService::class);
        if (! $cookieService->shouldUseCookies($request)) {
            return $next($request);
        }

        $tenantSlug = $request->route('tenant');
        $cookieName = $tenantSlug
            ? $cookieService->tenantAccessCookieName()
            : $cookieService->centralAccessCookieName();

        $token = $request->cookie($cookieName);

        if (is_string($token) && $token !== '') {
            $request->headers->set('Authorization', 'Bearer '.$token);

            if ($debug) {
                Log::debug('Cookie auth injected Authorization header', [
                    'path' => $request->path(),
                    'cookie' => $cookieName,
                ]);
            }

            return $next($request);
        }

        // No OAuth cookie found — let Passport's TokenGuard try the
        // laravel_token cookie (set by CreateFreshApiToken) on its own.
        // We must NOT inject an empty Authorization header.
        if ($debug) {
            Log::debug('Cookie auth: no OAuth token cookie, falling back to Passport cookie auth', [
                'path' => $request->path(),
            ]);
        }

        return $next($request);
    }

    private function isApiRequest(Request $request): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        $segments = $request->segments();

        return isset($segments[1]) && $segments[1] === 'api';
    }
}
