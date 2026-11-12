<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Automation API (docs/N8N_AUTOMATIONS.md, /api/v1/automation)
    |--------------------------------------------------------------------------
    |
    | Requests from n8n carry a bearer token of an `n8n` service account and an HMAC-SHA256
    | signature made with that account's own secret. A request is refused when its timestamp is
    | more than `signature_window_seconds` away from the server clock, or when its nonce was seen
    | within `nonce_ttl_seconds` (which must be longer than twice the window).
    |
    */
    'signature_window_seconds' => 300,

    'nonce_ttl_seconds' => 900,

    /*
    | Lifetime of an issued token in days.
    */
    'token_ttl_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Outbox to n8n (architecture section 16)
    |--------------------------------------------------------------------------
    |
    | When `enabled` is false no events are written. When the webhook URL or the outbound secret
    | is empty, events are kept pending and `automation:flush-outbox` sends nothing. Each failed
    | delivery waits the next delay in `retry_delays_seconds`; after the last one the event is
    | marked failed and reported by GET /monitor/webhooks.
    |
    | `enabled`, `webhook_url`, `secret` and `auth_token` (and `token_ttl_days`, `store_name`) are
    | admin settings (Settings > Automation, group `automation`; the secrets encrypted), applied
    | over these built-in defaults by SettingsOverlay. Never put them in `.env`. An empty
    | `store_name` setting follows the store name (`app.name`, Settings > Store and languages).
    |
    */
    'outbox' => [
        'enabled' => false,
        'webhook_url' => '',
        'secret' => '',
        'auth_header' => 'X-EGS-Webhook-Token',
        'auth_token' => '',
        'batch_size' => 50,
        'lease_seconds' => 120,
        'timeout_seconds' => 10,
        'retry_delays_seconds' => [60, 300, 900, 3600, 10800, 21600],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring thresholds
    |--------------------------------------------------------------------------
    |
    | A05 alerts below each supplier's own `low_balance_threshold`; E03 alerts below that
    | threshold times `supplier_balance_critical_fraction`. A scheduled task is late when no run
    | has started within `scheduler_grace_seconds` after it was last due.
    |
    */
    'supplier_balance_critical_fraction' => '0.5',

    'payment_failure_rate_alert_pct' => '30',

    'scheduler_grace_seconds' => 300,

    'low_margin_threshold_pct' => '5',

    /*
    |--------------------------------------------------------------------------
    | Customer messaging (D workflows)
    |--------------------------------------------------------------------------
    |
    | The only secondary channel is `sms`. A cart counts as abandoned after
    | `abandoned_cart_after_minutes` of inactivity and is offered for a reminder until
    | `abandoned_cart_max_age_hours`. Segment pages are capped at `segment_page_size`.
    |
    */
    'customer_channel' => 'sms',

    'abandoned_cart_after_minutes' => 60,

    'abandoned_cart_max_age_hours' => 48,

    'review_request_after_days' => 3,

    'review_request_until_days' => 14,

    'reactivation_after_days' => 60,

    'reactivation_until_days' => 365,

    'segment_page_size' => 100,

    'store_name' => 'boxcards.shop',
];
