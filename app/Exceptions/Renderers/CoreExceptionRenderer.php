<?php

namespace App\Exceptions\Renderers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CoreExceptionRenderer extends ApiErrorRenderer
{
    private static array $formPointerRoutes = [
        'api/auth/login',
    ];

    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                401,
                'UNAUTHENTICATED',
                'Unauthenticated',
                $e->getMessage() ?: 'Unauthenticated.',
            );
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                403,
                'INVALID_SIGNATURE',
                'Invalid Signature',
                'This link is invalid or has expired.',
            );
        });

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            return self::singleErrorResponse(
                413,
                'PAYLOAD_TOO_LARGE',
                'Payload Too Large',
                'Request payload is too large.',
            );
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            $model = null;

            if ($e instanceof ModelNotFoundException) {
                $model = $e->getModel();
            } elseif ($e instanceof NotFoundHttpException) {
                $modelNotFound = $e->getPrevious();
                if ($modelNotFound instanceof ModelNotFoundException) {
                    $model = $modelNotFound->getModel();
                }
            }

            return self::singleErrorResponse(
                404,
                'NOT_FOUND',
                'Not Found',
                class_exists($model) ? class_basename($model).' not found.' : 'Resource not found.',
            );
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            $errors = [];
            $useFormPointer = self::shouldUseFormValidationPointer($request);

            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'status' => 422,
                        'code' => 'VALIDATION_ERROR',
                        'title' => 'Validation Error',
                        'detail' => $message,
                        'source' => [
                            'pointer' => $useFormPointer ? '_form' : $field,
                        ],
                    ];
                }
            }

            return response()->json(['errors' => $errors], 422);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! self::shouldRenderJson($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            $title = Response::$statusTexts[$status] ?? 'Error';
            $code = strtoupper(str_replace(' ', '_', $title));

            return self::singleErrorResponse(
                $status,
                $code,
                $title,
                $e->getMessage() ?: $title.'.',
            );
        });
    }

    private static function shouldUseFormValidationPointer(Request $request): bool
    {
        return $request->is(self::$formPointerRoutes) && $request->isMethod('post');
    }
}
