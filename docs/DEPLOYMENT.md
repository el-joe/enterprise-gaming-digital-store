# Deployment

Target: single Ubuntu LTS VPS, Nginx, PHP-FPM 8.3, MySQL 8, Redis 7, Supervisor, n8n (no Docker). Status: planned. **Nginx, Supervisor and n8n are not installed on the development machine, so the configurations below have not been executed.**

## 1. Sizing and layout

Start: 4 vCPU, 8 GB RAM, 80 GB SSD. A static public IP is required, because Reloadly's higher order limit needs IP whitelisting. Scale-out path: move MySQL, then Redis, then add app nodes (all scheduled commands use `onOneServer`, sessions and cache are in Redis, media moves to object storage).

```
/var/www/egs/
  releases/<timestamp>/    backend, built assets
  shared/                  .env, storage/
  current -> releases/<timestamp>
/opt/n8n/                  n8n via npm, user n8n
/opt/mcp-server/           optional, if HTTP transport is used
```

## 2. Packages

`nginx php8.3-fpm php8.3-{cli,bcmath,gd,intl,mbstring,mysql,redis,zip,curl,xml} mysql-server redis-server supervisor certbot python3-certbot-nginx nodejs` (Node 20 or later).

## 3. Environment

Required production values (no real values are ever committed). `.env` holds only what must exist before the database can be read, what protects data stored in the database, and code-coupled or infrastructure values:

```
APP_ENV=production          APP_DEBUG=false          APP_URL=https://example.com
APP_KEY=                    APP_PREVIOUS_KEYS=
DB_CONNECTION=mysql         DB_DATABASE=egs          DB_USERNAME=egs_app   DB_PASSWORD=
DB_MAINT_USERNAME=egs_maint DB_MAINT_PASSWORD=
CACHE_STORE=redis           SESSION_DRIVER=redis     QUEUE_CONNECTION=redis
SESSION_SECURE_COOKIE=true  SESSION_SAME_SITE=lax
REDIS_HOST=127.0.0.1        REDIS_PASSWORD=
MAIL_MAILER=smtp            (host, user, password, from)
DIGITAL_CODE_KEYS=          DIGITAL_CODE_ACTIVE_KID=  DIGITAL_CODE_BLIND_INDEX_KEY=
SUPER_ADMIN_EMAIL=          SUPER_ADMIN_PASSWORD=     (first deploy only, removed after)
STRIPE_API_VERSION=         (empty uses the pinned version)
SEO_SITEMAP_DISK=local
GEOIP_DATABASE_PATH=        MAXMIND_LICENSE_KEY=      (only if GeoLite2 is ever built, BD9)
```

### 3.1 Which keys stay in `.env`, and why

| Keys | Why they stay |
|---|---|
| `DIGITAL_CODE_KEYS`, `DIGITAL_CODE_ACTIVE_KID`, `DIGITAL_CODE_BLIND_INDEX_KEY` | They encrypt and index the code vault. Stored next to the ciphertext in the same database, one database dump would hold both the codes and their keys, which defeats the encryption. Back them up separately. |
| `SUPER_ADMIN_EMAIL`, `SUPER_ADMIN_PASSWORD` | They bootstrap the first admin, before anyone can sign in to edit settings. Remove them after the first deploy. |
| `STRIPE_API_VERSION` | Code-coupled (ADR-023): webhook and response shapes depend on it, so it changes with a release, not from a screen. |
| `SEO_SITEMAP_DISK` | Infrastructure: which filesystem disk the server writes to. |
| `APP_*`, `DB_*`, cache, queue, session, mail transport, Redis, AWS, logs, `VITE_*`, `BCRYPT_ROUNDS` and the other Laravel framework keys | Needed to boot the framework and reach the database at all. `APP_KEY` also encrypts the secret settings. |

### 3.2 Settings from the database

Everything else this project added is an admin setting (Admin > Settings), stored in the `settings` table and applied over the matching config key at boot (`SettingsOverlay`); the secrets (`currency.oxr_app_id`, `automation.outbound_secret`, `automation.webhook_auth_token`, the FCM service-account JSON `push.fcm_service_account`) are encrypted with `APP_KEY` and write-only. A save takes effect at once in web requests; queue workers re-check a cached version before each job, so no `queue:restart` is needed. `config:cache` never captures these values (the overlay does not run during `config:cache` or `optimize`), so a settings change needs no cache rebuild.

