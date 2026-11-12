<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Guest order tokens (M-A3, ADR-036)
    |--------------------------------------------------------------------------
    |
    | Lifetime of the opaque token a stateless guest client receives from place-order, the
    | signed delivery link or the email one-time code. It stands in for the browser session.
    |
    */
    // Admin setting security.guest_order_token_ttl_hours (owner decision 11).
    'guest_order_token_ttl_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | Risk engine (PROJECT_ARCHITECTURE.md section 15)
    |--------------------------------------------------------------------------
    |
    | Weights of each signal. The review and block thresholds are admin settings
    | (`security.risk_review_score`, `security.risk_block_score`), as is the first-order
    | amount (`security.risk_first_order_amount_base`).
    |
    */
    'risk' => [
        'weights' => [
            'guest_checkout' => 10,
            'new_account' => 15,
            'first_order_high_value' => 20,
            'value_above_history' => 15,
            'country_mismatch' => 20,
            'card_country_mismatch' => 20,
            'buyer_velocity' => 20,
            'ip_velocity' => 15,
            'card_velocity' => 25,
            'failed_payment_attempts' => 15,
            'disposable_email' => 25,
        ],
        'velocity_window_minutes' => 60,
        'buyer_velocity_threshold' => 3,
        'ip_velocity_threshold' => 5,
        'card_velocity_threshold' => 3,
        'failed_attempts_threshold' => 3,
        'new_account_hours' => 24,
        'history_multiplier' => '3',
        'disposable_email_domains' => [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com', 'sharklasers.com', 'tempmail.com',
            'temp-mail.org', 'throwawaymail.com', 'trashmail.com', 'yopmail.com', 'getnada.com',
        ],
    ],
];
