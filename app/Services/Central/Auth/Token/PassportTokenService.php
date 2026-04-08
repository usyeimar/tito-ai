<?php

namespace App\Services\Central\Auth\Token;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PassportTokenService
{
    public function __construct(
        private readonly AccessTokenController $accessTokenController,
        private readonly ServerRequestInterface $serverRequest,
    ) {}

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    public function issueCentralTokensWithPassword(string $email, string $password): array
    {
        return $this->issueToken([
            'grant_type' => 'password',
            'client_id' => $this->centralClientId(),
            'client_secret' => $this->centralClientSecret(),
            'username' => $email,
            'password' => $password,
            'scope' => '',
        ]);
    }

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    public function issueCentralTokensForUser(CentralUser $user): array
    {
        $loginToken = $user->createPassportLoginToken();

        return $this->issueCentralTokensWithPassword($user->email, $loginToken);
    }

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    public function refreshCentralToken(string $refreshToken): array
    {
        return $this->issueToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->centralClientId(),
            'client_secret' => $this->centralClientSecret(),
            'scope' => '',
        ]);
    }

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    public function issueTenantTokensFromImpersonation(string $impersonationToken): array
    {
        return $this->issueToken([
            'grant_type' => 'impersonation_token',
            'impersonation_token' => $impersonationToken,
            'client_id' => $this->tenantClientId(),
            'client_secret' => $this->tenantClientSecret(),
            'scope' => '',
        ]);
    }

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    public function refreshTenantToken(string $refreshToken): array
    {
        return $this->issueToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->tenantClientId(),
            'client_secret' => $this->tenantClientSecret(),
            'scope' => '',
        ]);
    }

    /**
     * @return array{access_token:string,refresh_token:string,expires_in:int,token_type:string}
     */
    private function issueToken(array $payload): array
    {
        $request = $this->serverRequest->withParsedBody($payload);
        $response = $this->accessTokenController->issueToken(
            $request,
            app(ResponseInterface::class),
        );

        $body = (string) $response->getContent();
        $data = json_decode($body, true);

        $status = $response->getStatusCode();
        if ($status >= 400) {
            $message = is_array($data)
                ? (string) ($data['error_description'] ?? $data['error'] ?? 'Unable to issue access token.')
                : 'Unable to issue access token.';

            throw ValidationException::withMessages([
                'token' => [$message],
            ]);
        }

        if (! is_array($data) || ! isset($data['access_token'], $data['refresh_token'])) {
            throw ValidationException::withMessages([
                'token' => ['Unable to issue access token.'],
            ]);
        }

        return Arr::only($data, ['access_token', 'refresh_token', 'expires_in', 'token_type']);
    }

    private function centralClientId(): string
    {
        return $this->requiredConfig('passport_clients.central.client_id');
    }

    private function centralClientSecret(): string
    {
        return $this->requiredConfig('passport_clients.central.client_secret');
    }

    private function tenantClientId(): string
    {
        return $this->requiredConfig('passport_clients.tenant.client_id');
    }

    private function tenantClientSecret(): string
    {
        return $this->requiredConfig('passport_clients.tenant.client_secret');
    }

    /**
     * @return array<string, mixed>
     */
    public function listTokens(CentralUser $user): array
    {
        $tokens = $user->tokens()
            ->where('revoked', false)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'created_at', 'expires_at', 'revoked']);

        return [
            'tokens' => $tokens,
        ];
    }

    public function revokeTokens(CentralUser $user): array
    {
        $user->tokens()->each(function ($token): void {
            $token->revoke();
            $token->refreshToken?->revoke();
        });

        return ['message' => 'Tokens revoked.'];
    }

    public function revokeToken(CentralUser $user, string $tokenId): array
    {
        $token = $user->tokens()->whereKey($tokenId)->firstOrFail();
        $token->revoke();
        $token->refreshToken?->revoke();

        return ['message' => 'Token revoked.'];
    }

    private function requiredConfig(string $key): string
    {
        $value = (string) config($key);
        if ($value === '') {
            throw ValidationException::withMessages([
                'token' => ["Missing config value [{$key}]."],
            ]);
        }

        return $value;
    }
}
