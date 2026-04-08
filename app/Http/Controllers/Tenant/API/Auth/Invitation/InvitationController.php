<?php

namespace App\Http\Controllers\Tenant\API\Auth\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Auth\Invitation\IndexInvitationRequest;
use App\Http\Requests\Tenant\API\Auth\Invitation\StoreBatchInvitationRequest;
use App\Http\Requests\Tenant\API\Auth\Invitation\StoreSingleInvitationRequest;
use App\Http\Resources\Central\API\Tenancy\InvitationResource;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Services\Tenant\Auth\Invitation\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function index(IndexInvitationRequest $request): JsonResponse
    {
        $this->authorize('viewAny', TenantInvitation::class);
        $tenant = tenant();

        $invitations = $this->invitationService->listForTenant($tenant, $request->user(), $request->validated())
            ->load('tenant');

        return response()->json([
            'invitations' => InvitationResource::collection($invitations),
        ]);
    }

    public function store(StoreSingleInvitationRequest $request): JsonResponse
    {
        $this->authorize('create', TenantInvitation::class);

        $invitation = $this->invitationService->createSingleInvitation(
            tenant(),
            $request->user(),
            $request->validated('email'),
        );

        return response()->json([
            'invitation' => InvitationResource::make($invitation->load('tenant')),
        ], 201);
    }

    public function storeBatch(StoreBatchInvitationRequest $request): JsonResponse
    {
        $this->authorize('create', TenantInvitation::class);

        $result = $this->invitationService->createBatchInvitations(
            tenant(),
            $request->user(),
            $request->validated('emails'),
        );

        $result['successful']->each->load('tenant');

        return response()->json([
            'invitations' => InvitationResource::collection($result['successful']),
            'failed' => $result['failed'],
        ], $result['failed']->isNotEmpty() ? 207 : 201);
    }

    public function reinvite(Request $request, TenantInvitation $invitation): JsonResponse
    {
        $this->ensureTenantMatch($invitation);
        $this->authorize('reinvite', $invitation);

        $newInvitation = $this->invitationService->reinviteFromPrevious(
            $invitation,
            $request->user(),
        );

        return response()->json([
            'invitation' => InvitationResource::make($newInvitation->load('tenant')),
        ], 201);
    }

    public function resend(Request $request, TenantInvitation $invitation): InvitationResource
    {
        $this->ensureTenantMatch($invitation);
        $this->authorize('resend', $invitation);

        $invitation = $this->invitationService->resendInvitation($invitation, $request->user());

        $invitation->load('tenant');

        return InvitationResource::make($invitation);
    }

    public function revoke(Request $request, TenantInvitation $invitation): InvitationResource
    {
        $this->ensureTenantMatch($invitation);
        $this->authorize('revoke', $invitation);

        $invitation = $this->invitationService->revokeInvitation($invitation, $request->user());

        $invitation->load('tenant');

        return InvitationResource::make($invitation);
    }

    private function ensureTenantMatch(TenantInvitation $invitation): void
    {
        if ((string) $invitation->tenant_id !== (string) tenant('id')) {
            abort(404);
        }
    }
}
