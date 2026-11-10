# Security

Scope: application and operational security requirements. Each **must** has a test or a deployment check. Status: sections 1 (except customer session revocation) and 4, plus log redaction in section 9, are built and tested at M3; the rest is planned.

## 1. Authentication

| Item | Requirement |
|---|---|
| Password hashing | Laravel `bcrypt` or `argon2id` default driver, rehash on login |
| Password policy | `Password::min(12)->letters()->mixedCase()->numbers()->uncompromised()` for staff; `min(10)` and `uncompromised()` for customers |
| Email verification | Required before wallet use, reviews, and account code reveal |
| Password reset | Standard broker, 30-minute token, throttled, identical response whether the email exists or not |
| Staff 2FA | TOTP mandatory for `type=staff`, enforced by `EnsureTwoFactor` on all `/admin` routes |
| Login throttling | `auth` limiter (5/min per email+IP), then `security_events` |
| Locale | `Accept-Language` (`en`, `ar`) changes message text only; error `code` values and `errors` keys are identical in every language (M-A3) |
| Session | `http_only`, `secure`, `same_site=lax`; regenerated at login; 120-minute idle (admin 30). Customer can revoke sessions from the account page. `SESSION_DRIVER=redis`. |
| Mobile app tokens (M-A2, ADR-035) | Customers only (staff refused with the generic login error). Sanctum access token, exact ability `customer`, 15-minute expiry; opaque refresh token stored only as SHA-256, single use, rotated on every refresh, family revoked and audited (`auth.mobile_refresh_token_reused`, security event `auth.refresh_token_reused`) on reuse, absolute 30-day family lifetime; logout revokes the device's family, logout-all every family. Two-factor pending state is a hashed, single-use, 5-attempt, 300-second challenge row. First sign-in from a device name audited (`auth.mobile_new_device`). `sanctum` guard bound to the `users` provider: service-account tokens never authenticate customers, and customer tokens are refused by the MCP, automation and admin APIs. Limiters `auth` (login, register), `otp` (challenge) and `mobile-refresh`. Expired rows pruned hourly. Tokens are returned with `Cache-Control: no-store` and never logged or audited. |
| Stateless cart, checkout and account (M-A3, ADR-036) | Bearer clients use the parallel `/api/v1/mobile` routes in the `api` group; the web routes keep the session and CSRF and never accept a bearer token (no CSRF exemption keyed on an `Authorization` header). A present but invalid or non-customer bearer token is 401, never treated as a guest; staff and service-account tokens are refused. Guest cart token (`X-Cart-Token`) and guest order token (`X-Guest-Order-Token`): 64 random characters, stored only as SHA-256, returned with `Cache-Control: no-store, private`, never logged or audited. A guest order token is scoped to one guest order, expires after 24 h (`orders.guest_order_token_ttl_hours`), never opens a customer's order, and is pruned hourly. A guest reveal needs the signed-link proof and the email OTP proof on one token, as the session needs both; OTP verification always issues a new token (rotation). Recent authentication for reveal is stored per access token with the session's 15-minute window and is not extended by refresh. |

## 2. Web hardening

| Threat | Control |
|---|---|
| CSRF | Default middleware on all session writes. Exemptions only `/webhooks/*`. Stateless `api` routes (`/api/v1/mobile`, `/api/v1/storefront` catalog) start no session and read no session cookie, so they need none. |
| XSS | Blade `{{ }}` escaping. Rich text through HTML Purifier-style sanitization on save. `{!! !!}` is only allowed on fields sanitized at write time, and each use is listed in code review. |
| CSP | Nonce-based `Content-Security-Policy` via middleware. Allow-listed: Stripe, PayPal, GTM, GA as required. `frame-ancestors 'none'`, except Stripe and PayPal frames as `frame-src`. |
| Headers | HSTS (after SSL confirmed), `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` |
| SQL injection | Eloquent and bound parameters only. `DB::raw` needs review; sort columns are allow-listed. |
| Mass assignment | Explicit `$fillable`. Status, financial totals, `user_id` never fillable. |
| File upload | MIME and extension allow-list, size cap, re-encoded images, randomized names, stored outside the public root unless media. SVG rejected for non-staff. |
| Open redirect | Redirect targets are validated against an allow-list; `redirects` only points at same-site paths. |
| SSRF | Supplier `base_url_override` must be HTTPS and on an allow-list for that adapter. PayPal `cert_url` host is allow-listed. |
| Clickjacking | Per CSP above |

## 3. Input validation

