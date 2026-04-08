<?php

namespace App\Exceptions\Renderers\Tenant;

use App\Exceptions\Renderers\ApiErrorRenderer;
use App\Exceptions\TenantMailNotConfiguredException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

final class SystemExceptionRenderer extends ApiErrorRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (TenantMailNotConfiguredException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                422,
                'TENANT_MAIL_NOT_CONFIGURED',
                'Tenant Mail Not Configured',
                $e->getMessage(),
            );
        });
    }
}
