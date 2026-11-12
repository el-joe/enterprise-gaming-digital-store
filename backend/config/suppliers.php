<?php

use App\Domains\Suppliers\Adapters\InternalStockAdapter;
use App\Domains\Suppliers\Adapters\ManualFulfillmentAdapter;
use App\Domains\Suppliers\Adapters\Reloadly\ReloadlyGiftCardAdapter;

return [
    /*
    |--------------------------------------------------------------------------
    | Adapter registry (PROJECT_ARCHITECTURE.md 7.1)
    |--------------------------------------------------------------------------
    |
    | `suppliers.adapter_key` to adapter class. A new supplier is a new class here
    | and a database row; order and checkout code do not change.
    |
    */
    'adapters' => [
        'reloadly_giftcards' => ReloadlyGiftCardAdapter::class,
        'internal_stock' => InternalStockAdapter::class,
        'manual_fulfillment' => ManualFulfillmentAdapter::class,
    ],

    /*
    | Verification backoff in minutes after an ambiguous or in-progress outcome
    | (architecture 8.3). The last value repeats until the verification deadline.
    */
    'verification_backoff_minutes' => [1, 2, 5, 10, 15, 30],

    /*
    | A `submitting` row older than its supplier's request timeout plus this many
    | seconds is moved to pending verification by the sweeper.
    */
    'stale_submission_grace_seconds' => 60,

    /*
    | A unit still `queued` this long after it was queued is dispatched again. The
    | gate makes a repeat dispatch a no-op, so this only recovers lost jobs.
    */
    'requeue_stuck_after_minutes' => 5,

    /*
    | A failed deliverable fetch is retried (GET only) after this many minutes.
    */
    'deliverable_retry_after_minutes' => 10,

    /*
    | Circuit breaker: consecutive failed health checks before selection skips the
    | supplier, and for how long.
    */
    'circuit_failure_threshold' => 3,
    'circuit_open_minutes' => 5,

    'reconciliation_window_hours' => 48,

    /*
    | Synced cost ignores the supplier's sender discount until sandbox verification
    | confirms how it is applied (UNVERIFIED). Overstating cost is the safe error.
    */
    'apply_discount_to_cost' => false,

    /*
    | Synced prices older than this are not used by supplier selection (architecture 7.3).
    | A price unchanged since the last sync is written again once half this age has passed.
    */
    'max_price_age_minutes' => 1440,

    /*
    | SECURITY.md section 2 (SSRF): a supplier's base_url_override must be HTTPS and
    | on its adapter's list. Adapters without a list accept no override.
    */
    'override_hosts' => [
        'reloadly_giftcards' => ['giftcards.reloadly.com', 'giftcards-sandbox.reloadly.com'],
    ],

    /*
    | Owner decision 12 (SECURITY.md section 2, SSRF): supplier product images are downloaded only
    | over HTTPS from these hosts (exact names), without following redirects, within the timeout
    | and size limit, and only as a raster image. Every Reloadly product checked on 2026-11-12
    | (`logoUrls` and `brand.logoUrl`) is on cdn.reloadly.com.
    */
    'image_hosts' => ['cdn.reloadly.com'],
    'image_timeout_seconds' => 10,
    'image_max_kilobytes' => 5120,

    'reloadly' => [
        'auth_url' => 'https://auth.reloadly.com',
        'base_urls' => [
            'sandbox' => 'https://giftcards-sandbox.reloadly.com',
            'production' => 'https://giftcards.reloadly.com',
        ],
        'accept' => 'application/com.reloadly.giftcards-v1+json',
        // INTEGRATIONS.md 1.7: USD 100 (or equivalent) per request by default.
        'default_per_request_cap' => ['amount' => '100', 'currency' => 'USD'],
        'page_size' => 100,
    ],

    /*
    | Adapters whose sandbox has no working balance endpoint. Reloadly's sandbox never answers
    | GET /accounts/balance (checked 2026-11-12, no reply after 60 s) while its other endpoints do,
    | so in sandbox mode the balance refresh is skipped and the admin says so; live is unchanged.
    */
    'balance_unavailable_in_sandbox' => ['reloadly_giftcards'],
];
