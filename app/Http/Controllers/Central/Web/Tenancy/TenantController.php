<?php

namespace App\Http\Controllers\Central\Web\Tenancy;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\API\Tenancy\TenantResource;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Services\Central\Tenancy\TenantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $tenants = $this->tenantService->listForUser($user);

        $invitations = TenantInvitation::query()
            ->where('email', $user->email)
            ->pending()
            ->with('tenant')
            ->get();

        return Inertia::render('workspaces/index', [
            'workspaces' => collect($tenants->items())->map(fn ($tenant) => TenantResource::make($tenant)->resolve()),
            'appUrl' => config('app.url'),
            'invitations' => $invitations->map(fn (TenantInvitation $invitation) => [
                'id' => $invitation->id,
                'tenant' => [
                    'id' => $invitation->tenant->id,
                    'name' => $invitation->tenant->name,
                    'slug' => $invitation->tenant->slug,
                ],
                'expires_at' => $invitation->expires_at,
            ]),
        ]);
    }
}
