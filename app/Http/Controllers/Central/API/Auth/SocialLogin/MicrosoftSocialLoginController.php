<?php

namespace App\Http\Controllers\Central\API\Auth\SocialLogin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\SocialLogin\MicrosoftSocialLoginRequest;
use App\Http\Resources\Central\API\Auth\Authentication\AuthResource;
use App\Services\Central\Auth\SocialLogin\SocialLoginService;

class MicrosoftSocialLoginController extends Controller
{
    public function __construct(
        private readonly SocialLoginService $socialLoginService,
    ) {}

    public function login(MicrosoftSocialLoginRequest $request): AuthResource
    {
        $result = $this->socialLoginService->loginWithMicrosoft(
            $request,
            $request->validated('access_token'),
            $request->validated('device_name')
        );

        return AuthResource::make($result);
    }
}
