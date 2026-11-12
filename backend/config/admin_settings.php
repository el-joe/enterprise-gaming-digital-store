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
| sha256_fingerprints, apple_app_ids, fcm_project_id, fcm_service_account, host, cookie_domain,
| disk, locale_code; `placeholders` must appear in the value. `upload` lets a secret be pasted or
| uploaded as a file.
|
| Owner decision 11 ("only the Super Admin and technical bootstrap keys stay in .env"): every
| other runtime key is a setting here. `vault_keys` (group digital_codes) are the code-vault keys:
| applied like the others, the key material encrypted with APP_KEY, but never shown or edited on
| a screen; only the artisan commands of DigitalCodeKeyManager write them. `derived` config keys
| follow another config key while no setting of their own is stored (the session cookie name, the
| mail sender name and the n8n store name follow the store name; the public disk URL and the
| SMTP EHLO domain follow the store address).
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

        // Store name and languages (owner decision 11).
        'site' => [
            'store' => 'site',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'app_name' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:120'], 'config' => 'app.name', 'env' => 'APP_NAME'],
                'brand_name' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:120'], 'config' => 'brand.name', 'env' => 'BRAND_NAME'],
                'locale' => ['type' => 'select', 'options' => ['en', 'ar'], 'rules' => ['sometimes', 'required', 'in:en,ar'], 'config' => 'app.locale', 'env' => 'APP_LOCALE'],
                'fallback_locale' => ['type' => 'select', 'options' => ['en', 'ar'], 'rules' => ['sometimes', 'required', 'in:en,ar'], 'config' => 'app.fallback_locale', 'env' => 'APP_FALLBACK_LOCALE'],
                'faker_locale' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:12'], 'format' => 'locale_code', 'config' => 'app.faker_locale', 'env' => 'APP_FAKER_LOCALE'],
            ],
        ],

        // The store address and the session cookie (owner decision 11). Critical: the address is
        // where every link, app link and payment return points; the cookie carries the sign-in.
        'session' => [
            'store' => 'session',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'app_url' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:190'], 'format' => 'url', 'config' => 'app.url', 'env' => 'APP_URL'],
                'session_lifetime' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:5', 'max:43200'], 'config' => 'session.lifetime', 'env' => 'SESSION_LIFETIME'],
                'session_expire_on_close' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'session.expire_on_close', 'env' => 'SESSION_EXPIRE_ON_CLOSE'],
                'session_encrypt' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'session.encrypt', 'env' => 'SESSION_ENCRYPT'],
                'session_secure_cookie' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'session.secure', 'env' => 'SESSION_SECURE_COOKIE'],
                'session_same_site' => ['type' => 'select', 'options' => ['lax', 'strict', 'none'], 'rules' => ['sometimes', 'required', 'in:lax,strict,none'], 'config' => 'session.same_site', 'env' => 'SESSION_SAME_SITE'],
                'session_partitioned_cookie' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'session.partitioned', 'env' => 'SESSION_PARTITIONED_COOKIE'],
                'session_path' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:190'], 'format' => 'path', 'config' => 'session.path', 'env' => 'SESSION_PATH'],
                'session_domain' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'cookie_domain', 'config' => 'session.domain', 'env' => 'SESSION_DOMAIN'],
                'session_cookie' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'regex:/^[A-Za-z0-9_-]{1,64}$/'], 'config' => 'session.cookie', 'env' => 'SESSION_COOKIE'],
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
                'http_connect_timeout' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:60'], 'config' => 'payments.http.connect_timeout', 'env' => 'PAYMENTS_CONNECT_TIMEOUT'],
                'http_timeout' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:120'], 'config' => 'payments.http.timeout', 'env' => 'PAYMENTS_TIMEOUT'],
            ],
            // ADR-023: pinned with the code that parses Stripe responses; shown, never edited.
            'readonly' => [
                'stripe_api_version' => 'payments.stripe.api_version',
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
                'rates_provider' => ['type' => 'select', 'options' => ['currency_api', 'open_exchange_rates'], 'rules' => ['sometimes', 'required', 'in:currency_api,open_exchange_rates'], 'config' => 'services.exchange_rates.provider', 'default' => 'currency_api'],
                'max_change_pct' => ['type' => 'decimal', 'rules' => ['required', 'regex:/^\d{1,3}(\.\d{1,4})?$/'], 'default' => '10'],
                'oxr_base_url' => ['type' => 'string', 'rules' => ['sometimes', 'required', 'string', 'max:190'], 'format' => 'url', 'config' => 'services.open_exchange_rates.base_url', 'env' => 'OPEN_EXCHANGE_RATES_BASE_URL'],
                'oxr_connect_timeout' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:60'], 'config' => 'services.open_exchange_rates.connect_timeout', 'env' => 'OPEN_EXCHANGE_RATES_CONNECT_TIMEOUT'],
                'oxr_timeout' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:120'], 'config' => 'services.open_exchange_rates.timeout', 'env' => 'OPEN_EXCHANGE_RATES_TIMEOUT'],
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
                'password_timeout' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:300', 'max:86400'], 'config' => 'auth.password_timeout', 'env' => 'AUTH_PASSWORD_TIMEOUT'],
                'mobile_access_token_ttl_minutes' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:5', 'max:1440'], 'config' => 'mobile.auth.access_token_ttl_minutes', 'env' => 'MOBILE_ACCESS_TOKEN_TTL_MINUTES'],
                'mobile_refresh_token_ttl_days' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:365'], 'config' => 'mobile.auth.refresh_token_ttl_days', 'env' => 'MOBILE_REFRESH_TOKEN_TTL_DAYS'],
                'mobile_2fa_challenge_ttl_seconds' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:60', 'max:1800'], 'config' => 'mobile.auth.challenge_ttl_seconds', 'env' => 'MOBILE_2FA_CHALLENGE_TTL_SECONDS'],
                'guest_order_token_ttl_hours' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:720'], 'config' => 'orders.guest_order_token_ttl_hours', 'env' => 'GUEST_ORDER_TOKEN_TTL_HOURS'],
                'api_token_prefix' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'regex:/^[a-z0-9_]{1,20}$/'], 'config' => 'sanctum.token_prefix', 'env' => 'SANCTUM_TOKEN_PREFIX'],
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

        // Outgoing mail (owner decision 11: read by the mailer through SettingsOverlay; no longer
        // `.env`). Critical: the server credentials, and the sender every customer sees.
        'mail' => [
            'store' => 'mail',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'mailer' => ['type' => 'select', 'options' => ['smtp', 'sendmail', 'log', 'failover'], 'rules' => ['sometimes', 'required', 'in:smtp,sendmail,log,failover'], 'config' => 'mail.default', 'env' => 'MAIL_MAILER'],
                'smtp_scheme' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'in:smtp,smtps'], 'config' => 'mail.mailers.smtp.scheme', 'env' => 'MAIL_SCHEME'],
                'smtp_host' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'host', 'config' => 'mail.mailers.smtp.host', 'env' => 'MAIL_HOST'],
                'smtp_port' => ['type' => 'int', 'rules' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'], 'config' => 'mail.mailers.smtp.port', 'env' => 'MAIL_PORT'],
                'smtp_username' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'config' => 'mail.mailers.smtp.username', 'env' => 'MAIL_USERNAME'],
                'from_address' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'email:rfc', 'max:190'], 'config' => 'mail.from.address', 'env' => 'MAIL_FROM_ADDRESS'],
                'from_name' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:120'], 'config' => 'mail.from.name', 'env' => 'MAIL_FROM_NAME'],
                'ehlo_domain' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'host', 'config' => 'mail.mailers.smtp.local_domain', 'env' => 'MAIL_EHLO_DOMAIN'],
            ],
            'secrets' => [
                'smtp_password' => ['rules' => ['string', 'max:255'], 'config' => 'mail.mailers.smtp.password', 'env' => 'MAIL_PASSWORD'],
            ],
        ],

        // Files (owner decision 11). Critical: the storage credentials, and where files and media
        // are served from. S3 needs league/flysystem-aws-s3-v3 (not installed): `s3` is refused
        // until it is.
        'storage' => [
            'store' => 'storage',
            'permission' => 'settings.manage_critical',
            'applied' => true,
            'fields' => [
                'filesystem_disk' => ['type' => 'select', 'options' => ['local', 'public', 's3'], 'rules' => ['sometimes', 'required', 'in:local,public,s3'], 'format' => 'disk', 'config' => 'filesystems.default', 'env' => 'FILESYSTEM_DISK'],
                'sitemap_disk' => ['type' => 'select', 'options' => ['local', 'public', 's3'], 'rules' => ['sometimes', 'required', 'in:local,public,s3'], 'format' => 'disk', 'config' => 'seo.sitemaps.disk', 'env' => 'SEO_SITEMAP_DISK'],
                'media_base_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'url', 'config' => 'catalog.media.base_url', 'env' => 'MEDIA_BASE_URL'],
                'aws_access_key_id' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'regex:/^[A-Z0-9]{16,128}$/'], 'config' => 'filesystems.disks.s3.key', 'env' => 'AWS_ACCESS_KEY_ID'],
                'aws_default_region' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'regex:/^[a-z0-9-]{2,32}$/'], 'config' => 'filesystems.disks.s3.region', 'env' => 'AWS_DEFAULT_REGION'],
                'aws_bucket' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'regex:/^[a-z0-9][a-z0-9.-]{1,61}[a-z0-9]$/'], 'config' => 'filesystems.disks.s3.bucket', 'env' => 'AWS_BUCKET'],
                'aws_url' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'url', 'config' => 'filesystems.disks.s3.url', 'env' => 'AWS_URL'],
                'aws_endpoint' => ['type' => 'string', 'rules' => ['sometimes', 'nullable', 'string', 'max:190'], 'format' => 'url', 'config' => 'filesystems.disks.s3.endpoint', 'env' => 'AWS_ENDPOINT'],
                'aws_use_path_style_endpoint' => ['type' => 'bool', 'rules' => ['sometimes', 'required', 'boolean'], 'config' => 'filesystems.disks.s3.use_path_style_endpoint', 'env' => 'AWS_USE_PATH_STYLE_ENDPOINT'],
            ],
            'secrets' => [
                'aws_secret_access_key' => ['rules' => ['string', 'min:16', 'max:255'], 'config' => 'filesystems.disks.s3.secret', 'env' => 'AWS_SECRET_ACCESS_KEY'],
            ],
        ],

        // Affiliate attribution window (M11, owner decision 11).
        'affiliates' => [
            'store' => 'affiliates',
            'permission' => 'settings.manage',
            'applied' => true,
            'fields' => [
                'attribution_days' => ['type' => 'int', 'rules' => ['sometimes', 'required', 'integer', 'min:1', 'max:365'], 'config' => 'affiliates.attribution_days', 'env' => 'AFFILIATE_ATTRIBUTION_DAYS'],
            ],
        ],

        // The code-vault keys (owner decision 11, SECURITY.md 5): applied over config/fulfillment.php,
        // the key material encrypted with APP_KEY. Never shown or edited here: the screen shows
        // only whether they are configured, the active key id and the number of keys, to holders
        // of both `view_permissions`. Written only by `codes:generate-keys`, `codes:rotate-key`,
        // `codes:rebuild-blind-index` and `settings:import-env` (DigitalCodeKeyManager).
        'digital_codes' => [
            'store' => 'digital_codes',
            'permission' => 'settings.manage_critical',
            'view_permissions' => ['settings.manage_critical', 'digital_codes.reveal'],
            'applied' => true,
            'fields' => [],
            'vault_keys' => [
                'code_keys' => ['type' => 'string', 'encrypted' => true, 'config' => 'fulfillment.code_keys', 'env' => 'DIGITAL_CODE_KEYS'],
                'active_kid' => ['type' => 'int', 'encrypted' => false, 'config' => 'fulfillment.active_kid', 'env' => 'DIGITAL_CODE_ACTIVE_KID'],
                'blind_index_key' => ['type' => 'string', 'encrypted' => true, 'config' => 'fulfillment.blind_index_key', 'env' => 'DIGITAL_CODE_BLIND_INDEX_KEY'],
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

    /*
    | Config keys that follow another config key while no setting of their own is stored
    | (SettingsOverlay). `as`: same, host (the URL's host), storage_url (URL + /storage),
    | session_cookie (slug of the name + -session, Laravel's own default).
    */
    'derived' => [
        'session.cookie' => ['from' => 'app.name', 'as' => 'session_cookie'],
        'mail.from.name' => ['from' => 'app.name', 'as' => 'same'],
        'automation.store_name' => ['from' => 'app.name', 'as' => 'same'],
        'mail.mailers.smtp.local_domain' => ['from' => 'app.url', 'as' => 'host'],
        'filesystems.disks.public.url' => ['from' => 'app.url', 'as' => 'storage_url'],
    ],
];
