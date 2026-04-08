<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Tenant\Activity\DTOs\ActivityContext;
use App\Services\Tenant\Activity\Support\ActivityContextStore;
use App\Services\Tenant\Activity\Support\KnownMorphTypes;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureActivityContext
{
    public function __construct(
        private readonly KnownMorphTypes $knownTypes,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = trim((string) ($request->headers->get('X-Request-Id') ?? $request->headers->get('X-Request-ID') ?? ''));

        if ($requestId === '') {
            $requestId = 'req_'.Str::ulid()->toBase32();
        }

        $actorType = null;
        $actorId = null;
        $actorLabel = null;
        $user = $request->user();

        if ($user instanceof Model) {
            $actorType = $this->knownTypes->typeForModel($user) ?? 'tenant_user';
            $actorId = (string) $user->getKey();
            $actorLabel = trim((string) (data_get($user, 'name') ?? data_get($user, 'email') ?? ''));
            $actorLabel = $actorLabel !== '' ? $actorLabel : $actorId;
        }

        $context = new ActivityContext(
            actorType: $actorType,
            actorId: $actorId,
            actorLabel: $actorLabel,
            origin: 'api',
            requestId: $requestId,
            originMetadata: [
                'method' => $request->method(),
                'path' => $request->path(),
            ],
        );

        return ActivityContextStore::runWith($context, static fn (): Response => $next($request));
    }
}
