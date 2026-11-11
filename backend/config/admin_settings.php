<?php

/*
|--------------------------------------------------------------------------
| Admin settings screens (M15, spec Phase 9.12)
|--------------------------------------------------------------------------
|
| Each group is one screen of /admin/settings/{group}. `store` is the settings group written
| (`settings` table, SettingsService), `permission` the one that saves it (critical groups need
| `settings.manage_critical`, an explicit grant), and `applied` says whether running code reads
| the values today (false: stored for a later milestone and labelled so on the screen).
|
| Field types: string, int, decimal (a decimal string, never a float), bool, select, json.
| `default` is what the code uses when nothing is stored. Secrets are write-only: stored
| encrypted, shown only as configured or not configured, replaced or cleared, never read back.
| Read-only rows show values that live in config or are fixed by decision.
*/

return [

    'groups' => [

        'general' => [
            'store' => 'general',
            'permission' => 'settings.manage',
            'applied' => false,
            'fields' => [
                'store_name' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:120'], 'default' => 'boxcards.shop'],
                'support_email' => ['type' => 'string', 'rules' => ['nullable', 'email:rfc', 'max:190'], 'default' => null],
                'legal_entity' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:190'], 'default' => null],
            ],
        ],

        'orders' => [
            'store' => 'orders',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'max_payment_attempts' => ['type' => 'int', 'rules' => ['required', 'integer', 'min:1', 'max:10'], 'default' => 5],
                'quote_ttl_minutes' => ['type' => 'int', 'rules' => ['required', 'integer', 'min:5', 'max:120'], 'default' => 30],
            ],
        ],

        'checkout' => [
            'store' => null,
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [],
            'readonly' => [
                'guest_checkout' => 'storefront.guest_checkout',
            ],
        ],

        'pricing' => [
            'store' => 'pricing',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'default_min_margin_pct' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,3}(\.\d{1,4})?$/'], 'default' => '0'],
                'default_min_margin_fixed' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,12}(\.\d{1,6})?$/'], 'default' => '0'],
                'default_below_margin_policy' => ['type' => 'select', 'options' => ['block', 'floor', 'allow'], 'rules' => ['required', 'in:block,floor,allow'], 'default' => 'block'],
                'default_gateway_fee_pct' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,3}(\.\d{1,4})?$/'], 'default' => '0'],
                'default_gateway_fee_fixed' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,12}(\.\d{1,6})?$/'], 'default' => '0'],
                'conversion_cost_pct' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,3}(\.\d{1,4})?$/'], 'default' => '0'],
            ],
        ],

        'currency' => [
            'store' => 'currency',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'max_age_minutes' => ['type' => 'int', 'rules' => ['required', 'integer', 'min:60', 'max:10080'], 'default' => 1440],
                'max_change_pct' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,3}(\.\d{1,4})?$/'], 'default' => '10'],
            ],
            'secrets' => [
                'oxr_app_id' => ['rules' => ['string', 'min:8', 'max:120']],
            ],
            'readonly' => [
                'base' => 'settings:currency.base:USD',
            ],
        ],

        'security' => [
            'store' => 'security',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'risk_review_score' => ['type' => 'int', 'rules' => ['required', 'integer', 'min:0', 'max:1000'], 'default' => 40],
                'risk_block_score' => ['type' => 'int', 'rules' => ['required', 'integer', 'min:0', 'max:1000', 'gt:risk_review_score'], 'default' => 80],
                'risk_first_order_amount_base' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,12}(\.\d{1,6})?$/'], 'default' => '100'],
            ],
        ],

        'suppliers' => [
            'store' => 'suppliers',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'selection_strategy' => ['type' => 'select', 'options' => ['priority', 'lowest_cost'], 'rules' => ['required', 'in:priority,lowest_cost'], 'default' => 'priority'],
                'sender_name' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:64'], 'default' => null],
            ],
        ],

        'notifications' => [
            'store' => 'notifications',
            'permission' => 'settings.manage',
            'applied' => false,
            'fields' => [
                'staff_alert_email' => ['type' => 'string', 'rules' => ['nullable', 'email:rfc', 'max:190'], 'default' => null],
                'order_receipts' => ['type' => 'bool', 'rules' => ['required', 'boolean'], 'default' => true],
            ],
        ],

        'mail' => [
            'store' => 'mail',
            'permission' => 'settings.manage_critical',
            'applied' => false,
            'fields' => [
                'smtp_host' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:190'], 'default' => null],
                'smtp_port' => ['type' => 'int', 'rules' => ['nullable', 'integer', 'min:1', 'max:65535'], 'default' => null],
                'from_address' => ['type' => 'string', 'rules' => ['nullable', 'email:rfc', 'max:190'], 'default' => null],
            ],
            'secrets' => [
                'smtp_password' => ['rules' => ['string', 'max:255']],
            ],
        ],

        // M18 (SEO_STRATEGY.md section 12, INTEGRATIONS.md sections 6 and 7): read by
        // AnalyticsSettings. Public ids reach the browser; secrets and the Search Console token
        // (written only by the OAuth flow) never do.
        'analytics' => [
            'store' => 'analytics',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'ga4_measurement_id' => ['type' => 'string', 'rules' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,20}$/'], 'default' => null],
                'gtm_container_id' => ['type' => 'string', 'rules' => ['nullable', 'string', 'regex:/^GTM-[A-Z0-9]{4,12}$/'], 'default' => null],
                'browser_events' => ['type' => 'bool', 'rules' => ['required', 'boolean'], 'default' => true],
                'server_purchase' => ['type' => 'bool', 'rules' => ['required', 'boolean'], 'default' => true],
                'mp_region' => ['type' => 'select', 'options' => ['global', 'eu'], 'rules' => ['required', 'in:global,eu'], 'default' => 'global'],
                'search_console_property' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:190', 'regex:~^(sc-domain:[a-z0-9.-]+|https://[^\s/]+/)$~'], 'default' => null],
                'search_console_client_id' => ['type' => 'string', 'rules' => ['nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9._-]+\.apps\.googleusercontent\.com$/'], 'default' => null],
                'search_console_verification' => ['type' => 'string', 'rules' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_-]{10,100}$/'], 'default' => null],
                'faq_schema' => ['type' => 'bool', 'rules' => ['required', 'boolean'], 'default' => false],
            ],
            'secrets' => [
                'ga4_api_secret' => ['rules' => ['string', 'min:8', 'max:120']],
                'search_console_client_secret' => ['rules' => ['string', 'min:8', 'max:190']],
            ],
        ],

        'mobile' => [
            'store' => 'app',
            'permission' => 'settings.manage',
            'applied' => true,
            'app_config' => true,
            'fields' => [],
        ],

        'automation' => [
            'store' => 'automation',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'task_drivers' => ['type' => 'json', 'rules' => ['nullable', 'array:catalog_sync,exchange_rates,payment_reconciliation'], 'item_rules' => ['in:scheduler,n8n'], 'default' => []],
            ],
        ],
    ],
];
