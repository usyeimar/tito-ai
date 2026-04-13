<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tito AI Runners
|--------------------------------------------------------------------------
|
| Connection settings for the FastAPI runners microservice that manages
| voice agent sessions. The Laravel side never talks to LiveKit/Daily
| directly: it asks the runner to create a session and forwards the
| returned room URL + access token to the client.
|
| Source: services/runners
|
*/

return [
    'base_url' => rtrim((string) env('TITO_RUNNERS_URL', 'http://localhost:8000'), '/'),

    'api_key' => env('TITO_RUNNERS_API_KEY'),

    'timeout' => (int) env('TITO_RUNNERS_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Runner Registry (Load Balancing)
    |--------------------------------------------------------------------------
    |
    | When enabled, Laravel will use Redis to discover available runners and
    | balance new sessions across them based on current load (active_sessions).
    |
    | Requirements:
    | - RUNNER_ADVERTISE_URL must be set on each runner instance
    | - Redis must be accessible to both Laravel and runners
    |
    */
    'use_registry' => (bool) env('TITO_RUNNERS_USE_REGISTRY', false),

    /*
    | Default transport when an agent does not specify one. Both `livekit`
    | and `daily` are supported by the browser UI; the runner picks the
    | matching room provider per session.
    */
    'default_transport' => env('TITO_RUNNERS_DEFAULT_TRANSPORT', 'livekit'),

    /*
    | Whitelist of transport providers the UI can render. Sessions whose
    | provider is not in this list are torn down with a clear error.
    */
    'allowed_transports' => ['livekit', 'daily'],

    /*
    | Default callback URL the runner will use to POST session events
    | (transcripts, status changes, etc.). Leave null to let the runner
    | use its own BACKEND_URL.
    */
    'callback_url' => env('TITO_RUNNERS_CALLBACK_URL'),
];
