<?php

namespace App\Support\Passport;

use App\Models\Central\Tenancy\ImpersonationToken;
use App\Models\Tenant\Auth\Authentication\User as TenantUser;
use DateInterval;
use Illuminate\Support\Carbon;
use Laravel\Passport\Token as PassportToken;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class ImpersonationTokenGrant extends AbstractGrant
{
    public function __construct(
        RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
    }

    public function getIdentifier(): string
    {
        return 'impersonation_token';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL,
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

        $tokenValue = (string) $this->getRequestParameter('impersonation_token', $request);
        if ($tokenValue === '') {
            throw OAuthServerException::invalidRequest('impersonation_token');
        }

        $impersonationToken = ImpersonationToken::query()->find($tokenValue);
        if (! $impersonationToken) {
            throw OAuthServerException::invalidGrant('Impersonation token not found.');
        }

        $ttlSeconds = (int) config('tenancy.impersonation_ttl', 60);
        $createdAt = $impersonationToken->created_at;
        if (is_string($createdAt)) {
            $createdAt = Carbon::parse($createdAt);
        }

        if (! $createdAt instanceof \DateTimeInterface) {
            $impersonationToken->delete();
            throw OAuthServerException::invalidGrant('Impersonation token missing timestamp.');
        }

        if ($createdAt->addSeconds($ttlSeconds)->isPast()) {
            $impersonationToken->delete();
            throw OAuthServerException::invalidGrant('Impersonation token expired.');
        }

        if (! tenant() || (string) tenant()->getTenantKey() !== (string) $impersonationToken->tenant_id) {
            throw OAuthServerException::accessDenied('Unauthorized tenant context.');
        }

        if (! in_array($impersonationToken->auth_guard, ['tenant', 'tenant-api'], true)) {
            $impersonationToken->delete();
            throw OAuthServerException::invalidGrant('Impersonation token guard mismatch.');
        }

        $user = TenantUser::query()->find($impersonationToken->user_id);
        if (! $user) {
            $impersonationToken->delete();
            throw OAuthServerException::invalidGrant('Impersonation user not found.');
        }

        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $user->getAuthIdentifier(),
            $scopes,
        );

        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        $this->storeImpersonator($accessToken->getIdentifier(), $impersonationToken->impersonator_central_user_id);

        $impersonationToken->delete();

        return $responseType;
    }

    private function storeImpersonator(string $tokenId, ?string $impersonatorId): void
    {
        if (! $impersonatorId) {
            return;
        }

        PassportToken::query()->whereKey($tokenId)->update([
            'impersonator_central_user_id' => $impersonatorId,
        ]);
    }
}
