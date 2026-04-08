<?php

namespace App\Http\Requests\Shared\Auth;

use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('refresh_token')) {
            return;
        }

        $cookieService = app(TokenCookieService::class);
        if (! $cookieService->shouldUseCookies($this)) {
            return;
        }

        $cookieName = tenancy()->initialized
            ? $cookieService->tenantRefreshCookieName()
            : $cookieService->centralRefreshCookieName();

        $cookieValue = $this->cookie($cookieName);

        if (is_string($cookieValue) && $cookieValue !== '') {
            $this->merge(['refresh_token' => $cookieValue]);
        }
    }

    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }
}
