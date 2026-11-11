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
    | Biometric re-authentication (OD-1, owner decisions 5 and 6, ADR-040)
    |--------------------------------------------------------------------------
    |
    | A device-bound ECDSA P-256 key, registered per sign-in after a password confirmation,
    | answers a single-use challenge to satisfy the code-reveal re-auth without the password.
    | `challenge_ttl_seconds` is how long a challenge may be signed; after
    | `max_failed_attempts` consecutive bad signatures the key is revoked (password again).
    | `per_minute` limits challenge and confirm requests per customer.
    |
    */
    'biometric' => [
        'challenge_ttl_seconds' => (int) (env('MOBILE_BIOMETRIC_CHALLENGE_TTL_SECONDS') ?: 60),
        'max_failed_attempts' => 5,
        'per_minute' => 10,
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
        // Published as true only while `storefront.guest_checkout` is also on (owner decision 4).
        'guest_checkout_enabled' => (bool) env('MOBILE_GUEST_CHECKOUT_ENABLED', true),
        'wallet_enabled' => (bool) env('MOBILE_WALLET_ENABLED', true),
        'reviews_enabled' => (bool) env('MOBILE_REVIEWS_ENABLED', true),
        'support_enabled' => (bool) env('MOBILE_SUPPORT_ENABLED', true),

        // Owner decision 3 (OD-1): when store review forbids in-app purchase, the app shows
        // "Complete purchase on boxcards.shop" and opens `web_checkout_url` (published as
        // `links.web_checkout`) in the system browser instead of its own checkout. Off by default.
        'web_checkout_redirect' => (bool) env('MOBILE_WEB_CHECKOUT_REDIRECT', false),
        'web_checkout_url' => env('MOBILE_WEB_CHECKOUT_URL') ?: '/checkout',

        // M-A10: the app has no stateless forgot-password endpoint (POST /forgot-password is a
        // session route), so "Forgot password?" opens this page in the system browser.
        // Published as `links.forgot_password`.
        'forgot_password_url' => env('MOBILE_FORGOT_PASSWORD_URL') ?: '/forgot-password',

        // Absolute https URLs (or site paths, made absolute with APP_URL); null hides the link.
        'support_url' => env('MOBILE_SUPPORT_URL') ?: null,
        'terms_url' => env('MOBILE_TERMS_URL') ?: null,
        'privacy_url' => env('MOBILE_PRIVACY_URL') ?: null,
        'refund_url' => env('MOBILE_REFUND_URL') ?: null,

        'cache_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | App link association files (M-A6, ADR-039, gap G8)
    |--------------------------------------------------------------------------
    |
    | Served by Laravel at /.well-known/assetlinks.json and /.well-known/apple-app-site-association.
    | Values are supplied by the owner and never invented: with no fingerprint, assetlinks.json
    | is `[]`; with no iOS app id, the Apple file lists no apps.
    |
    | - `android_packages`: application ids (comma-separated), for example
    |   `shop.boxcards.app,shop.boxcards.app.staging,shop.boxcards.app.dev` for the flavors.
    | - `android_sha256_fingerprints`: SHA-256 signing certificate fingerprints (comma-separated,
    |   `AA:BB:...`, 32 bytes); with Play App Signing, the one shown in the Play Console. Every
    |   listed package is declared with every fingerprint.
    | - `ios_app_ids`: `TEAMID.bundle.id` (comma-separated), for example `ABCDE12345.shop.boxcards.app`.
    | - `paths`: the paths the apps handle. Only the payment return paths live under `/app/`.
    |
    */
    'app_links' => [
        'android_packages' => env('MOBILE_ANDROID_PACKAGES') ?: 'shop.boxcards.app',
        'android_sha256_fingerprints' => env('MOBILE_ANDROID_SHA256_FINGERPRINTS') ?: '',
        'ios_app_ids' => env('MOBILE_IOS_APP_IDS') ?: '',
        'paths' => ['/app/*'],
        'cache_seconds' => 3600,
    ],
];
