<?php

namespace App\Services\Central\Auth\Passkey;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Services\Central\Auth\Password\PasswordConfirmationService;
use App\Services\Concerns\EnsuresVerifiedEmail;
use Laragear\WebAuthn\Models\WebAuthnCredential;

class PasskeyService
{
    use EnsuresVerifiedEmail;

    public function __construct(
        private readonly PasswordConfirmationService $passwordConfirmationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listPasskeys(CentralUser $user): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage passkeys.');

        $passkeys = $user->webAuthnCredentials()
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (WebAuthnCredential $credential): array => [
                'id' => $credential->id,
                'alias' => $credential->alias,
                'origin' => $credential->origin,
                'aaguid' => $credential->aaguid,
                'attestation_format' => $credential->attestation_format,
                'disabled_at' => $credential->disabled_at,
                'created_at' => $credential->created_at,
            ]);

        return [
            'passkeys' => $passkeys,
        ];
    }

    public function revokePasskey(CentralUser $user, string $credentialId): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage passkeys.');

        $credential = $user->webAuthnCredentials()->whereKey($credentialId)->firstOrFail();
        $credential->disable();

        return ['message' => 'Passkey revoked.'];
    }

    public function ensureRecentPasswordConfirmation(CentralUser $user): void
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to manage passkeys.');
        $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);
    }
}
