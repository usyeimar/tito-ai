<?php

namespace App\Http\Controllers\Central\API\Auth\EmailVerification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\API\Auth\EmailVerification\EmailVerificationNotificationRequest;
use App\Models\Central\Auth\Authentication\CentralUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function resendEmailVerification(EmailVerificationNotificationRequest $request): JsonResponse
    {
        $reason = $request->validated('reason') ?? 'signup';
        $user = $request->user();

        if (! $user instanceof CentralUser) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ])->status(422);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['This email address has already been verified.'],
            ])->status(422);
        }

        $sent = $user->resendEmailVerificationNotification($reason);

        if (! $sent) {
            Log::warning('Failed to resend verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'reason' => $reason,
            ]);

            throw ValidationException::withMessages([
                'email' => ['Unable to send verification email. Please try again later.'],
            ])->status(500);
        }

        Log::info('Verification email resent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $reason,
        ]);

        return response()->json([
            'message' => 'A new verification link has been sent to your email address.',
        ]);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $user = CentralUser::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            throw ValidationException::withMessages([
                'email' => ['This verification link is invalid.'],
            ])->status(422);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['This email address has already been verified.'],
            ])->status(422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'message' => 'Email successfully verified.',
        ]);
    }
}
