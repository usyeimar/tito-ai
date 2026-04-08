<?php

namespace App\Http\Middleware;

use App\Models\Central\Audit\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class AuditTenantRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $tenantUser = $request->user();
        $token = $tenantUser?->token();
        $impersonatorId = $token instanceof Token
            ? $token->impersonator_central_user_id
            : null;
        if (! $impersonatorId) {
            return $response;
        }

        $payload = $this->payloadForAudit($request);

        AuditLog::create([
            'tenant_id' => tenant('id'),
            'actor_central_user_id' => $impersonatorId,
            'tenant_user_id' => $tenantUser?->id,
            'route' => $request->route()?->getName(),
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'query' => $request->query(),
                'payload' => $payload,
            ],
        ]);

        return $response;
    }

    private function payloadForAudit(Request $request): array
    {
        if (! $request->isMethodSafe()) {
            $input = $request->except([
                'password',
                'password_confirmation',
                'current_password',
                'token',
                'access_token',
                'refresh_token',
                'code',
                'state',
                'code_verifier',
            ]);

            return $this->truncatePayload($input);
        }

        return [];
    }

    private function truncatePayload(array $payload, int $maxBytes = 4096): array
    {
        $encoded = json_encode($payload);
        if ($encoded === false || strlen($encoded) <= $maxBytes) {
            return $payload;
        }

        $truncated = substr($encoded, 0, $maxBytes);

        return [
            '_truncated' => true,
            'data' => $truncated,
        ];
    }
}
