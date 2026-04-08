<?php

namespace App\Services\Central\Auth\Profile;

use App\Http\Resources\Central\API\Auth\Authentication\UserResource;
use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Central\System\SystemProfilePicture;
use App\Services\Central\Auth\Password\PasswordConfirmationService;
use App\Services\Concerns\EnsuresVerifiedEmail;
use App\Services\Shared\Auth\UserTokenService;
use App\Services\Tenant\Commons\ProfilePictures\ProfilePictureService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileService
{
    use EnsuresVerifiedEmail;

    public function __construct(
        private readonly UserTokenService $userTokenService,
        private readonly PasswordConfirmationService $passwordConfirmationService,
        private readonly ProfilePictureService $pictureService,
    ) {}

    public function updateProfile(CentralUser $user, array $data): array
    {
        $data = Arr::only($data, ['name', 'email']);
        $email = array_key_exists('email', $data)
            ? Str::lower((string) $data['email'])
            : null;

        $emailChanged = $email !== null && $email !== '' && $email !== $user->email;
        if ($emailChanged) {
            $this->passwordConfirmationService->ensureRecentPasswordConfirmation($user);
            $data['email'] = $email;
            $data['email_verified_at'] = null;
            $data['email_verification_sent_at'] = null;
        }

        $user->forceFill($data)->save();

        if ($emailChanged) {
            $this->userTokenService->revokeAllTokens($user, true);
            $user->sendEmailVerificationNotificationOnce('update');
        }

        return [
            'message' => 'Profile updated.',
            'data' => [
                'user' => UserResource::make($user->refresh()),
                'email_verification_required' => $emailChanged,
            ],
        ];
    }

    public function updateProfilePicture(CentralUser $user, UploadedFile $profilePicture): array
    {
        try {
            $path = $this->pictureService->store($profilePicture, 'users', (string) $user->global_id);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'profile_picture' => [$exception->getMessage()],
            ]);
        }

        $picture = SystemProfilePicture::query()
            ->where('user_global_id', $user->global_id)
            ->first();

        if (! $picture) {
            $picture = new SystemProfilePicture([
                'global_id' => (string) Str::ulid(),
                'user_global_id' => $user->global_id,
            ]);
        }

        $picture->path = $path;
        $picture->save();

        $tenantIds = $user->tenants()->pluck('tenants.id')->all();
        if ($tenantIds !== []) {
            $picture->tenants()->syncWithoutDetaching($tenantIds);
        }

        return [
            'message' => 'Profile picture updated.',
            'data' => [
                'user' => UserResource::make($user->refresh()->load('profilePicture')),
            ],
        ];
    }

    public function removeProfilePicture(CentralUser $user): array
    {
        if (! $user->global_id) {
            return [
                'message' => 'Profile picture removed.',
                'data' => [
                    'user' => UserResource::make($user->refresh()),
                ],
            ];
        }

        $picture = SystemProfilePicture::query()
            ->where('user_global_id', $user->global_id)
            ->first();

        if ($picture?->path) {
            try {
                $this->pictureService->delete($picture->path);
            } catch (Throwable $exception) {
                throw ValidationException::withMessages([
                    'profile_picture' => [$exception->getMessage()],
                ]);
            }
        }

        $picture?->delete();

        return [
            'message' => 'Profile picture removed.',
            'data' => [
                'user' => UserResource::make($user->refresh()),
            ],
        ];
    }

    public function updatePassword(CentralUser $user, array $data): array
    {
        $this->ensureVerifiedEmail($user, 'Email verification is required to update your password.');

        $closeAllSessions = Arr::get($data, 'close_all_sessions', false);
        $password = Arr::get($data, 'password');

        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();

        $this->userTokenService->revokeAllTokens($user, ! $closeAllSessions);

        return [
            'message' => 'Password updated.',
            'data' => [
                'user' => UserResource::make($user->refresh()),
            ],
        ];
    }
}
