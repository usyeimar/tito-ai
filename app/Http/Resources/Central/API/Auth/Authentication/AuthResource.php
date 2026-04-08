<?php

namespace App\Http\Resources\Central\API\Auth\Authentication;

use App\Http\Resources\Central\API\Tenancy\TenantResource;
use App\Services\Central\Auth\Token\TokenCookieService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $kind = $this->resource['kind'] ?? 'auth';
        $basePayload = [
            'user' => UserResource::make($this->resource['user']),
            'tenants' => TenantResource::collection($this->resolveTenants()),
            'email_verification_required' => $this->emailVerificationRequired(),
        ];

        $verificationPayload = $this->verificationPayload();

        if ($kind === 'tfa_required') {
            return [
                ...$basePayload,
                'tfa_required' => true,
                'tfa_token' => $this->resource['tfa_token'],
                ...$verificationPayload,
            ];
        }

        return [
            ...$basePayload,
            'tfa_required' => false,
            ...$this->tokenPayload($request),
            ...$verificationPayload,
        ];
    }

    public function withResponse(Request $request, Response $response): void
    {
        if (! array_key_exists('access_token', $this->resource)) {
            return;
        }

        $cookieService = app(TokenCookieService::class);
        if (! $cookieService->shouldUseCookies($request)) {
            return;
        }

        $response->headers->setCookie(
            $cookieService->centralAccessCookie($this->resource['access_token'])
        );

        if (! array_key_exists('refresh_token', $this->resource)) {
            return;
        }

        $response->headers->setCookie(
            $cookieService->centralRefreshCookie($this->resource['refresh_token'])
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenPayload(Request $request): array
    {
        if (! array_key_exists('access_token', $this->resource)) {
            if (array_key_exists('expires_in', $this->resource) && $this->resource['expires_in'] !== null) {
                return [
                    'expires_in' => $this->resource['expires_in'],
                ];
            }

            return [];
        }

        $cookieService = app(TokenCookieService::class);
        if ($cookieService->shouldUseCookies($request)) {
            $payload = [];

            if (array_key_exists('expires_in', $this->resource) && $this->resource['expires_in'] !== null) {
                $payload['expires_in'] = $this->resource['expires_in'];
            }

            return $payload;
        }

        $payload = [
            'access_token' => $this->resource['access_token'],
            'token_type' => $this->resource['token_type'] ?? 'Bearer',
        ];

        if (array_key_exists('refresh_token', $this->resource)) {
            $payload['refresh_token'] = $this->resource['refresh_token'];
        }

        if (array_key_exists('expires_in', $this->resource) && $this->resource['expires_in'] !== null) {
            $payload['expires_in'] = $this->resource['expires_in'];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function verificationPayload(): array
    {
        $payload = [];

        if (array_key_exists('verification_sent', $this->resource)) {
            $payload['verification_sent'] = (bool) $this->resource['verification_sent'];
        }

        return $payload;
    }

    private function emailVerificationRequired(): bool
    {
        return (bool) ($this->resource['email_verification_required'] ?? false);
    }

    private function resolveTenants(): mixed
    {
        if (array_key_exists('tenants', $this->resource)) {
            return $this->resource['tenants'];
        }

        $user = $this->resource['user'] ?? null;

        if ($user && method_exists($user, 'tenants')) {
            return $user->tenants()->get();
        }

        return [];
    }
}
