<?php

namespace App\Services\Central\Auth\Password;

use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordService
{
    public function sendPasswordResetLink(string $email): array
    {
        Password::sendResetLink(['email' => Str::lower($email)]);

        return [
            'message' => 'If your email exists in our system, you will receive a password reset link.',
        ];
    }

    public function resetPassword(array $data): array
    {
        $status = Password::reset(
            $data,
            function (CentralUser $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $this->revokePassportTokens($user);

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return ['message' => 'Password has been reset.'];
    }

    private function revokePassportTokens(CentralUser $user): void
    {
        $connection = $user->getConnectionName();

        $tokenQuery = DB::connection($connection)
            ->table('oauth_access_tokens')
            ->where('user_id', $user->getKey());

        $tokenIds = $tokenQuery->pluck('id');

        if ($tokenIds->isEmpty()) {
            return;
        }

        $tokenQuery->update(['revoked' => true]);

        DB::connection($connection)
            ->table('oauth_refresh_tokens')
            ->whereIn('access_token_id', $tokenIds)
            ->update(['revoked' => true]);
    }
}
