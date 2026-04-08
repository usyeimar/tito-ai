<?php

namespace App\Services\Central\Auth\Token;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class TokenCookieService
{
    public function shouldUseCookies(Request $request): bool
    {
        if (! (bool) config('passport_tokens.refresh_cookie.enabled', true)) {
            return false;
        }

        if ($this->requestedCookieMode($request)) {
            return $this->originAllowed($request);
        }

        if ($this->requestedTokenMode($request)) {
            return false;
        }

        if (! $this->originAllowed($request)) {
            return false;
        }

        return $this->isBrowserLike($request);
    }

    public function requestedCookieMode(Request $request): bool
    {
        return strtolower((string) $request->header('X-Auth-Mode', '')) === 'cookie';
    }

    public function requestedTokenMode(Request $request): bool
    {
        return strtolower((string) $request->header('X-Auth-Mode', '')) === 'token';
    }

    public function centralAccessCookie(string $accessToken): Cookie
    {
        return $this->makeCookie(
            $this->centralAccessCookieName(),
            $accessToken,
            $this->centralAccessCookiePath(),
            $this->accessCookieMinutes(),
            'access_cookie',
        );
    }

    public function tenantAccessCookie(string $accessToken, string $tenantSlug): Cookie
    {
        return $this->makeCookie(
            $this->tenantAccessCookieName(),
            $accessToken,
            $this->tenantAccessCookiePath($tenantSlug),
            $this->accessCookieMinutes(),
            'access_cookie',
        );
    }

    public function centralRefreshCookie(string $refreshToken): Cookie
    {
        return $this->makeCookie(
            $this->centralRefreshCookieName(),
            $refreshToken,
            $this->centralRefreshCookiePath(),
            $this->refreshCookieMinutes(),
            'refresh_cookie',
        );
    }

    public function tenantRefreshCookie(string $refreshToken, string $tenantSlug): Cookie
    {
        return $this->makeCookie(
            $this->tenantRefreshCookieName(),
            $refreshToken,
            $this->tenantRefreshCookiePath($tenantSlug),
            $this->refreshCookieMinutes(),
            'refresh_cookie',
        );
    }

    public function forgetCentralAccessCookie(): Cookie
    {
        return $this->forgetCookie(
            $this->centralAccessCookieName(),
            $this->centralAccessCookiePath(),
            'access_cookie',
        );
    }

    public function forgetTenantAccessCookie(string $tenantSlug): Cookie
    {
        return $this->forgetCookie(
            $this->tenantAccessCookieName(),
            $this->tenantAccessCookiePath($tenantSlug),
            'access_cookie',
        );
    }

    public function forgetCentralRefreshCookie(): Cookie
    {
        return $this->forgetCookie(
            $this->centralRefreshCookieName(),
            $this->centralRefreshCookiePath(),
            'refresh_cookie',
        );
    }

    public function forgetTenantRefreshCookie(string $tenantSlug): Cookie
    {
        return $this->forgetCookie(
            $this->tenantRefreshCookieName(),
            $this->tenantRefreshCookiePath($tenantSlug),
            'refresh_cookie',
        );
    }

    public function centralAccessCookieName(): string
    {
        return (string) config('passport_tokens.access_cookie.central_name', 'central_access_token');
    }

    public function tenantAccessCookieName(): string
    {
        return (string) config('passport_tokens.access_cookie.tenant_name', 'tenant_access_token');
    }

    public function centralRefreshCookieName(): string
    {
        return (string) config('passport_tokens.refresh_cookie.central_name', 'central_refresh_token');
    }

    public function tenantRefreshCookieName(): string
    {
        return (string) config('passport_tokens.refresh_cookie.tenant_name', 'tenant_refresh_token');
    }

    private function makeCookie(
        string $name,
        string $value,
        string $path,
        int $minutes,
        string $section,
    ): Cookie {
        return cookie(
            $name,
            $value,
            $minutes,
            $path,
            $this->cookieDomain($section),
            $this->cookieSecure($section),
            true,
            false,
            $this->cookieSameSite($section),
        );
    }

    private function forgetCookie(string $name, string $path, string $section): Cookie
    {
        return cookie(
            $name,
            '',
            -1,
            $path,
            $this->cookieDomain($section),
            $this->cookieSecure($section),
            true,
            false,
            $this->cookieSameSite($section),
        );
    }

    private function accessCookieMinutes(): int
    {
        $minutes = (int) config('passport_tokens.access_ttl_minutes', 60);

        return max(1, $minutes);
    }

    private function refreshCookieMinutes(): int
    {
        $days = (int) config('passport_tokens.refresh_ttl_days', 30);

        return max(1, $days) * 24 * 60;
    }

    private function cookieDomain(string $section): ?string
    {
        $domain = config("passport_tokens.{$section}.domain");
        if (is_string($domain) && $domain !== '') {
            return $domain;
        }

        $fallback = config('passport_tokens.refresh_cookie.domain');

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    private function cookieSecure(string $section): ?bool
    {
        $secure = config("passport_tokens.{$section}.secure");
        if (is_bool($secure)) {
            return $secure;
        }

        $fallback = config('passport_tokens.refresh_cookie.secure');

        return is_bool($fallback) ? $fallback : null;
    }

    private function cookieSameSite(string $section): ?string
    {
        $sameSite = config("passport_tokens.{$section}.same_site");
        if (is_string($sameSite) && $sameSite !== '') {
            return $sameSite;
        }

        $fallback = config('passport_tokens.refresh_cookie.same_site');

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    private function centralAccessCookiePath(): string
    {
        return '/api';
    }

    private function tenantAccessCookiePath(string $tenantSlug): string
    {
        $tenantSlug = trim($tenantSlug, '/');

        return "/{$tenantSlug}/api";
    }

    private function centralRefreshCookiePath(): string
    {
        return '/api/auth/refresh';
    }

    private function tenantRefreshCookiePath(string $tenantSlug): string
    {
        $tenantSlug = trim($tenantSlug, '/');

        return "/{$tenantSlug}/api/refresh";
    }

    private function isBrowserLike(Request $request): bool
    {
        if ($request->headers->has('Sec-Fetch-Mode') || $request->headers->has('Sec-Fetch-Site')) {
            return true;
        }

        if ($request->headers->has('Origin')) {
            return true;
        }

        $userAgent = (string) $request->userAgent();

        return $userAgent !== '' && str_contains($userAgent, 'Mozilla/');
    }

    public function originAllowed(Request $request): bool
    {
        $origin = $request->headers->get('Origin');
        if (! is_string($origin) || $origin === '') {
            return true;
        }

        $origin = rtrim($origin, '/');
        $allowed = array_map(
            static fn (string $value): string => rtrim($value, '/'),
            array_filter((array) config('cors.allowed_origins', []))
        );

        return in_array($origin, $allowed, true);
    }
}
