<?php

return [
    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', env('APP_NAME')),
        'id' => env('WEBAUTHN_ID', parse_url((string) env('APP_URL', ''), PHP_URL_HOST)),
    ],

    'origins' => env('WEBAUTHN_ORIGINS', env('FRONTEND_URL')),

    'challenge' => [
        'bytes' => 16,
        'timeout' => 60,
        'key' => '_webauthn',
    ],
];
