<?php

return [
    'access_ttl_minutes' => (int) env('PASSPORT_ACCESS_TTL_MINUTES', 60),
    'refresh_ttl_days' => (int) env('PASSPORT_REFRESH_TTL_DAYS', 30),
    'access_cookie' => [
        'central_name' => env('PASSPORT_CENTRAL_ACCESS_COOKIE', 'central_access_token'),
        'tenant_name' => env('PASSPORT_TENANT_ACCESS_COOKIE', 'tenant_access_token'),
        'domain' => env('PASSPORT_ACCESS_COOKIE_DOMAIN', env('SESSION_DOMAIN')),
        'secure' => env('PASSPORT_ACCESS_COOKIE_SECURE', env('SESSION_SECURE_COOKIE')),
        'same_site' => env('PASSPORT_ACCESS_COOKIE_SAME_SITE', env('SESSION_SAME_SITE', 'lax')),
    ],
    'refresh_cookie' => [
        'enabled' => (bool) env('PASSPORT_REFRESH_COOKIE_ENABLED', true),
        'central_name' => env('PASSPORT_CENTRAL_REFRESH_COOKIE', 'central_refresh_token'),
        'tenant_name' => env('PASSPORT_TENANT_REFRESH_COOKIE', 'tenant_refresh_token'),
        'domain' => env('PASSPORT_REFRESH_COOKIE_DOMAIN', env('SESSION_DOMAIN')),
        'secure' => env('PASSPORT_REFRESH_COOKIE_SECURE', env('SESSION_SECURE_COOKIE')),
        'same_site' => env('PASSPORT_REFRESH_COOKIE_SAME_SITE', env('SESSION_SAME_SITE', 'lax')),
    ],
];
