<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Push notifications through Firebase Cloud Messaging (M-A18, owner decision 9, ADR-042)
    |--------------------------------------------------------------------------
    |
    | Off by default. While `enabled` is false every send is a no-op that logs at debug level, so
    | the app's token registration still works and nothing leaves the server.
    |
    | - `fcm.project_id`: the Firebase project id (Project settings > General).
    | - `fcm.credentials_path`: absolute path to the service-account JSON key of that project
    |   (Project settings > Service accounts > Generate new private key), stored OUTSIDE the web
    |   root and readable only by the PHP user. The key never goes into the repository, the
    |   environment file or the database; only its path does.
    |
    | Messages go to the FCM HTTP v1 API with an OAuth2 access token obtained from a JWT signed with
    | the service account's RSA key (openssl, no SDK). The access token is cached encrypted until
    | shortly before it expires.
    |
    */
    'enabled' => (bool) env('FCM_ENABLED', false),

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID') ?: null,
        'credentials_path' => env('FCM_CREDENTIALS_PATH') ?: null,
        'base_url' => 'https://fcm.googleapis.com/v1',
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'connect_timeout' => 5,
        'timeout' => 10,
    ],

    // Android notification channel the app creates at start (MainActivity.kt); shown in the
    // system settings as "Order and support updates".
    'android_channel_id' => 'updates',

    // Delivery job retries: attempts and the waits between them (seconds). A 429 or 5xx answer is
    // retried (honouring Retry-After); an unregistered or invalid token is deleted at once.
    'tries' => 5,
    'backoff' => [30, 120, 600, 1800],

    // POST and DELETE /api/v1/mobile/push-token per customer and per IP.
    'register_per_minute' => 10,
    'register_per_minute_per_ip' => 60,
];