Every write goes through a FormRequest (rule 12). Delivery field values are validated against each definition's regex and type. Provider data is also treated as untrusted: adapters validate shape into DTOs before use.

## 4. Authorization

Spatie roles and permissions, checked in routes and Policies. Admin routes require `type=staff`, 2FA, and a named permission. Hidden navigation is a convenience, not the control.

`Gate::before` is **not** used to grant Super Admin everything. Super Admin holds all permissions except those marked (E), which must be granted explicitly by another Super Admin and are audited.

### Permissions

`dashboard.view`; `reports.sales`, `reports.profit`; `catalog.view`, `catalog.manage`, `catalog.publish`; `suppliers.view`, `suppliers.manage`, `suppliers.credentials` (E), `suppliers.sync`, `suppliers.logs`; `mappings.manage`; `orders.view`, `orders.review`, `orders.fulfill_manual`, `orders.reconcile`, `orders.fraud_review`; `digital_codes.import`, `digital_codes.reveal` (E); `refunds.view`, `refunds.create`, `refunds.approve`; `customers.view`, `customers.view_pii`, `customers.manage`, `customers.wallet_adjust`; `payments.view`, `payments.manage`, `payments.credentials` (E), `payments.webhook_logs`; `pricing.view`, `pricing.manage`, `pricing.override_margin`; `currency.manage`; `marketing.coupons`, `marketing.promotions`, `marketing.affiliates`, `marketing.banners`; `content.pages`, `content.blog`, `content.faq`, `content.menus`, `content.homepage`, `content.media`; `seo.manage`, `seo.redirects`; `support.view`, `support.reply`, `support.manage`, `support.refund_requests`; `users.manage`, `roles.manage`; `settings.view`, `settings.manage`, `settings.manage_critical` (E); `service_accounts.manage` (E); `mcp_actions.approve`; `ai_suggestions.review`; `audit.view`.

### Roles

| Role | Grants |
|---|---|
| Super Admin | All except (E), which are explicit grants |
| Admin | All operational permissions, no `users.manage`, no `roles.manage` (a role editor could grant itself anything), no credentials, no critical settings |
| Operations Manager | `dashboard.view`, orders.*, `suppliers.view`, `suppliers.sync`, `suppliers.logs`, `mappings.manage`, `digital_codes.import`, `refunds.view`, `customers.view` |
| Finance Manager | `dashboard.view`, reports.*, `payments.view`, `payments.webhook_logs`, refunds.*, `pricing.view`, `orders.view` |
| Support Agent | support.*, `orders.view`, `customers.view`, `customers.view_pii`, `refunds.view` |
| Catalog Manager | catalog.*, `mappings.manage`, `content.media`, `pricing.view` |
| Marketing Manager | marketing.*, `content.homepage`, `content.blog`, `content.media`, `ai_suggestions.review`, `reports.sales` |
| SEO Manager | seo.*, `content.pages`, `content.blog`, `catalog.view` |

Customer data access is object-level: Policies check ownership on orders, tickets, reviews, wallet.

## 5. Secrets

| Secret | Storage |
|---|---|
| Supplier and gateway credentials | `enc` columns; write-only in UI (masked, replaced never read back); never in any API Resource |
| `APP_KEY`, `DIGITAL_CODE_KEYS`, `DIGITAL_CODE_BLIND_INDEX_KEY` | Environment only. Backed up separately from database backups. |
| Google refresh token, GA4 `api_secret`, OXR `app_id`, SMTP password | Encrypted settings |
| n8n secrets | n8n's own credential store |

Frontend receives only publishable identifiers: Stripe publishable key, PayPal client ID, GTM and GA measurement IDs, Paymob public key.

`APP_KEY` rotation uses `APP_PREVIOUS_KEYS`. Code keys use the keyring.

## 6. Payments and webhooks

- Never trust frontend confirmation; never fulfil from a redirect (`PROJECT_ARCHITECTURE.md` section 9).
- Signature verified over the raw body before anything else; comparisons with `hash_equals`.
- Stripe replay tolerance 5 minutes. Replayed events are absorbed by the unique key.
- Amount and currency are compared against the stored snapshot; a mismatch sends the order to `manual_review`.
- Test credentials in production are refused: adapter boot verifies the key prefix or mode matches `mode`.
- `APP_ENV=production` refuses to register any test or fake adapter.

## 7. Digital codes

See `PROJECT_ARCHITECTURE.md` section 10. Automated tests must assert a seeded canary code does not appear in: log files, `supplier_api_logs`, `order_events`, `audit_logs`, `automation_outbox`, GA payloads, mail bodies, API Resources or error responses.

