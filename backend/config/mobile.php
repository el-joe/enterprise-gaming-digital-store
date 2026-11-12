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
    | The three lifetimes are the admin settings security.mobile_access_token_ttl_minutes,
    | security.mobile_refresh_token_ttl_days and security.mobile_2fa_challenge_ttl_seconds
    | (owner decision 11), applied by SettingsOverlay.
    |
    */
    'auth' => [
        'access_token_ttl_minutes' => 15,

        'refresh_token_ttl_days' => 30,

        'challenge_ttl_seconds' => 300,

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
    | `per_minute` limits challenge and confirm requests per customer. `challenge_ttl_seconds` is
    | the admin setting app_links.biometric_challenge_ttl_seconds (this is its default).
    |
    */
    'biometric' => [
        'challenge_ttl_seconds' => 60,
        'max_failed_attempts' => 5,
        'per_minute' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | App configuration defaults (M-A4, GET /api/v1/app/config, gap G9)
    |--------------------------------------------------------------------------
    |
    | Built-in defaults only: the settings table (group `app`, same key, edited in Admin > Settings >
    | Mobile app or with `php artisan mobile:app-config set <key> <value>`) is the source of truth
    | and takes effect without a deploy. There are no `.env` keys for these values.
    | Everything here is public. Never put a secret in this section.
    |
    | Versions are Semantic Versioning 2.0.0. An installed version below `*_min_version` must
    | update; below `*_latest_version` it may. The feature flags tell the app what to offer;
    | they do not switch the server routes off.
    |
    */
    'app' => [
        'android_min_version' => '1.0.0',
        'android_latest_version' => '1.0.0',
        'ios_min_version' => '1.0.0',
        'ios_latest_version' => '1.0.0',

        'maintenance' => false,
        // Per locale, for example {"en": "...", "ar": "..."}; null uses the translated default text.
        'maintenance_message' => null,

        'checkout_enabled' => true,
        // Published as true only while `storefront.guest_checkout` is also on (owner decision 4).
        'guest_checkout_enabled' => true,
        'wallet_enabled' => true,
        'reviews_enabled' => true,
        'support_enabled' => true,

        // Owner decision 3 (OD-1): when store review forbids in-app purchase, the app shows
        // "Complete purchase on boxcards.shop" and opens `web_checkout_url` (published as
        // `links.web_checkout`) in the system browser instead of its own checkout. Off by default.
        'web_checkout_redirect' => false,
        'web_checkout_url' => '/checkout',

        // M-A10: the app has no stateless forgot-password endpoint (POST /forgot-password is a
        // session route), so "Forgot password?" opens this page in the system browser.
        // Published as `links.forgot_password`.
        'forgot_password_url' => '/forgot-password',

        // Absolute https URLs (or site paths, made absolute with APP_URL); null hides the link.
        'support_url' => null,
        'terms_url' => null,
        'privacy_url' => null,
        'refund_url' => null,

        'cache_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | App link association files (M-A6, ADR-039, gap G8)
    |--------------------------------------------------------------------------
    |
    | Served by Laravel at /.well-known/assetlinks.json and /.well-known/apple-app-site-association.
    | Values are supplied by the owner and never invented: with no fingerprint, assetlinks.json
    | is `[]`; with no iOS app id, the Apple file lists no apps. The packages, fingerprints and app
    | ids are admin settings (Settings > App links, group `app_links`, applied over these
    | defaults by SettingsOverlay); `paths` stays here.
    |
    | - `android_packages`: application ids (comma-separated), for example
    |   `shop.boxcards.app,shop.boxcards.app.staging,shop.boxcards.app.dev` for the flavors.
    | - `android_sha256_fingerprints`: SHA-256 signing certificate fingerprints (comma-separated,
    |   `AA:BB:...`, 32 bytes); with Play App Signing, the one shown in the Play Console. Every
    |   listed package is declared with every fingerprint.
    | - `ios_app_ids`: `TEAMID.bundle.id` (comma-separated), for example `ABCDE12345.shop.boxcards.app`.
    | - `paths`: the paths the apps handle, mirroring the Android intent filter
    |   (application/android/app/src/main/AndroidManifest.xml) and the app's link listener: the
    |   payment returns under `/app/` (M-A6), product pages (M-A12), the customer order page and
    |   the signed guest access link of the delivery emails (M-A14), and the support ticket page
    |   of the "support replied" email (M-A18). In Apple components `*`
    |   matches any characters, including `/`; a component without `?` matches any query.
    |
    */
    'app_links' => [
        'android_packages' => 'shop.boxcards.app',
        'android_sha256_fingerprints' => '',
        'ios_app_ids' => '',
        'paths' => [
            '/app/*',
            '/products/*',
            '/en/products/*',
            '/ar/products/*',
            '/account/orders/*',
            '/en/account/orders/*',
            '/ar/account/orders/*',
            '/account/tickets/*',
            '/en/account/tickets/*',
            '/ar/account/tickets/*',
            '/api/v1/checkout/guest/orders/*/access',
        ],
        'cache_seconds' => 3600,
    ],
];
