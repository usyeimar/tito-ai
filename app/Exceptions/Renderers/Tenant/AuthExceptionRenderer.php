<?php

namespace App\Exceptions\Renderers\Tenant;

use App\Exceptions\InvitationLinkInvalidException;
use App\Exceptions\InvitationPendingConflictException;
use App\Exceptions\Renderers\ApiErrorRenderer;
use App\Exceptions\TenantImpersonationUnavailableException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;

final class AuthExceptionRenderer extends ApiErrorRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (TenantImpersonationUnavailableException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                403,
                'TENANT_IMPERSONATION_UNAVAILABLE',
                'Tenant Impersonation Unavailable',
                $e->getMessage(),
            );
        });

        $exceptions->render(function (InvitationPendingConflictException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                409,
                'INVITATION_PENDING_CONFLICT',
                'Invitation Conflict',
                $e->getMessage(),
            );
        });

        $exceptions->render(function (InvitationLinkInvalidException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                410,
                'INVITATION_LINK_INVALID',
                'Invitation Link Invalid',
                $e->getMessage(),
            );
        });

        $exceptions->render(function (OAuthServerException $e, Request $request) {
            return self::renderOAuthException($e, $request);
        });

        $exceptions->render(function (\Laravel\Passport\Exceptions\OAuthServerException $e, Request $request) {
            $leagueException = $e->getPrevious();

            if ($leagueException instanceof OAuthServerException) {
                return self::renderOAuthException($leagueException, $request);
            }

            return null;
        });
    }

    private static function renderOAuthException(OAuthServerException $e, Request $request): ?JsonResponse
    {
        if (! self::shouldRenderJson($request)) {
            return null;
        }

        return self::singleErrorResponse(
            $e->getHttpStatusCode(),
            strtoupper(str_replace('-', '_', $e->getErrorType())),
            ucwords(str_replace(['-', '_'], ' ', $e->getErrorType())),
            $e->getHint() ?: $e->getMessage(),
        );
    }
}
