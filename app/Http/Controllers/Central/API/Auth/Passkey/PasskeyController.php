<?php

namespace App\Http\Controllers\Central\API\Auth\Passkey;

use App\Http\Controllers\Controller;
use App\Services\Central\Auth\Passkey\PasskeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasskeyController extends Controller
{
    public function __construct(
        private readonly PasskeyService $passkeyService,
    ) {}

    public function passkeys(Request $request): JsonResponse
    {
        $result = $this->passkeyService->listPasskeys($request->user());

        return response()->json($result);
    }

    public function revokePasskey(Request $request, string $credential): JsonResponse
    {
        $result = $this->passkeyService->revokePasskey($request->user(), $credential);

        return response()->json($result);
    }
}
