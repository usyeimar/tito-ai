<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies runner webhook requests using HMAC-SHA256 signature.
 *
 * Expected headers:
 *   X-Tito-Signature: sha256=<hex-hmac>
 *   X-Tito-Timestamp: <unix-timestamp>
 *
 * Falls back to simple API key check (X-Tito-Agent-Key) for backward compatibility.
 */
class VerifyRunnerSignature
{
    private const MAX_TIMESTAMP_DRIFT_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('runners.api_key');

        // No secret configured = skip verification (dev mode)
        if (empty($secret)) {
            return $next($request);
        }

        // Try HMAC signature first
        $signature = $request->header('X-Tito-Signature');
        $timestamp = $request->header('X-Tito-Timestamp');

        if ($signature && $timestamp) {
            if (! $this->verifyHmac($signature, $timestamp, $request->getContent(), $secret)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            return $next($request);
        }

        // Fallback: simple API key
        if ($request->header('X-Tito-Agent-Key') === $secret) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    private function verifyHmac(string $signature, string $timestamp, string $body, string $secret): bool
    {
        // Reject stale timestamps to prevent replay attacks
        if (abs(time() - (int) $timestamp) > self::MAX_TIMESTAMP_DRIFT_SECONDS) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $timestamp.$body, $secret);

        return hash_equals($expected, $signature);
    }
}
