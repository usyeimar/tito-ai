<?php

namespace App\Http\Controllers\Central\API\Auth\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\Profile\UpdatePasswordRequest;
use App\Http\Requests\Central\API\Auth\Profile\UpdateProfilePictureRequest;
use App\Http\Requests\Central\API\Auth\Profile\UpdateProfileRequest;
use App\Services\Central\Auth\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfile($request->user(), $request->validated());

        return response()->json($result);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $result = $this->profileService->updatePassword($request->user(), $request->validated());

        return response()->json($result);
    }

    public function updateProfilePicture(UpdateProfilePictureRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfilePicture(
            $request->user(),
            $request->validated('profile_picture'),
        );

        return response()->json($result);
    }

    public function removeProfilePicture(Request $request): JsonResponse
    {
        $result = $this->profileService->removeProfilePicture($request->user());

        return response()->json($result);
    }
}
