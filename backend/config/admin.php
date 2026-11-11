<?php

/*
|--------------------------------------------------------------------------
| Admin panel (M14)
|--------------------------------------------------------------------------
*/

return [

    /*
    | Theme: "dark" (the approved Figma admin frames, owner decision 2026-11-11) or "light"
    | (the Figma `Admin Light` mode). Views read it through the `data-theme` attribute only.
    */
    'theme' => env('ADMIN_THEME', 'dark') === 'light' ? 'light' : 'dark',

    /*
    | Sidebar navigation. An item shows only when its `route` exists and the staff member holds
    | at least one of its `permissions` (hiding is a convenience; every route checks its own
    | permission). Items whose screens arrive in M15 have no route yet and stay hidden.
    */
    'navigation' => [
        ['key' => 'dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard', 'permissions' => ['dashboard.view']],
        ['key' => 'catalog', 'icon' => 'tag', 'tabs' => 'catalog'],
        ['key' => 'suppliers', 'icon' => 'plug', 'route' => 'admin.suppliers.index', 'permissions' => ['suppliers.view']],
        ['key' => 'orders', 'icon' => 'box', 'route' => 'admin.orders.index', 'permissions' => ['orders.view'], 'badge' => 'orders_attention'],
        ['key' => 'customers', 'icon' => 'user', 'route' => 'admin.customers.index', 'permissions' => ['customers.view']],
        ['key' => 'payments', 'icon' => 'card', 'tabs' => 'payments'],
        ['key' => 'pricing', 'icon' => 'percent', 'tabs' => 'pricing'],
        ['key' => 'marketing', 'icon' => 'zap', 'tabs' => 'marketing'],
        ['key' => 'content', 'icon' => 'file', 'tabs' => 'content'],
        ['key' => 'support', 'icon' => 'chat', 'tabs' => 'support'],
        ['key' => 'reviews', 'icon' => 'star', 'route' => 'admin.reviews.index', 'permissions' => ['catalog.manage']],
        ['key' => 'automation', 'icon' => 'activity', 'tabs' => 'automation'],
        ['key' => 'users', 'icon' => 'key', 'tabs' => 'users'],
        ['key' => 'settings', 'icon' => 'sliders', 'route' => 'admin.settings.show', 'permissions' => ['settings.view']],
    ],

    /*
    | Sub-navigation (tabs) of a section (M15). Each tab is its own route with its own
    | permissions; a sidebar entry with `tabs` opens the first tab the staff member may open and
    | is hidden when there is none.
    */
    'sections' => [
        'catalog' => [
            ['key' => 'products', 'route' => 'admin.catalog.products.index', 'permissions' => ['catalog.view']],
            ['key' => 'platforms', 'route' => 'admin.catalog.platforms.index', 'permissions' => ['catalog.view']],
            ['key' => 'categories', 'route' => 'admin.catalog.categories.index', 'permissions' => ['catalog.view']],
            ['key' => 'regions', 'route' => 'admin.catalog.regions.index', 'permissions' => ['catalog.view']],
            ['key' => 'attributes', 'route' => 'admin.catalog.attributes.index', 'permissions' => ['catalog.view']],
            ['key' => 'delivery-fields', 'route' => 'admin.catalog.delivery-fields.index', 'permissions' => ['catalog.view']],
            ['key' => 'mappings', 'route' => 'admin.catalog.mappings.index', 'permissions' => ['mappings.manage']],
        ],
        'payments' => [
            ['key' => 'gateways', 'route' => 'admin.payments.index', 'permissions' => ['payments.view']],
            ['key' => 'transactions', 'route' => 'admin.payments.transactions', 'permissions' => ['payments.view']],
            ['key' => 'webhooks', 'route' => 'admin.payments.webhooks', 'permissions' => ['payments.webhook_logs']],
            ['key' => 'refunds', 'route' => 'admin.payments.refunds', 'permissions' => ['refunds.view']],
            ['key' => 'disputes', 'route' => 'admin.payments.disputes', 'permissions' => ['payments.view']],
            ['key' => 'reconciliation', 'route' => 'admin.payments.reconciliation', 'permissions' => ['payments.view']],
        ],
        'pricing' => [
            ['key' => 'pricing-rules', 'route' => 'admin.pricing.pricing-rules.index', 'permissions' => ['pricing.view']],
            ['key' => 'variant-prices', 'route' => 'admin.pricing.variant-prices.index', 'permissions' => ['pricing.view']],
            ['key' => 'currencies', 'route' => 'admin.pricing.currencies.index', 'permissions' => ['pricing.view', 'currency.manage']],
            ['key' => 'exchange-rates', 'route' => 'admin.pricing.exchange-rates.index', 'permissions' => ['pricing.view', 'currency.manage']],
            ['key' => 'countries', 'route' => 'admin.pricing.countries.index', 'permissions' => ['pricing.view', 'currency.manage']],
        ],
        'marketing' => [
            ['key' => 'coupons', 'route' => 'admin.marketing.coupons.index', 'permissions' => ['marketing.coupons']],
            ['key' => 'promotions', 'route' => 'admin.marketing.promotions.index', 'permissions' => ['marketing.promotions']],
            ['key' => 'affiliates', 'route' => 'admin.marketing.affiliates.index', 'permissions' => ['marketing.affiliates']],
            ['key' => 'banners', 'route' => 'admin.marketing.banners.index', 'permissions' => ['marketing.banners']],
            ['key' => 'homepage-sections', 'route' => 'admin.marketing.homepage-sections.index', 'permissions' => ['content.homepage']],
        ],
        'content' => [
            ['key' => 'pages', 'route' => 'admin.content.pages.index', 'permissions' => ['content.pages']],
            ['key' => 'blog-posts', 'route' => 'admin.content.blog-posts.index', 'permissions' => ['content.blog']],
            ['key' => 'blog-categories', 'route' => 'admin.content.blog-categories.index', 'permissions' => ['content.blog']],
            ['key' => 'faqs', 'route' => 'admin.content.faqs.index', 'permissions' => ['content.faq']],
            ['key' => 'faq-groups', 'route' => 'admin.content.faq-groups.index', 'permissions' => ['content.faq']],
            ['key' => 'menus', 'route' => 'admin.content.menus.index', 'permissions' => ['content.menus']],
            ['key' => 'redirects', 'route' => 'admin.content.redirects.index', 'permissions' => ['seo.redirects']],
            ['key' => 'media', 'route' => 'admin.content.media.index', 'permissions' => ['content.media']],
        ],
        'support' => [
            ['key' => 'tickets', 'route' => 'admin.support.tickets.index', 'permissions' => ['support.view']],
            ['key' => 'refund-requests', 'route' => 'admin.support.refund-requests.index', 'permissions' => ['support.refund_requests']],
        ],
        'automation' => [
            ['key' => 'ai-suggestions', 'route' => 'admin.automation.ai-suggestions.index', 'permissions' => ['ai_suggestions.review']],
            ['key' => 'mcp-actions', 'route' => 'admin.automation.mcp-actions.index', 'permissions' => ['mcp_actions.approve']],
            ['key' => 'mcp-tokens', 'route' => 'admin.automation.mcp-tokens.index', 'permissions' => ['service_accounts.manage']],
            ['key' => 'automation-credentials', 'route' => 'admin.automation.credentials.index', 'permissions' => ['service_accounts.manage']],
        ],
        'users' => [
            ['key' => 'staff', 'route' => 'admin.users.staff.index', 'permissions' => ['users.manage']],
            ['key' => 'roles', 'route' => 'admin.users.roles.index', 'permissions' => ['roles.manage', 'users.manage']],
        ],
    ],

    /*
    | Translatable content (M15, ADR-015): morph type => the permission that edits it and its
    | fields (text, textarea or html; html is sanitized like the base column). The base columns
    | are English; other supported locales are written to `content_translations` through
    | `PUT /admin/api/translations/{type}/{id}`.
    */
    'translations' => [
        'product' => ['permission' => 'catalog.manage', 'fields' => ['name' => 'text', 'short_description' => 'textarea', 'description' => 'html', 'instructions' => 'textarea', 'terms' => 'textarea']],
        'product_variant' => ['permission' => 'catalog.manage', 'fields' => ['title' => 'text']],
        'gaming_platform' => ['permission' => 'catalog.manage', 'fields' => ['name' => 'text', 'description' => 'textarea']],
        'category' => ['permission' => 'catalog.manage', 'fields' => ['name' => 'text', 'description' => 'textarea']],
        'banner' => ['permission' => 'marketing.banners', 'fields' => ['title' => 'text', 'subtitle' => 'text', 'cta_label' => 'text']],
        'homepage_section' => ['permission' => 'content.homepage', 'fields' => ['title' => 'text', 'subtitle' => 'text']],
        'faq' => ['permission' => 'content.faq', 'fields' => ['question' => 'text', 'answer' => 'textarea']],
        'page' => ['permission' => 'content.pages', 'fields' => ['title' => 'text', 'body' => 'html']],
        'blog_post' => ['permission' => 'content.blog', 'fields' => ['title' => 'text', 'excerpt' => 'textarea', 'body' => 'html']],
        'menu_item' => ['permission' => 'content.menus', 'fields' => ['label' => 'text']],
    ],

    /*
    | SEO fields per page type (M15, `seo_metadata`): morph type => the permission that opens the
    | record (`seo.manage` is also required to write).
    */
    'seo' => [
        'product' => 'catalog.view',
        'gaming_platform' => 'catalog.view',
        'category' => 'catalog.view',
        'page' => 'content.pages',
        'blog_post' => 'content.blog',
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
        // M15
        'product_status' => ['active' => 'success', 'draft' => 'neutral', 'inactive' => 'warning'],
        'variant_availability' => ['in_stock' => 'success', 'limited' => 'warning', 'out_of_stock' => 'danger'],
        'affiliate_status' => ['active' => 'success', 'pending' => 'warning', 'suspended' => 'danger'],
        'commission_status' => ['approved' => 'info', 'paid' => 'success', 'pending' => 'warning', 'reversed' => 'neutral'],
        'page_status' => ['published' => 'success', 'draft' => 'neutral'],
        'blog_status' => ['published' => 'success', 'scheduled' => 'info', 'draft' => 'neutral'],
        'mapping_role' => ['primary' => 'accent', 'secondary' => 'info', 'other' => 'neutral'],
        'ticket_status' => ['open' => 'info', 'pending_staff' => 'warning', 'pending_customer' => 'neutral', 'resolved' => 'success', 'closed' => 'neutral'],
        'ticket_priority' => ['urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', 'low' => 'neutral'],
        'refund_request_status' => ['requested' => 'warning', 'approved' => 'info', 'rejected' => 'danger', 'completed' => 'success'],
        'review_status' => ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'],
        'user_status' => ['active' => 'success', 'suspended' => 'danger', 'closed' => 'neutral'],
        'risk_level' => ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'blocked' => 'danger'],
        'wallet_type' => ['credit_refund' => 'info', 'credit_admin' => 'accent', 'debit_purchase' => 'neutral', 'debit_admin' => 'warning', 'reversal' => 'danger'],
        'wallet_status' => ['active' => 'success', 'frozen' => 'danger'],
        'suggestion_status' => ['pending_review' => 'warning', 'approved' => 'success', 'rejected' => 'neutral'],
        'mcp_status' => ['pending' => 'warning', 'approved' => 'info', 'executed' => 'success', 'rejected' => 'neutral', 'failed' => 'danger', 'expired' => 'neutral'],
    ],
];
