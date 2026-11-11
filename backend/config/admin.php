<?php

/*
|--------------------------------------------------------------------------
| Admin panel (M14)
|--------------------------------------------------------------------------
*/

return [

    /*
    | Sidebar navigation. An item shows only when its `route` exists and the staff member holds
    | at least one of its `permissions` (hiding is a convenience; every route checks its own
    | permission). Items whose screens arrive in M15 have no route yet and stay hidden.
    */
    'navigation' => [
        ['key' => 'dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard', 'permissions' => ['dashboard.view']],
        ['key' => 'catalog', 'icon' => 'tag', 'route' => null, 'permissions' => ['catalog.view']],
        ['key' => 'suppliers', 'icon' => 'plug', 'route' => 'admin.suppliers.index', 'permissions' => ['suppliers.view']],
        ['key' => 'orders', 'icon' => 'box', 'route' => 'admin.orders.index', 'permissions' => ['orders.view'], 'badge' => 'orders_attention'],
        ['key' => 'customers', 'icon' => 'user', 'route' => null, 'permissions' => ['customers.view']],
        ['key' => 'payments', 'icon' => 'card', 'route' => 'admin.payments.index', 'permissions' => ['payments.view', 'payments.webhook_logs', 'refunds.view']],
        ['key' => 'pricing', 'icon' => 'percent', 'route' => null, 'permissions' => ['pricing.view']],
        ['key' => 'marketing', 'icon' => 'zap', 'route' => null, 'permissions' => ['marketing.coupons', 'marketing.promotions', 'marketing.affiliates', 'marketing.banners']],
        ['key' => 'content', 'icon' => 'file', 'route' => null, 'permissions' => ['content.pages', 'content.blog', 'content.faq', 'content.menus', 'content.homepage', 'content.media']],
        ['key' => 'support', 'icon' => 'chat', 'route' => null, 'permissions' => ['support.view']],
        ['key' => 'reviews', 'icon' => 'star', 'route' => null, 'permissions' => ['catalog.manage']],
        ['key' => 'users', 'icon' => 'key', 'route' => null, 'permissions' => ['users.manage', 'roles.manage']],
        ['key' => 'settings', 'icon' => 'sliders', 'route' => null, 'permissions' => ['settings.view']],
    ],

    /*
    | Payments sub-navigation (tabs). Each tab is its own route with its own permission.
    */
    'payment_tabs' => [
        ['key' => 'gateways', 'route' => 'admin.payments.index', 'permissions' => ['payments.view']],
        ['key' => 'transactions', 'route' => 'admin.payments.transactions', 'permissions' => ['payments.view']],
        ['key' => 'webhooks', 'route' => 'admin.payments.webhooks', 'permissions' => ['payments.webhook_logs']],
        ['key' => 'refunds', 'route' => 'admin.payments.refunds', 'permissions' => ['refunds.view']],
        ['key' => 'disputes', 'route' => 'admin.payments.disputes', 'permissions' => ['payments.view']],
        ['key' => 'reconciliation', 'route' => 'admin.payments.reconciliation', 'permissions' => ['payments.view']],
    ],

    /*
    | Write-only credential fields per adapter key. The replace form asks for every field of the
    | mode; values are never read back (only "configured" or "not configured" is shown).
    */
    'credential_fields' => [
        'suppliers' => [
            'reloadly_giftcards' => ['client_id', 'client_secret'],
        ],
        'gateways' => [
            'stripe' => ['secret_key', 'publishable_key'],
            'paypal' => ['client_id', 'client_secret', 'webhook_id'],
            'paymob' => ['api_key', 'secret_key', 'public_key'],
        ],
    ],

    /*
    | Server-side tables: page sizes offered, and the export row cap (a queued CSV or XLSX file,
    | kept for `export_ttl_hours`).
    */
    'tables' => [
        'page_lengths' => [10, 25, 50, 100],
        'default_page_length' => 25,
        'export_max_rows' => 50000,
        'export_ttl_hours' => 24,
        'saved_filters_per_table' => 20,
    ],

    'dashboard' => [
        'ranges' => [7, 30, 90],
        'default_range' => 30,
    ],

    /*
    | Badge tone per enum value (success, info, warning, danger, accent, neutral); labels are in
    | lang/{locale}/admin.php `enums`. Unlisted values are neutral.
    */
    'tones' => [
        'order_status' => [
            'completed' => 'success', 'paid' => 'info', 'processing' => 'info', 'supplier_pending' => 'info', 'payment_processing' => 'info',
            'pending_payment' => 'neutral', 'pending_verification' => 'warning', 'manual_review' => 'warning', 'partially_fulfilled' => 'warning',
            'refund_pending' => 'warning', 'failed' => 'danger', 'cancelled' => 'neutral', 'refunded' => 'neutral', 'partially_refunded' => 'neutral',
        ],
        'unit_status' => [
            'delivered' => 'success', 'pending' => 'neutral', 'queued' => 'info', 'submitting' => 'info', 'supplier_pending' => 'info',
            'pending_verification' => 'warning', 'manual_task' => 'warning', 'manual_review' => 'warning', 'failed' => 'danger',
            'refund_pending' => 'warning', 'refunded' => 'neutral', 'cancelled' => 'neutral',
        ],
        'payment_status' => [
            'succeeded' => 'success', 'created' => 'neutral', 'requires_action' => 'warning', 'processing' => 'info', 'failed' => 'danger',
            'cancelled' => 'neutral', 'expired' => 'neutral', 'partially_refunded' => 'neutral', 'refunded' => 'neutral', 'duplicate_capture' => 'danger',
        ],
        'refund_status' => ['requested' => 'info', 'pending' => 'warning', 'succeeded' => 'success', 'failed' => 'danger', 'unknown' => 'warning'],
        'webhook_status' => ['received' => 'info', 'processed' => 'success', 'ignored' => 'neutral', 'failed' => 'danger'],
        'dispute_status' => ['open' => 'warning', 'won' => 'success', 'lost' => 'danger', 'closed' => 'neutral'],
        'reconciliation_status' => ['open' => 'warning', 'resolved' => 'success', 'ignored' => 'neutral'],
        'supplier_health' => ['healthy' => 'success', 'degraded' => 'warning', 'down' => 'danger', 'unknown' => 'neutral'],
        'supplier_status' => ['active' => 'success', 'inactive' => 'neutral'],
        'transaction_status' => [
            'succeeded' => 'success', 'accepted' => 'info', 'submitting' => 'info', 'pending_verification' => 'warning',
            'rejected' => 'danger', 'not_accepted_verified' => 'danger', 'failed' => 'danger',
        ],
        'risk_decision' => ['pass' => 'success', 'review' => 'warning', 'block' => 'danger'],
        'capability' => ['full' => 'success', 'advisory' => 'warning', 'none' => 'neutral', 'unverified' => 'info'],
        'mode' => ['sandbox' => 'warning', 'production' => 'success'],
    ],
];