| Settings group (screen) | Keys (former `.env` key) | Permission |
|---|---|---|
| `payments` (Payment returns) | `return_url` (`PAYMENTS_RETURN_URL`), `cancel_url` (`PAYMENTS_CANCEL_URL`), `app_return_url` (`PAYMENTS_APP_RETURN_URL`), `app_cancel_url` (`PAYMENTS_APP_CANCEL_URL`), `stripe_app_return_url` (`PAYMENTS_STRIPE_APP_RETURN_URL`) | `settings.manage` |
| `currency` | secret `oxr_app_id` (`OPEN_EXCHANGE_RATES_APP_ID`) | `settings.manage_critical` |
| `geoip` | `geoip_driver` (`GEOIP_DRIVER`), `geoip_country_header` (`GEOIP_COUNTRY_HEADER`) | `settings.manage_critical` |
| `mcp` | `pending_action_ttl_minutes`, `coupon_tier1_max_percent`, `coupon_tier1_max_fixed_base`, `mcp_token_ttl_days` (`MCP_*`) | `settings.manage` |
| `automation` | `outbox_enabled` (`AUTOMATION_OUTBOX_ENABLED`), `webhook_url` (`N8N_WEBHOOK_URL`), `automation_store_name` (`AUTOMATION_STORE_NAME`), `automation_token_ttl_days` (`AUTOMATION_TOKEN_TTL_DAYS`); secrets `outbound_secret` (`N8N_OUTBOUND_SECRET`), `webhook_auth_token` (`N8N_WEBHOOK_AUTH_TOKEN`) | `settings.manage_critical` |
| `account_links` | `password_reset_url` (`PASSWORD_RESET_URL`), `email_verified_url` (`EMAIL_VERIFIED_URL`) | `settings.manage_critical` |
| `app_links` | `android_packages`, `android_sha256_fingerprints`, `ios_app_ids` (`MOBILE_ANDROID_PACKAGES`, `MOBILE_ANDROID_SHA256_FINGERPRINTS`, `MOBILE_IOS_APP_IDS`), `biometric_challenge_ttl_seconds` (`MOBILE_BIOMETRIC_CHALLENGE_TTL_SECONDS`) | `settings.manage_critical` |
| `app` (Mobile app) | `web_checkout_redirect`, `web_checkout_url`, `forgot_password_url` (`MOBILE_WEB_CHECKOUT_REDIRECT`, `MOBILE_WEB_CHECKOUT_URL`, `MOBILE_FORGOT_PASSWORD_URL`) and the other app config keys | `settings.manage` |
| `push` | `push_enabled` (`FCM_ENABLED`), `fcm_project_id` (`FCM_PROJECT_ID`); secret `fcm_service_account` (the JSON file named by `FCM_CREDENTIALS_PATH`) | `settings.manage_critical` |
| `checkout` | `guest_checkout` (`STOREFRONT_GUEST_CHECKOUT`) | `settings.manage_critical` |
| `security` | `admin_idle_minutes` (`ADMIN_IDLE_MINUTES`) | `settings.manage_critical` |
| `seo` (Search engines) | `block_all_crawlers` (`SEO_BLOCK_ALL_CRAWLERS`) | `settings.manage` |
| `admin` (Admin panel) | `admin_theme` (`ADMIN_THEME`) | `settings.manage` |

The config files keep safe built-in defaults (switches off, no secrets), used on a fresh install and whenever the database cannot be read.

**Migrating an existing installation (once, after deploying this release):**

```
php artisan migrate --force             (no new migration; harmless)
php artisan config:clear                (the import reads .env, which a cached config hides)
php artisan settings:import-env         (prints each key with imported, skipped, empty or invalid; never a value)
php artisan config:cache
```

The import copies the current value of every moved key into the settings once. A key that already has a database value is skipped (a seeded built-in default that no one changed does not count); `--force` overwrites. For FCM it reads the JSON file at `FCM_CREDENTIALS_PATH` and stores its content encrypted. A value its setting refuses (for example an `http` URL in production) is reported `invalid`, stored nowhere, and the command exits non-zero: fix it in the admin screen. Then remove the moved lines from `.env` and delete the FCM key file from the server.

Mobile payment returns and app links (M-A6, `API_DOCUMENTATION.md` 1e). `APP_URL` must be the HTTPS store domain the app links are verified on (`https://boxcards.shop`); an app attempt is refused in production otherwise. The return templates, packages, fingerprints and iOS app ids are set in Settings > Payment returns and Settings > App links. Values are supplied by the owner (open decision 4 in `MOBILE_APP_ROADMAP.md`); never invent a fingerprint or team id. Android re-verifies only on install or update (Android 15+ periodically), Apple caches through its CDN.

## 4. Database

Tuning start: `innodb_buffer_pool_size=4G`, `max_connections=200`, `slow_query_log=1`, `long_query_time=0.5`, UTC server time zone.

