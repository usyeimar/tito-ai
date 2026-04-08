<?php

namespace App\Http\Middleware;

use App\Services\Central\Auth\Token\TokenCookieService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DebugAuthModeCookie
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $cookieService = app(TokenCookieService::class);
        $tenantSlug = $request->route('tenant');
        $accessCookieName = $tenantSlug
            ? $cookieService->tenantAccessCookieName()
            : $cookieService->centralAccessCookieName();
        $refreshCookieName = $tenantSlug
            ? $cookieService->tenantRefreshCookieName()
            : $cookieService->centralRefreshCookieName();

        Log::debug('Auth cookie debug', [
            'path' => $request->path(),
            'method' => $request->method(),
            'auth_mode' => $request->header('X-Auth-Mode'),
            'should_use_cookies' => $cookieService->shouldUseCookies($request),
            'api_middleware' => app('router')->getMiddlewareGroups()['api'] ?? null,
            'access_cookie_name' => $accessCookieName,
            'refresh_cookie_name' => $refreshCookieName,
            'has_access_cookie' => $request->cookies->has($accessCookieName),
            'has_refresh_cookie' => $request->cookies->has($refreshCookieName),
            'cookie_names' => array_keys($request->cookies->all()),
            'header_names' => array_keys($request->headers->all()),
            'headers' => Arr::only($request->headers->all(), [
                'host',
                'origin',
                'referer',
                'x-auth-mode',
                'sec-fetch-mode',
                'sec-fetch-site',
                'user-agent',
                'authorization',
                'cookie',
            ]),
        ]);

        return $next($request);
    }
}