## 8. Rate limiting and abuse

Limiters in `API_DOCUMENTATION.md`. Coupon brute force: limiter plus lockout per session (per customer, bearer token or cart token for stateless clients, plus the per-IP limit, M-A3). Search has per-IP limits. Nginx adds a coarse `limit_req` on `/login`, `/webhooks`. Registration and guest checkout use a CAPTCHA only if abuse is observed (setting-driven, off by default, no vendor chosen).

## 9. Logging, audit and retention

Audited actions (always to `audit_logs`): logins by staff, mobile sign-in from a new device and refresh-token reuse (M-A2), guest order tokens issued after the email code or the signed link (M-A3, never the token), permission changes, settings, credentials set (never values), price changes, coupon creation, refunds, reveal attempts, order overrides, supplier retries, service account changes, MCP writes.

Redaction: `RedactSensitiveLogData` Monolog processor removes keys including `password`, `token`, `secret`, `api_key`, `authorization`, `card*`, `pin*`, `code`, `client_secret`, `redemption*`. `dontFlash` and `dontReport` are configured. Provider HTTP exceptions are logged by status and operation only.

| Data | Retention |
|---|---|
| `audit_logs`, `order_events`, `digital_code_accesses`, financial tables | 7 years, or as counsel advises |
| `payment_webhooks` payload | 180 days, then payload nulled, row retained |
| `supplier_api_logs` | 90 days |
| `supplier_health_checks`, `supplier_balance_snapshots` | 13 months |
| `automation_logs`, `mcp_audit_logs` | 13 months |
| `not_found_logs`, `search_queries` | 13 months |
| Application log files | 30 days |
| Abandoned carts | 90 days |

## 10. Service clients

n8n and MCP: named `service_accounts` with Sanctum tokens, scopes, optional IP allow-list, per-account rate limit, and read-only by default. Tokens are shown once, and never logged. MCP (built M16, `MCP_SERVER.md`): bearer tokens only (no session), exact `mcp:` abilities only (a `*` ability grants nothing), tokens expire after 90 days by default, every call is written to `mcp_audit_logs`, customer emails and phones are masked, and there is no route for codes, credentials, refunds, settings or queries. Writes are pending actions approved out of band (ADR-020, ADR-032). n8n (built M17, `N8N_AUTOMATIONS.md` section 2): bearer token of an `n8n` service account with exact `automation:` scopes plus an HMAC-SHA256 signature with the account's own encrypted secret over timestamp, nonce, method, path with query, workflow id, execution key and raw body; a 5-minute window and a nonce stored in the cache block replay; only enabled workflows may call; every request writes an `automation_logs` call row (query redacted, never the body). Laravel-to-n8n webhooks carry a header token and an HMAC over `timestamp.raw_body` with `N8N_OUTBOUND_SECRET`. n8n never receives codes, credentials or payment data; customer contact details are returned only for customers eligible under `CustomerContactPolicy`.

## 11. Suspicious transaction detection

`PROJECT_ARCHITECTURE.md` section 15. Manual review queue with SLA alerts. First-order caps per payment method configurable. Payment disputes raise the customer's risk level.

## 12. Privacy

Consent is recorded in `customer_consents`. Marketing requires opt-in and honours unsubscribe at send time. Personal data export and erasure commands (`customers:export`, `customers:anonymize`); anonymization preserves financial records while replacing identity fields. Cookie consent gates GTM and GA. EU data: consider `region1` GA4 collection. No PII in analytics or URLs.

## 13. Reliability controls

Database transactions on all financial writes; no external calls within them; `queue:monitor`; failed jobs alert; backups and restore drill (`DEPLOYMENT.md`); exception handling returns generic messages and a `request_id`.

## 14. Risks accepted and open

| Item | Status |
|---|---|
| Reloadly webhook signature format unknown | Webhooks disabled until verified; polling is primary |
| Paymob callback retry policy unknown | Polling covers it |
| Gift card resale legal position, per brand | Needs business and legal confirmation |
| AML and stored-value treatment of wallet | Avoided by limiting wallet to credits and refunds |

## 15. Pre-launch checklist

- `APP_DEBUG=false`, `APP_ENV=production`; `/up` only exposes status.
- All credentials are production credentials matched to `mode`.
- Canary code test passes; arch tests pass.
- Webhook endpoints reject unsigned requests in production.
- Admin 2FA enforced; default accounts removed.
- DB user grants applied per `DEPLOYMENT.md`.
- Backup restore tested. Rate limits observed.
- Dependency audit (`composer audit`, `npm audit`) clean or accepted.
