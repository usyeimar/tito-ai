<?php

namespace App\Http\Controllers\Central\API\Auth\Password;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\Password\ConfirmPasswordRequest;
use App\Http\Requests\Central\API\Auth\Password\ForgotPasswordRequest;
use App\Http\Requests\Central\API\Auth\Password\ResetPasswordRequest;
use App\Services\Central\Auth\Password\PasswordService;
use App\Services\Central\Auth\Tfa\TfaService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        private readonly PasswordService $passwordService,
        private readonly TfaService $tfaService,
    ) {}

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordService->sendPasswordResetLink($request->validated('email'));

        return response()->json($result);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->passwordService->resetPassword($request->validated());

        return response()->json($result);
    }

    public function confirmPassword(ConfirmPasswordRequest $request): JsonResponse
    {
        $result = $this->tfaService->challengeTfa(
            $request->user(),
            $request->validated('password')
        );

        return response()->json($result);
    }
}
