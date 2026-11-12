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
|
| Settings from the database (owner request 2026-11-12, `docs/PROGRESS.md` "Settings from the
| database"): a field or secret with `config` is written over that config key at boot by
| SettingsOverlay (cached, re-checked per job in queue workers), so running code keeps reading
| `config()`; the config file keeps the built-in default, which is also this field's default.
| `env` names the former `.env` key that `php artisan settings:import-env` copies once (`env_file`:
| the key holds a path whose file content is imported). `format` adds a runtime rule
| (SettingValueFormat): url, path, path_or_url, app_url, header, android_packages,
| sha256_fingerprints, apple_app_ids, fcm_project_id, fcm_service_account; `placeholders` must
| appear in the value. `upload` lets a secret be pasted or uploaded as a file.
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

        // Owner decision 4: guest checkout on the web and in the app, default off. Critical, since
        // turning it on opens checkout to anonymous buyers.
        'checkout' => [
            'store' => 'checkout',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'guest_checkout' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'storefront.guest_checkout', 'env' => 'STOREFRONT_GUEST_CHECKOUT'],
            ],
        ],

        // Provider return and cancel templates (M-A6, ADR-039), built by PaymentReturnUrlBuilder.
        'payments' => [
            'store' => 'payments',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'return_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'path_or_url', 'placeholders' => ['{order}'], 'config' => 'payments.return_url', 'env' => 'PAYMENTS_RETURN_URL'],
                'cancel_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'path_or_url', 'placeholders' => ['{order}'], 'config' => 'payments.cancel_url', 'env' => 'PAYMENTS_CANCEL_URL'],
                'app_return_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'path', 'placeholders' => ['{order}'], 'config' => 'payments.app_return_url', 'env' => 'PAYMENTS_APP_RETURN_URL'],
                'app_cancel_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'path', 'placeholders' => ['{order}'], 'config' => 'payments.app_cancel_url', 'env' => 'PAYMENTS_APP_CANCEL_URL'],
                'stripe_app_return_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'app_url', 'config' => 'payments.stripe_app_return_url', 'env' => 'PAYMENTS_STRIPE_APP_RETURN_URL'],
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
                'oxr_app_id' => ['rules' => ['string', 'min:8', 'max:120'], 'config' => 'services.open_exchange_rates.app_id', 'env' => 'OPEN_EXCHANGE_RATES_APP_ID'],
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
                'admin_idle_minutes' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:5', 'max:480'], 'config' => 'identity.admin_idle_minutes', 'env' => 'ADMIN_IDLE_MINUTES'],
            ],
        ],

        // IP country for eligibility and the display currency (ADR-016, OD-1). `header` is safe only
        // when the origin accepts traffic from the trusted edge proxy alone.
        'geoip' => [
            'store' => 'geoip',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'geoip_driver' => ['type' => 'select', 'options' => ['null', 'header'], 'rules' => ['sometimes', 'required', 'in:null,header'], 'config' => 'payments.geoip.driver', 'env' => 'GEOIP_DRIVER'],
                'geoip_country_header' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:64'], 'format' => 'header', 'config' => 'payments.geoip.header', 'env' => 'GEOIP_COUNTRY_HEADER'],
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

        // The public app config (AppConfigSetting, group `app`); `env` lists the former `.env` keys
        // that settings:import-env copies into that group.
        'mobile' => [
            'store' => 'app',
            'permission' => 'settings.manage',
            'applied' => true,
            'app_config' => true,
            'fields' => [],
            'env' => [
                'web_checkout_redirect' => 'MOBILE_WEB_CHECKOUT_REDIRECT',
                'web_checkout_url' => 'MOBILE_WEB_CHECKOUT_URL',
                'forgot_password_url' => 'MOBILE_FORGOT_PASSWORD_URL',
                'android_min_version' => 'MOBILE_ANDROID_MIN_VERSION',
                'android_latest_version' => 'MOBILE_ANDROID_LATEST_VERSION',
                'ios_min_version' => 'MOBILE_IOS_MIN_VERSION',
                'ios_latest_version' => 'MOBILE_IOS_LATEST_VERSION',
                'maintenance' => 'MOBILE_MAINTENANCE',
                'checkout_enabled' => 'MOBILE_CHECKOUT_ENABLED',
                'guest_checkout_enabled' => 'MOBILE_GUEST_CHECKOUT_ENABLED',
                'wallet_enabled' => 'MOBILE_WALLET_ENABLED',
                'reviews_enabled' => 'MOBILE_REVIEWS_ENABLED',
                'support_enabled' => 'MOBILE_SUPPORT_ENABLED',
                'support_url' => 'MOBILE_SUPPORT_URL',
                'terms_url' => 'MOBILE_TERMS_URL',
                'privacy_url' => 'MOBILE_PRIVACY_URL',
                'refund_url' => 'MOBILE_REFUND_URL',
            ],
        ],

        // App link association files (M-A6, `/.well-known`) and biometric re-auth (OD-1 decision 6).
        // Critical: a wrong package, fingerprint or app id declares someone else's app.
        'app_links' => [
            'store' => 'app_links',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'android_packages' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:1000'], 'format' => 'android_packages', 'config' => 'mobile.app_links.android_packages', 'env' => 'MOBILE_ANDROID_PACKAGES'],
                'android_sha256_fingerprints' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:2000'], 'format' => 'sha256_fingerprints', 'config' => 'mobile.app_links.android_sha256_fingerprints', 'env' => 'MOBILE_ANDROID_SHA256_FINGERPRINTS'],
                'ios_app_ids' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:1000'], 'format' => 'apple_app_ids', 'config' => 'mobile.app_links.ios_app_ids', 'env' => 'MOBILE_IOS_APP_IDS'],
                'biometric_challenge_ttl_seconds' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:15', 'max:300'], 'config' => 'mobile.biometric.challenge_ttl_seconds', 'env' => 'MOBILE_BIOMETRIC_CHALLENGE_TTL_SECONDS'],
            ],
        ],

        // Email link landings (M-A5): the reset link carries the reset token to this page, so a
        // change is critical.
        'account_links' => [
            'store' => 'account_links',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'password_reset_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'url', 'placeholders' => ['{token}'], 'config' => 'auth.links.password_reset_url', 'env' => 'PASSWORD_RESET_URL'],
                'email_verified_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'url', 'config' => 'auth.links.email_verified_url', 'env' => 'EMAIL_VERIFIED_URL'],
            ],
        ],

        // Push through FCM (M-A18, owner decision 9). The service-account JSON is a write-only
        // secret read by FcmClient; it never reaches config.
        'push' => [
            'store' => 'push',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'push_enabled' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'push.enabled', 'env' => 'FCM_ENABLED'],
                'fcm_project_id' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:30'], 'format' => 'fcm_project_id', 'config' => 'push.fcm.project_id', 'env' => 'FCM_PROJECT_ID'],
            ],
            'secrets' => [
                'fcm_service_account' => ['rules' => ['string', 'max:20000'], 'format' => 'fcm_service_account', 'upload' => true, 'env' => 'FCM_CREDENTIALS_PATH', 'env_file' => true],
            ],
        ],

        // MCP server limits (M16, MCP_SERVER.md).
        'mcp' => [
            'store' => 'mcp',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'pending_action_ttl_minutes' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:1440'], 'config' => 'mcp.pending_action_ttl_minutes', 'env' => 'MCP_PENDING_ACTION_TTL_MINUTES'],
                'coupon_tier1_max_percent' => ['type' => 'decimal', 'rules' => ['sometimes', 'required', 'regex:/^\d{1,3}(\.\d{1,4})?$/', 'numeric', 'max:100'], 'config' => 'mcp.coupon_tier1_max_percent', 'env' => 'MCP_COUPON_TIER1_MAX_PERCENT'],
                'coupon_tier1_max_fixed_base' => ['type' => 'decimal', 'rules' => ['sometimes', 'required', 'regex:/^\d{1,12}(\.\d{1,6})?$/'], 'config' => 'mcp.coupon_tier1_max_fixed_base', 'env' => 'MCP_COUPON_TIER1_MAX_FIXED_BASE'],
                'mcp_token_ttl_days' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:365'], 'config' => 'mcp.token_ttl_days', 'env' => 'MCP_TOKEN_TTL_DAYS'],
            ],
        ],

        // n8n (M17, N8N_AUTOMATIONS.md). Critical: the webhook URL receives store events and the
        // secrets sign them.
        'automation' => [
            'store' => 'automation',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'task_drivers' => ['type' => 'json', 'rules' => ['nullable', 'array:catalog_sync,exchange_rates,payment_reconciliation'], 'item_rules' => ['in:scheduler,n8n'], 'default' => []],
                'outbox_enabled' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'automation.outbox.enabled', 'env' => 'AUTOMATION_OUTBOX_ENABLED'],
                'webhook_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:500'], 'format' => 'url', 'config' => 'automation.outbox.webhook_url', 'env' => 'N8N_WEBHOOK_URL'],
                'automation_store_name' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:120'], 'config' => 'automation.store_name', 'env' => 'AUTOMATION_STORE_NAME'],
                'automation_token_ttl_days' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:3650'], 'config' => 'automation.token_ttl_days', 'env' => 'AUTOMATION_TOKEN_TTL_DAYS'],
            ],
            'secrets' => [
                'outbound_secret' => ['rules' => ['string', 'min:16', 'max:255'], 'config' => 'automation.outbox.secret', 'env' => 'N8N_OUTBOUND_SECRET'],
                'webhook_auth_token' => ['rules' => ['string', 'min:16', 'max:255'], 'config' => 'automation.outbox.auth_token', 'env' => 'N8N_WEBHOOK_AUTH_TOKEN'],
            ],
        ],

        'seo' => [
            'store' => 'seo',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'block_all_crawlers' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'seo.robots.block_all', 'env' => 'SEO_BLOCK_ALL_CRAWLERS'],
            ],
        ],

        'admin' => [
            'store' => 'admin',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'admin_theme' => ['type' => 'select', 'options' => ['dark', 'light'], 'rules' => ['sometimes', 'required', 'in:dark,light'], 'config' => 'admin.theme', 'env' => 'ADMIN_THEME'],
            ],
        ],
    ],
];
