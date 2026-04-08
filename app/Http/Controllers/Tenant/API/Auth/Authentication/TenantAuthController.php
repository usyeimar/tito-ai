<?php

namespace App\Http\Controllers\Tenant\API\Auth\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\API\Auth\Authentication\TenantUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantAuthController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['type.resourceTypeProfile', 'roles', 'profilePicture']);

        return response()->json([
            'user' => TenantUserResource::make($user),
            'tenant' => [
                'id' => (string) tenant()->getTenantKey(),
            ],
        ]);
    }
}
