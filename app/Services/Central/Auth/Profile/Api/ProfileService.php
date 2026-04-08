<?php

namespace App\Services\Central\Auth\Profile\Api;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Support\Str;

class ProfileService
{
    public function updateProfile(CentralUser $user, array $data): array
    {
        $email = $data['email'] ?? null;
        $emailChanged = $email !== null && $email !== '' && $email !== $user->email;

        if ($emailChanged) {
            $data['email'] = Str::lower($email);
            $data['email_verified_at'] = null;
        }

        $user->forceFill($data)->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return [
            'message' => 'Profile updated.',
            'data' => [
                'user' => $user->refresh(),
                'email_verification_required' => $emailChanged,
            ],
        ];
    }

    public function updateProfilePicture(CentralUser $user, array $data): array
    {
        return [
            'message' => 'Profile picture updated.',
            'data' => [
                'user' => $user->refresh(),
            ],
        ];
    }

    public function removeProfilePicture(CentralUser $user): array
    {
        return [
            'message' => 'Profile picture removed.',
            'data' => [
                'user' => $user->refresh(),
            ],
        ];
    }

    public function updatePassword(CentralUser $user, array $data): array
    {
        $closeAllSessions = $data['close_all_sessions'] ?? false;
        $password = $data['password'];

        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();

        return [
            'message' => 'Password updated.',
            'data' => [
                'user' => $user->refresh(),
            ],
        ];
    }
}
