<?php

namespace App\Http\Middleware;

use App\Services\Central\Auth\Token\TokenCookieService;
use Closure;
use Illuminate\Http\Request;

class EnsureCookieAuthOrigin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->isApiRequest($request)) {
            return $next($request);
        }

        $cookieService = app(TokenCookieService::class);
        if ($cookieService->requestedCookieMode($request) && ! $cookieService->originAllowed($request)) {
            abort(403, 'Cookie auth is not allowed from this origin.');
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
