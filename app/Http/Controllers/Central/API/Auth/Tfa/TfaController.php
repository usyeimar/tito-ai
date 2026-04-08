<?php

namespace App\Http\Controllers\Central\API\Auth\Tfa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\Tfa\TfaChallengeRequest;
use App\Http\Requests\Central\API\Auth\Tfa\TfaConfirmRequest;
use App\Http\Requests\Central\API\Auth\Tfa\TfaVerifyRequest;
use App\Http\Resources\Central\API\Auth\Authentication\AuthResource;
use App\Http\Resources\Central\API\Auth\Tfa\RecoveryCodesResource;
use App\Http\Resources\Central\API\Auth\Tfa\TfaEnableResource;
use App\Services\Central\Auth\Tfa\TfaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TfaController extends Controller
{
    public function __construct(
        private readonly TfaService $tfaService,
    ) {}

    public function verifyTfa(TfaVerifyRequest $request): AuthResource
    {
        $result = $this->tfaService->verifyTfa($request, $request->validated());

        return AuthResource::make($result);
    }

    public function tfaChallenge(TfaChallengeRequest $request): JsonResponse
    {
        $result = $this->tfaService->challengeTfa(
            $request->user(),
            $request->validated('password')
        );

        return response()->json($result);
    }

    public function tfaEnable(Request $request): TfaEnableResource
    {
        $result = $this->tfaService->enableTfa(
            $request->user()
        );

        return TfaEnableResource::make($result);
    }

    public function tfaConfirm(TfaConfirmRequest $request): RecoveryCodesResource
    {
        $result = $this->tfaService->confirmTfa(
            $request->user(),
            $request->validated('code')
        );

        return RecoveryCodesResource::make($result);
    }

    public function tfaRecoveryCodes(Request $request): RecoveryCodesResource
    {
        $result = $this->tfaService->getRecoveryCodes($request->user());

        return RecoveryCodesResource::make($result);
    }

    public function tfaRegenerateRecoveryCodes(Request $request): RecoveryCodesResource
    {
        $result = $this->tfaService->regenerateRecoveryCodes($request->user());

        return RecoveryCodesResource::make($result);
    }

    public function tfaDisable(Request $request): JsonResponse
    {
        $result = $this->tfaService->disableTfa($request->user());

        return response()->json($result);
    }
}
