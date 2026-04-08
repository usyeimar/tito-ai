<?php

namespace App\Http\Controllers\Central\API\Auth\Passkey;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\API\Auth\Authentication\AuthResource;
use App\Services\Central\Auth\Authentication\AuthService;
use Illuminate\Contracts\Support\Responsable;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

class PasskeyLoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function getOptions(AssertionRequest $request): Responsable
    {
        $request->validate([
            'email' => ['sometimes', 'email'],
        ]);

        return $request
            ->secureLogin()
            ->toVerify($request->only('email'));
    }

    public function login(AssertedRequest $request): AuthResource
    {
        $request->validate([
            'device_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $user = $request->login();

        abort_unless($user, 422, 'Passkey authentication failed.');

        $authPayload = $this->authService->authenticate(
            $request,
            $user,
            array_key_exists('device_name', $request->all())
                ? (string) $request->input('device_name')
                : null
        );

        return AuthResource::make([
            'kind' => 'auth',
            'user' => $user,
            ...$authPayload,
            'tenants' => $user->tenants()->get(),
        ]);
    }
}