Separate users:

| User | Grants |
|---|---|
| `egs_app` | `SELECT, INSERT, UPDATE, DELETE` on the schema. **Then revoked:** `UPDATE, DELETE` on `audit_logs`, `order_events`, `digital_code_accesses`, `wallet_transactions`, `exchange_rate_history`, `mcp_audit_logs` |
| `egs_maint` | `DELETE` on prunable logs, used only by `logs:prune` through a dedicated connection |
| `egs_migrate` | DDL, used only by deploys |

MySQL cannot subtract a table from a database-wide grant unless partial revokes are enabled, so the deploy script issues a `GRANT` per table from the schema, giving those append-only tables `SELECT, INSERT` only.

## 5. Deploy steps

```
1. git fetch; create release dir; link shared .env and storage
2. composer install --no-dev --optimize-autoloader
3. npm ci && npm run build
4. php artisan migrate --force          (egs_migrate; backward-compatible migrations only)
5. php artisan config:cache route:cache view:cache event:cache
6. switch symlink (atomic)
7. reload php-fpm; php artisan queue:restart
8. smoke checks: /up, a storefront page, queue heartbeat
```

Rollback: repoint symlink, `queue:restart`. Migrations must remain compatible with the previous release; destructive changes ship in a later release.

## 6. Nginx

```nginx
limit_req_zone $binary_remote_addr zone=login:10m rate=10r/m;
limit_req_zone $binary_remote_addr zone=webhooks:10m rate=20r/s;

server {
    listen 443 ssl http2;
    server_name example.com;
    root /var/www/egs/current/backend/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options nosniff always;

    client_max_body_size 12m;
    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;

    location /build/ { expires 1y; add_header Cache-Control "public, immutable"; try_files $uri =404; }
    location ~* \.(webp|avif|png|jpg|jpeg|svg|woff2)$ { expires 30d; add_header Cache-Control "public"; try_files $uri /index.php?$query_string; }

    location = /login { limit_req zone=login burst=5 nodelay; try_files $uri /index.php?$query_string; }
    location ^~ /webhooks/ { limit_req zone=webhooks burst=40 nodelay; try_files $uri /index.php?$query_string; }
    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 60;
    }
    location ~ /\.(?!well-known) { deny all; }
}

server { listen 80; server_name example.com; return 301 https://$host$request_uri; }
```

App links (M-A6): `/.well-known/assetlinks.json`, `/.well-known/apple-app-site-association` and `/app/payments/...` are Laravel routes. The config above already passes them to `index.php` (`location /` falls through `try_files`, and `location ~ /\.(?!well-known)` exempts `.well-known` from the dot-file deny). Keep it that way: do not add a `location /.well-known/` block that serves only static files or returns 404, do not put files under `backend/public/.well-known/` (a static file would shadow the route), do not redirect these paths (Apple and Android refuse redirects), and keep them on the apex HTTPS host. If certbot's webroot plugin is used instead of `--nginx`, scope it to `location ^~ /.well-known/acme-challenge/` only. Smoke check: `curl -sI https://<host>/.well-known/assetlinks.json` shows `200` and `content-type: application/json`.

Set `TrustProxies` if a CDN is placed in front.

### n8n

The full n8n setup (environment variables, credentials, import order) is in `/n8n/README.md`. Workflows there are authored and validated, not executed.

```nginx
server {
    listen 443 ssl http2;
    server_name n8n.example.com;
    ssl_certificate     /etc/letsencrypt/live/n8n.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/n8n.example.com/privkey.pem;

    # Editor restricted to the operators' addresses. Webhooks remain reachable.
    location /webhook/ { proxy_pass http://127.0.0.1:5678; include proxy_params; }
    location / {
        allow 203.0.113.10;   # replace
        deny all;
        proxy_pass http://127.0.0.1:5678;
        include proxy_params;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

## 7. Supervisor

```ini
[program:egs-webhooks]
command=php /var/www/egs/current/backend/artisan queue:work redis --queue=webhooks --timeout=60 --tries=5 --sleep=1 --max-time=3600
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=70
stdout_logfile=/var/log/egs/webhooks.log

[program:egs-payments]
command=php /var/www/egs/current/backend/artisan queue:work redis --queue=payments --timeout=90 --tries=3 --sleep=1 --max-time=3600
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=100
stdout_logfile=/var/log/egs/payments.log

[program:egs-fulfillment]
command=php /var/www/egs/current/backend/artisan queue:work redis --queue=fulfillment --timeout=90 --tries=1 --sleep=1 --max-time=3600
user=www-data
numprocs=3
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=100
stdout_logfile=/var/log/egs/fulfillment.log

