<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile app authentication (M-A2, /api/v1/mobile/auth, ADR-035)
    |--------------------------------------------------------------------------
    |
    | The app holds a short-lived Sanctum access token (ability `customer`) and a rotating,
    | single-use refresh token. `access_token_ttl_minutes` is the lifetime of each access token.
    | `refresh_token_ttl_days` is the absolute lifetime of a sign-in (a refresh-token family):
    | rotation never extends it, so the customer signs in again after this many days.
    |
    | A pending two-factor sign-in lives `challenge_ttl_seconds` and allows
    | `challenge_max_attempts` wrong codes before it is spent.
    |
    */
    'auth' => [
        'access_token_ttl_minutes' => (int) (env('MOBILE_ACCESS_TOKEN_TTL_MINUTES') ?: 15),

        'refresh_token_ttl_days' => (int) (env('MOBILE_REFRESH_TOKEN_TTL_DAYS') ?: 30),

        'challenge_ttl_seconds' => (int) (env('MOBILE_2FA_CHALLENGE_TTL_SECONDS') ?: 300),

        'challenge_max_attempts' => 5,

        /*
        | Refresh limiter: per presented token and per IP (carrier NAT puts many devices behind
        | one address, so the IP limit is wider than the `auth` limiter's).
        */
        'refresh_per_minute_per_token' => 5,

        'refresh_per_minute_per_ip' => 60,
    ],
];
