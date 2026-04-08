<?php

return [
    'central' => [
        'client_id' => env('PASSPORT_CENTRAL_CLIENT_ID', null),
        'client_secret' => env('PASSPORT_CENTRAL_CLIENT_SECRET', null),
    ],
    'tenant' => [
        'client_id' => env('PASSPORT_TENANT_CLIENT_ID', null),
        'client_secret' => env('PASSPORT_TENANT_CLIENT_SECRET', null),
    ],
];