[program:egs-verification]
command=php /var/www/egs/current/backend/artisan queue:work redis --queue=verification --timeout=60 --tries=3 --sleep=2 --max-time=3600
user=www-data
numprocs=1
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=70
stdout_logfile=/var/log/egs/verification.log

[program:egs-default]
command=php /var/www/egs/current/backend/artisan queue:work redis --queue=notifications,default --timeout=60 --tries=3 --sleep=2 --max-time=3600
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=70
stdout_logfile=/var/log/egs/default.log

[program:egs-long]
command=php /var/www/egs/current/backend/artisan queue:work redis-long --queue=sync,reports --timeout=600 --tries=2 --sleep=3 --max-time=3600
user=www-data
numprocs=1
process_name=%(program_name)s_%(process_num)02d
autostart=true
autorestart=true
stopwaitsecs=610
stdout_logfile=/var/log/egs/long.log

[program:n8n]
command=/usr/bin/n8n start
directory=/opt/n8n
user=n8n
environment=N8N_HOST="n8n.example.com",N8N_PROTOCOL="https",WEBHOOK_URL="https://n8n.example.com/",GENERIC_TIMEZONE="UTC",NODE_FUNCTION_ALLOW_BUILTIN="crypto",N8N_ENCRYPTION_KEY="%(ENV_N8N_ENCRYPTION_KEY)s",EGS_API_BASE_URL="https://example.com",EGS_AUTOMATION_HMAC_SECRET="%(ENV_EGS_AUTOMATION_HMAC_SECRET)s",EGS_OUTBOUND_SECRET="%(ENV_EGS_OUTBOUND_SECRET)s",EGS_ALERT_CHANNEL="%(ENV_EGS_ALERT_CHANNEL)s"
autostart=true
autorestart=true
stdout_logfile=/var/log/egs/n8n.log
```

`config/queue.php` defines `redis` (`retry_after` 180) and `redis-long` (`retry_after` 660). The per-queue table is in `PROJECT_ARCHITECTURE.md` section 5. `--tries=1` on fulfilment is required, not a tuning choice.

## 8. Scheduler

```
* * * * * www-data cd /var/www/egs/current/backend && php artisan schedule:run >> /dev/null 2>&1
```

## 9. SSL

`certbot --nginx -d example.com -d n8n.example.com`. Renewal timer verified with `certbot renew --dry-run`. Stripe and PayPal require valid HTTPS for webhooks.

## 10. Caching and performance

`config:cache`, `route:cache`, `view:cache`, `event:cache`, OPcache with `validate_timestamps=0` (FPM reloaded on deploy). Redis for settings, FX, redirects, menus, homepage fragments. No full-page cache for pages that vary by currency unless the cache key includes it. Brotli at the Nginx level if the module is available.

## 11. Backups

| Item | Frequency | Retention | Location |
|---|---|---|---|
| MySQL dump (`--single-transaction`) | every 6 hours, plus binlog for point-in-time | 30 days | Off-host, encrypted |
| `storage/` and media | daily | 30 days | Off-host |
| `.env` and code keys | on change | indefinitely | **Separate** secure store (losing `DIGITAL_CODE_KEYS` makes codes unrecoverable) |
| n8n data and workflows | daily | 30 days | Off-host |

Restore drill: quarterly, documented with date in `PROGRESS.md`. Restore into a separate database, run `migrate:status` and a sample order read. The backup destination is a business decision (object storage provider).

## 12. Monitoring

| Signal | Tool |
|---|---|
| Uptime | n8n F01, and an external monitor (n8n cannot report on its own outage) |
| Errors | Laravel logs, F03, optional Sentry-compatible reporter (not chosen) |
| Queues | `queue:monitor`, `failed_jobs`, F02 |
| Scheduler | `scheduled_task_runs` heartbeats (written for every scheduled task since M17), F04, MCP `get_system_health` |
| DB | Slow log, F05 |
| Host | disk, memory alerts (provider or node exporter) |

## 13. Logging

`LOG_CHANNEL=daily`, `LOG_LEVEL=info`, 30-day retention, JSON formatter, `logrotate` for Supervisor logs, redaction processor always on.

## 14. Go-live sequence

Credentials are entered in sandbox mode first; each integration passes a sandbox run (`IMPLEMENTATION_ROADMAP.md` M20) before its `mode` becomes `production`. Provider webhooks are registered against production URLs. Set a Reloadly IP whitelist and 2FA. Gateways are enabled one at a time, with a small real test order and refund for each.
