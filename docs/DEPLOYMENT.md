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

Required production values (no real values are ever committed):

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
GEOIP_DATABASE_PATH=        MAXMIND_LICENSE_KEY=
AUTOMATION_OUTBOX_ENABLED=true   N8N_WEBHOOK_URL=https://n8n.example.com/webhook/egs-events
N8N_OUTBOUND_SECRET=        N8N_WEBHOOK_AUTH_TOKEN=
```

Supplier and gateway credentials are entered in the admin panel and stored encrypted, not in `.env`.

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
