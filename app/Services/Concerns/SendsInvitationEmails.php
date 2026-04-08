<?php

namespace App\Services\Concerns;

use App\Mail\Tenant\TenantInvitationMail;
use App\Models\Central\Tenancy\TenantInvitation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait SendsInvitationEmails
{
    private const int TOKEN_LENGTH = 64;

    protected function sendInvitationEmail(TenantInvitation $invitation, string $token): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'auth.invitations.resolve',
            $invitation->expires_at,
            ['token' => $token],
        );

        $query = parse_url($signedUrl, PHP_URL_QUERY) ?? '';
        $frontend = rtrim((string) config('app.frontend_url'), '/');
        $acceptUrl = "{$frontend}/workspaces/invitations/accept?{$query}";

        Mail::to($invitation->email)->send(new TenantInvitationMail($invitation, $invitation->tenant, $acceptUrl));
    }

    protected function generateToken(): string
    {
        return Str::random(self::TOKEN_LENGTH);
    }

    protected function expiresAt(): CarbonImmutable
    {
        $ttlDays = (int) config('invitations.ttl_days', 7);

        return CarbonImmutable::now()->addDays(max($ttlDays, 1));
    }
}
