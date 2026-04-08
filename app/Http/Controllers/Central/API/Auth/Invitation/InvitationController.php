<?php

namespace App\Http\Controllers\Central\API\Auth\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\Auth\Invitation\IndexInvitationRequest;
use App\Http\Resources\Central\API\Tenancy\InvitationResource;
use App\Models\Central\Tenancy\TenantInvitation;
use App\Services\Central\Auth\Invitation\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function index(IndexInvitationRequest $request): JsonResponse
    {
        $invitations = $this->invitationService
            ->listForEmail((string) $request->user()->email, data_get($request->validated(), 'filter.search'))
            ->load('tenant');

        return response()->json([
            'invitations' => InvitationResource::collection($invitations),
        ]);
    }

    public function resolve(Request $request): InvitationResource
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $invitation = $this->invitationService->resolveInvitation((string) $request->string('token'));
        $invitation->load('tenant');

        return InvitationResource::make($invitation);
    }

    public function accept(Request $request, TenantInvitation $invitation): InvitationResource
    {
        $invitation = $this->invitationService->acceptInvitation($invitation, $request->user());
        $invitation->load('tenant');

        return InvitationResource::make($invitation);
    }

    public function decline(Request $request, TenantInvitation $invitation): InvitationResource
    {
        $invitation = $this->invitationService->declineInvitation($invitation, $request->user());
        $invitation->load('tenant');

        return InvitationResource::make($invitation);
    }
}
