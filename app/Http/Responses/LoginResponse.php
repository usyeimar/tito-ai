<?php

namespace App\Http\Responses;

use App\Actions\Fortify\AuthenticateUser;
use App\Http\Middleware\HydrateCentralAuth;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private readonly AuthenticateUser $authenticator,
        private readonly TokenCookieService $cookieService,
    ) {}

    /**
     * Create an HTTP response for the login request.
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        if (! $user) {
            $redirect = $this->getRedirectUrl($request);

            return redirect()->to($redirect);
        }

        $tokens = $this->authenticator->authenticate($request, $user);

        $redirect = $this->getRedirectUrl($request);

        $response = redirect()->to($redirect);

        if ($user instanceof CentralUser) {
            $response->headers->setCookie(HydrateCentralAuth::cookie($user));
        }

        if ($this->cookieService->shouldUseCookies($request)) {
            $response->headers->setCookie(
                $this->cookieService->centralAccessCookie($tokens['access_token'])
            );

            $response->headers->setCookie(
                $this->cookieService->centralRefreshCookie($tokens['refresh_token'])
            );
        }

        return $response;
    }

    private function getRedirectUrl(Request $request): string
    {
        if ($request->session()->has('url.intended')) {
            return $request->session()->pull('url.intended');
        }

        return Fortify::redirects('login') ?? '/';
    }
}
