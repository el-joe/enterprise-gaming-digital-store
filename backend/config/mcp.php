<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP API (docs/MCP_SERVER.md)
    |--------------------------------------------------------------------------
    |
    | Pending write actions expire after `pending_action_ttl_minutes`. A price change larger
    | than `price_change_tier1_max_pct` percent, or one at or below the margin floor, is tier 2
    | (admin approval only). A coupon above the tier 1 caps is tier 2 as well. Amounts and
    | percentages are decimal strings. An empty environment value falls back to the default.
    |
    */
    'pending_action_ttl_minutes' => (int) (env('MCP_PENDING_ACTION_TTL_MINUTES') ?: 15),

    'price_change_tier1_max_pct' => '10',

    'coupon_tier1_max_percent' => (string) (env('MCP_COUPON_TIER1_MAX_PERCENT') ?: '20'),

    'coupon_tier1_max_fixed_base' => (string) (env('MCP_COUPON_TIER1_MAX_FIXED_BASE') ?: '10'),

    /*
    | Upper bound on variants examined by get_low_margin_products in one call.
    */
    'low_margin_scan_limit' => 5000,

    /*
    | Lifetime of an issued token in days.
    */
    'token_ttl_days' => (int) (env('MCP_TOKEN_TTL_DAYS') ?: 90),
];
