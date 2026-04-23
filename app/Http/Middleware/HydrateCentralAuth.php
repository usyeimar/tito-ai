<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Token\TokenCookieService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restores the central 'web' guard session from an encrypted cookie.
 *
 * When a user enters a tenant workspace via impersonation, the session is
 * stored in the tenant database. Navigating back to central routes
 * (e.g. /workspaces) would appear unauthenticated because the central
 * database has no record of the session.
 *
 * This middleware checks for a `central_auth_user` cookie (set during login)
 * and re-authenticates the user on the 'web' guard when the session is empty.
 * It also ensures the cookie stays fresh for already-authenticated users.
 */
class HydrateCentralAuth
{
    public const COOKIE_NAME = 'central_auth_user';

    /** Cookie lifetime in minutes (30 days). */
    public const COOKIE_LIFETIME = 43200;

    public function handle(Request $request, Closure $next): Response
    {
        $debug = app()->environment('local');
        $accessCookieName = app(TokenCookieService::class)->centralAccessCookieName();

        if ($debug) {
            Log::debug('HydrateCentralAuth entered', [
                'path' => $request->path(),
                'web_check' => auth('web')->check(),
                'has_central_auth_user' => (bool) $request->cookie(self::COOKIE_NAME),
                'has_central_access' => (bool) $request->cookie($accessCookieName),
                'cookies' => array_keys($request->cookies->all()),
            ]);
        }

        // Already authenticated — ensure cookie is set for future cross-context visits.
        if (auth('web')->check()) {
            $user = auth('web')->user();

            if ($user instanceof CentralUser && ! $request->cookie(self::COOKIE_NAME)) {
                Cookie::queue(self::cookie($user));
            }

            return $next($request);
        }

        // Not authenticated — try to restore from cookie.
        $userId = $request->cookie(self::COOKIE_NAME);

        if (is_string($userId) && $userId !== '') {
            $user = CentralUser::find($userId);

            if ($user) {
                auth('web')->login($user);
                Cookie::queue(self::cookie($user));

                if ($debug) {
                    Log::debug('HydrateCentralAuth restored from central_auth_user', [
                        'user_id' => $user->getKey(),
                    ]);
                }

                return $next($request);
            }

            Cookie::queue(Cookie::forget(self::COOKIE_NAME));

            if ($debug) {
                Log::debug('HydrateCentralAuth cookie pointed to missing user', [
                    'raw_cookie_len' => strlen($userId),
                ]);
            }
        }

        // Fallback: resolve user from the central access token cookie.
        $user = $this->userFromAccessCookie($request);
        if ($user instanceof CentralUser) {
            auth('web')->login($user);
            Cookie::queue(self::cookie($user));

            if ($debug) {
                Log::debug('HydrateCentralAuth restored from access_token cookie', [
                    'user_id' => $user->getKey(),
                ]);
            }

            return $next($request);
        }

        if ($debug) {
            Log::debug('HydrateCentralAuth could not restore', [
                'path' => $request->path(),
            ]);
        }

        return $next($request);
    }

    private function userFromAccessCookie(Request $request): ?CentralUser
    {
        $cookieName = app(TokenCookieService::class)->centralAccessCookieName();
        $accessToken = $request->cookie($cookieName);

        if (! is_string($accessToken) || $accessToken === '') {
            return null;
        }

        try {
            $jwt = (new Parser(new JoseEncoder))->parse($accessToken);
            $tokenId = $jwt->claims()->get('jti');
        } catch (\Throwable) {
            return null;
        }

        if (! is_string($tokenId) || $tokenId === '') {
            return null;
        }

        $token = Token::query()
            ->where('id', $tokenId)
            ->where('revoked', false)
            ->first();

        if (! $token || ! $token->user_id) {
            return null;
        }

        return CentralUser::find($token->user_id);
    }

    /**
     * Create the central auth cookie for the given user.
     */
    public static function cookie(CentralUser $user): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            self::COOKIE_NAME,
            (string) $user->getKey(),
            self::COOKIE_LIFETIME,
            '/',
            null,
            null,
            true,
        );
    }
}
