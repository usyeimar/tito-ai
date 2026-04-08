<?php

namespace App\Services\Shared\Auth;

use App\Models\Central\Auth\Authentication\CentralUser;
use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Support\Facades\DB;

class UserTokenService
{
    public function revokeAllTokens(CentralUser $centralUser, bool $omitCurrrentSession = false): void
    {
        $this->revokeTokensForConnection($centralUser->getConnectionName(), $centralUser, $omitCurrrentSession);

        $centralUser->tenants()->each(function ($tenant) use ($centralUser): void {
            $tenant->run(function () use ($centralUser): void {
                $tenantUser = User::query()->where('global_id', $centralUser->global_id)->first();
                if (! $tenantUser) {
                    return;
                }

                $this->revokeTokensForConnection(null, $tenantUser);
            });
        });
    }

    private function revokeTokensForConnection(?string $connection, CentralUser|User $user, bool $omitCurrrentSession = false): void
    {
        $tokenQuery = DB::connection($connection)
            ->table('oauth_access_tokens')
            ->where('user_id', $user->getKey())
            ->when($omitCurrrentSession, function ($query) use ($user): void {
                $query->where('id', '!=', $user->currentAccessToken()->id);
            });

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
