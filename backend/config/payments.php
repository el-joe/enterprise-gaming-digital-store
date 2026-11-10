<?php

use App\Domains\Payments\Adapters\Paymob\PaymobPaymentGateway;
use App\Domains\Payments\Adapters\PayPal\PayPalPaymentGateway;
use App\Domains\Payments\Adapters\Stripe\StripePaymentGateway;
use App\Domains\Payments\Adapters\Wallet\WalletPaymentGateway;

return [
    /*
    |--------------------------------------------------------------------------
    | Gateway adapter registry (PROJECT_ARCHITECTURE.md 9.2)
    |--------------------------------------------------------------------------
    |
    | `payment_gateways.adapter_key` to adapter class. Every adapter here is tested
    | with mocked HTTP only and is awaiting sandbox verification, except `wallet`
    | (M11), which is internal: it debits our own ledger and calls no provider.
    |
    */
    'adapters' => [
        'stripe' => StripePaymentGateway::class,
        'paypal' => PayPalPaymentGateway::class,
        'paymob' => PaymobPaymentGateway::class,
        'wallet' => WalletPaymentGateway::class,
    ],

    /*
    | Where the customer's browser is sent after the provider's page. The page
    | (M12) only shows a "checking" state and asks the server to verify; it never
    | marks anything paid. `{order}` is replaced by the order public id.
    */
    'return_url' => env('PAYMENTS_RETURN_URL') ?: '/checkout/orders/{order}/return',

    'http' => [
        'connect_timeout' => (int) env('PAYMENTS_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('PAYMENTS_TIMEOUT', 20),
    ],

    'stripe' => [
        // ADR-023: webhook and response shapes depend on the version, so it is pinned.
        'api_version' => env('STRIPE_API_VERSION') ?: '2026-10-28.endive',
        'webhook_tolerance_seconds' => 300,
    ],

    'paypal' => [
        'base_urls' => [
            'sandbox' => 'https://api-m.sandbox.paypal.com',
            'production' => 'https://api-m.paypal.com',
        ],
    ],

    'paymob' => [
        'base_url' => 'https://accept.paymob.com',
        'auth_token_minutes' => 50,
    ],

    /*
    | Provider idempotency keys are retained for at least 24 hours (Stripe) or an
    | unverified period (PayPal create and capture), so a create or refund is only
    | re-sent with the same key within this window. After it, state is re-read.
    */
    'idempotency_retry_window_hours' => 12,

    /*
    | Reconciliation (architecture 9.5).
    */
    'reconcile_open_after_minutes' => 5,
    'reconcile_backoff_minutes' => [1, 5, 15, 60],
    'expire_unpaid_attempt_after_hours' => 24,
    'reconciliation_window_hours' => 48,
    'webhook_max_attempts' => 5,

    /*
    | IP country (ADR-016). `null` resolves nothing (orders.ip_country stays null and
    | rules that need an IP country do not match). `header` reads a country header set
    | by a trusted edge proxy such as Cloudflare; enable it only when the origin
    | accepts traffic from that proxy alone, since a client can send the header itself.
    | GeoLite2 needs the geoip2/geoip2 package and a MaxMind database, not installed.
    */
    'geoip' => [
        'driver' => env('GEOIP_DRIVER') ?: 'null',
        'header' => env('GEOIP_COUNTRY_HEADER') ?: 'CF-IPCountry',
    ],
];
