<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Define your application's broadcast channels here.
|
| Tenancy prefixed-channel setup:
| - Central channels: no prefix (e.g. 'orders')
| - Tenant channels: use tenant_channel('orders', ...)
| - Global channels: prefix with 'global__' or use global_channel('orders', ...)
|
*/

// Example central channel:
// Broadcast::channel('orders', fn ($user) => true);

// Example tenant channel:
// tenant_channel('orders', fn ($user) => true);

// Example global channel:
// global_channel('system', fn ($user) => true);

tenant_channel('bulk-actions.{userId}', function ($user, string $tenant, string $userId): bool {
    return (string) $user->getAuthIdentifier() === $userId;
});

tenant_channel('notifications.{userId}', function ($user, string $tenant, string $userId): bool {
    return (string) $user->getAuthIdentifier() === $userId;
});

tenant_channel('ringcentral.user.{userId}', function ($user, string $tenant, string $userId): bool {
    return (string) $user->getAuthIdentifier() === $userId
        && $user->can('ring_central.view');
});

tenant_channel('ringcentral.shared', function ($user): bool {
    return $user->can('ring_central.view');
});

tenant_channel('ringcentral.admin', function ($user): bool {
    return $user->can('ring_central.manage');
});
