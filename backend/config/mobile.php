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

    /*
    |--------------------------------------------------------------------------
    | App configuration defaults (M-A4, GET /api/v1/app/config, gap G9)
    |--------------------------------------------------------------------------
    |
    | Defaults only: a value saved in the settings table (group `app`, same key, set with
    | `php artisan mobile:app-config set <key> <value>`) wins and takes effect without a deploy.
    | Everything here is public. Never put a secret in this section.
    |
    | Versions are Semantic Versioning 2.0.0. An installed version below `*_min_version` must
    | update; below `*_latest_version` it may. The feature flags tell the app what to offer;
    | they do not switch the server routes off.
    |
    */
    'app' => [
        'android_min_version' => env('MOBILE_ANDROID_MIN_VERSION') ?: '1.0.0',
        'android_latest_version' => env('MOBILE_ANDROID_LATEST_VERSION') ?: '1.0.0',
        'ios_min_version' => env('MOBILE_IOS_MIN_VERSION') ?: '1.0.0',
        'ios_latest_version' => env('MOBILE_IOS_LATEST_VERSION') ?: '1.0.0',

        'maintenance' => (bool) env('MOBILE_MAINTENANCE', false),
        // Per locale, for example {"en": "...", "ar": "..."}; null uses the translated default text.
        'maintenance_message' => null,

        'checkout_enabled' => (bool) env('MOBILE_CHECKOUT_ENABLED', true),
        'guest_checkout_enabled' => (bool) env('MOBILE_GUEST_CHECKOUT_ENABLED', true),
        'wallet_enabled' => (bool) env('MOBILE_WALLET_ENABLED', true),
        'reviews_enabled' => (bool) env('MOBILE_REVIEWS_ENABLED', true),
        'support_enabled' => (bool) env('MOBILE_SUPPORT_ENABLED', true),

        // Absolute https URLs (or site paths, made absolute with APP_URL); null hides the link.
        'support_url' => env('MOBILE_SUPPORT_URL') ?: null,
        'terms_url' => env('MOBILE_TERMS_URL') ?: null,
        'privacy_url' => env('MOBILE_PRIVACY_URL') ?: null,
        'refund_url' => env('MOBILE_REFUND_URL') ?: null,

        'cache_seconds' => 60,
    ],
];
