<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WrapApiResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        // Exclude specific URIs if needed via config or middleware parameters
        // Example: if (in_array($request->path(), config('api.response_wrap_exclusions', []))) { return $response; }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return $response;
        }

        $data = $response->getData(true);

        if (! is_array($data)) {
            return $response;
        }

        if (array_key_exists('data', $data) || array_key_exists('errors', $data)) {
            return $response;
        }

        $wrapped = response()->json(['data' => $data], $status, $response->headers->all());

        foreach ($response->headers->getCookies() as $cookie) {
            $wrapped->headers->setCookie($cookie);
        }

        return $wrapped;
    }
}
