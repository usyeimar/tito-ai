<?php

namespace App\Http\Controllers\Central\Web\Tenancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Tenancy\StoreTenantRequest;
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

    public function store(StoreTenantRequest $request)
    {
        $this->tenantService->createForUser(
            $request->user(),
            $request->validated(),
        );

        return redirect()->route('workspaces');
    }

    public function enter(Request $request, \App\Models\Central\Tenancy\Tenant $tenant)
    {
        $user = $request->user();

        if (! $user->tenants()->whereKey($tenant->getKey())->exists()) {
            abort(403, 'You do not have access to this workspace.');
        }

        $tenantUserId = $tenant->run(fn () => \App\Models\Tenant\Auth\Authentication\User::query()
            ->where('global_id', $user->global_id)
            ->where('is_active', true)
            ->value('id'));

        if (! $tenantUserId) {
            abort(403, 'Your tenant account is inactive or unavailable. Please contact your workspace administrator.');
        }

        $redirectUrl = route('tenant.dashboard', ['tenant' => $tenant->slug]);
        
        $token = tenancy()->impersonate($tenant, (string) $tenantUserId, $redirectUrl, 'tenant');

        return redirect()->route('tenant.impersonate', [
            'tenant' => $tenant->slug,
            'token' => $token->token
        ]);
    }
}
