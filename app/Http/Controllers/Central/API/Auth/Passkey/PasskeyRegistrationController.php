<?php

namespace App\Http\Controllers\Central\API\Auth\Passkey;

use App\Http\Controllers\Controller;
use App\Services\Central\Auth\Passkey\PasskeyService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class PasskeyRegistrationController extends Controller
{
    public function __construct(
        private readonly PasskeyService $passkeyService,
    ) {}

    public function options(AttestationRequest $request): Responsable
    {
        $this->passkeyService->ensureVerifiedEmail(
            $request->user(),
            'Email verification is required to manage passkeys.'
        );

        return $request
            ->userless()
            ->secureRegistration()
            ->toCreate();
    }

    public function store(AttestedRequest $request): JsonResponse
    {
        $request->validate([
            'alias' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $this->passkeyService->ensureRecentPasswordConfirmation($request->user());

        $request->save($request->only('alias'));

        return response()->json([
            'message' => 'Passkey registered.',
        ]);
    }
}
