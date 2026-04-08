<?php

namespace App\Http\Resources\Central\API\Tenancy;

use App\Models\Central\Tenancy\TenantInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantInvitation
 */
class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'last_sent_at' => $this->last_sent_at,
            'accepted_at' => $this->accepted_at,
            'declined_at' => $this->declined_at,
            'revoked_at' => $this->revoked_at,
            'created_at' => $this->created_at,
            'tenant' => $this->whenLoaded('tenant', fn () => TenantResource::make($this->tenant)),
        ];
    }
}
