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

        if ($debug) {
            Log::debug('Cookie auth middleware entered', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        if ($request->headers->has('Authorization')) {
            if ($debug) {
                Log::debug('Cookie auth skipped; Authorization header present', [
                    'path' => $request->path(),
                ]);
            }

            return $next($request);
        }

        $cookieService = app(TokenCookieService::class);
        if (! $cookieService->shouldUseCookies($request)) {
            if ($debug) {
                Log::debug('Cookie auth skipped; shouldUseCookies=false', [
                    'path' => $request->path(),
                    'auth_mode' => $request->header('X-Auth-Mode'),
                ]);
            }

            return $next($request);
        }

        $tenantSlug = $request->route('tenant');
        $cookieName = $tenantSlug
            ? $cookieService->tenantAccessCookieName()
            : $cookieService->centralAccessCookieName();

        $token = $request->cookie($cookieName);
        if ($debug) {
            Log::debug('Cookie auth lookup', [
                'path' => $request->path(),
                'auth_mode' => $request->header('X-Auth-Mode'),
                'cookie_name' => $cookieName,
                'has_cookie' => is_string($token) && $token !== '',
                'cookie_length' => is_string($token) ? strlen($token) : null,
                'cookie_names' => array_keys($request->cookies->all()),
                'header_names' => array_keys($request->headers->all()),
                'bearer_token' => $request->bearerToken(),
            ]);
        }
        if (is_string($token) && $token !== '') {
            $request->headers->set('Authorization', 'Bearer '.$token);
            if ($debug) {
                Log::debug('Cookie auth injected Authorization header', [
                    'path' => $request->path(),
                ]);
            }
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
