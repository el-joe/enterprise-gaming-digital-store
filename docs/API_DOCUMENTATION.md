# API Documentation

**Status:** built so far: the authentication routes in section 1a, the two-factor routes in section 4, `GET /admin/api/me` and the envelopes (M3); section 2a storefront catalog, search and currency (M5, M6); section 5a admin catalog, content, media, currency and pricing (M5, M6); section 3a cart, checkout and guest verification (M7); section 4a code reveal and section 5b suppliers, mappings, fulfilment, manual tasks and digital codes (M8, M9); section 3b payment options, attempts, verification and PayPal capture, section 5c staff refunds and section 6 payment webhooks (M10); section 2b product reviews and affiliate clicks, section 4b customer wallet, reviews, tickets and refund requests, section 5d admin coupons, promotions, affiliates, reviews, support and customer wallets, real coupons in 3a and the wallet gateway in 3b (M11); section 8 MCP API with admin MCP tokens and action approval (M16); section 7 automation API for n8n with admin automation credentials (M17); section 1b mobile authentication with customer bearer tokens (M-A2); section 1c stateless cart, checkout, account and locale for the app under `/api/v1/mobile` (M-A3); section 2c product detail, platforms, categories, countries and home and section 1d app config (M-A4); section 4c account orders, guest order detail, profile, password, devices, notification inbox and preferences, and the sessionless email links of 1a (M-A5). Everything else is planned. Update the status column per route group as milestones land.

## 1. Conventions

### Base paths

| Group | Prefix | Auth | Status |
|---|---|---|---|
| Public JSON (storefront JS) | `/api/v1/storefront` | Session optional, CSRF for writes, rate limited | catalog, search and currency built (2a); cart built (3a); reviews and affiliate clicks built (2b); product detail, platforms, categories, countries, home built (2c); newsletter planned |
| App config | `/api/v1/app/config` (and `/api/v1/mobile/app/config`) | none, public, cached 60 s | built (M-A4, section 1d) |
| Customer JSON | `/api/v1/account` | Session, verified email where noted | two-factor, confirm-password and code reveal built (4a); wallet, reviews, tickets, refund requests built (4b); orders, profile, password, devices, notifications and preferences built (4c, M-A5); digital-codes list and wishlist planned |
| Checkout JSON | `/api/v1/checkout` | Session or guest cart token, CSRF | validate, place-order, status, guest OTP (3a), guest code access (4a) and payment routes (3b) built |
| Admin JSON | `/admin/api` | Session, `type=staff`, confirmed 2FA with a session that passed the challenge, permission per route, CSRF | `GET /me`, sections 5a to 5d built; rest planned |
| Yajra DataTables | `/admin/api/tables/{table}` | As admin | planned |
| Provider webhooks | `/webhooks/{provider}` | Signature only, no session, no CSRF, `webhooks` limiter | payment gateways built (6); suppliers planned |
| Automation (n8n) | `/api/v1/automation` | Sanctum bearer token of an `n8n` service account + HMAC signature, timestamp and nonce + scopes | built (M17, section 7) |
| MCP | `/api/v1/mcp` | Sanctum bearer token of an `mcp` service account + scopes | built (M16, section 8) |
| Mobile app | `/api/v1/mobile` | Stateless (`api` group, no session, no CSRF); signed-in routes need a customer Sanctum bearer token with the `customer` ability | auth built (M-A2, section 1b); cart, checkout and account built (M-A3, section 1c); public storefront reads (2a, 2b reviews, 2c) and app config (1d) built (M-A4); account orders, profile, devices, notifications and guest order detail built (M-A5, section 4c); payment returns planned (M-A6) |

Web pages (Blade) are listed in `SEO_STRATEGY.md` section 2.

### Headers

| Header | Use |
|---|---|
| `Accept: application/json` | Required for JSON behaviour |
| `X-CSRF-TOKEN` | Session writes |
| `X-Request-Id` | Accepted or generated (ULID); echoed; logged |
| `Idempotency-Key` | Required on `POST /api/v1/checkout/place-order` (and its `/api/v1/mobile` twin). Refund creation is protected by `uq_ru_unit` and the remaining-amount check instead; provider calls carry their own keys (architecture 9.3) |
| `Accept-Language` | `en` or `ar` (region suffixes and quality values honoured, e.g. `ar-EG,ar;q=0.9`); anything else is `en`. Sets the language of messages and translated storefront content; the response carries `Content-Language` (M-A3, section 1c) |
| `Authorization: Bearer` | Customer access token (section 1b). Stateless `/api/v1/mobile` routes only |
| `X-Cart-Token` | Stateless guest cart (section 1c) |
| `X-Currency` | Display currency for stateless clients; overrides the `currency` cookie (section 1c) |
| `X-Guest-Order-Token` | Per-order guest access for stateless clients (section 1c) |

### Standard success envelope

```json
{ "success": true, "message": "Saved.", "data": {}, "meta": {} }
```

`meta` carries pagination (`current_page`, `per_page`, `total`, `last_page`) when applicable. Yajra table responses are the exception: they use DataTables' own format (`draw`, `recordsTotal`, `recordsFiltered`, `data`) and are not enveloped.

### Standard error envelope

All admin JS depends on this exact shape. It is implemented once, in `bootstrap/app.php` exception rendering, for requests expecting JSON.

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "code": "validation_failed",
  "errors": {
    "name": ["The name field is required."],
    "variants.0.price": ["The price must be at least 0.01."]
  },
  "request_id": "01JC..."
}
```

| HTTP | `code` | `errors` | Client behaviour |
|---|---|---|---|
| 401 | `unauthenticated` | absent | Redirect to login |
| 401 | `refresh_token_invalid` | absent | Mobile: the refresh token is unknown, expired, revoked or reused; sign out locally and show the login |
| 403 | `forbidden` | absent | SweetAlert2 error |
| 403 | `two_factor_required` | absent | Redirect to two-factor enrolment or challenge |
| 404 | `not_found` | absent | SweetAlert2 error |
| 403 | `margin_override_forbidden` | absent | SweetAlert2 error |
| 409 | `current_device` (M-A5: revoke the calling device with logout instead), `conflict`, `illegal_transition`, `idempotency_in_progress`, `idempotency_mismatch`, `two_factor_already_enabled`, `duplicate_code`, `review_exists`, `ticket_closed`, `refund_request_open`, `wallet_frozen`, `wallet_idempotency_mismatch` | absent | SweetAlert2 and reload row |
| 423 | `recent_auth_required` | absent | Ask for the password (`POST /api/v1/account/confirm-password`), then retry |
| 422 | `cart_empty`, `item_unavailable` (`errors.items.{cart_item_id}` or `errors.variant_id`), `order_total_invalid`, `otp_invalid` | optional | Message near the form |
| 419 | `csrf_expired` | absent | Prompt to refresh |
| 422 | `validation_failed` | object | Field-level display |
| 422 | Domain codes such as `coupon_invalid`, `price_changed`, `gateway_not_eligible`, `below_margin`, `product_not_publishable`, `rate_missing`, `rate_stale`, `invalid_image`, `base_currency_rate`, `two_factor_invalid`, `two_factor_expired`, `payment_currency_below_margin`, `supplier_not_configured`, `wallet_insufficient_balance`, `wallet_unavailable`, `wallet_amount_invalid`, `review_not_eligible`, `refund_destination_unavailable`, `notification_preference_mandatory` (M-A5) | optional | Message near the form |
| 403 | `email_unverified` (reviews) | absent | Ask the customer to verify their email |
| 403 | `reveal_denied` | absent | Generic message; the attempt is audited |
| 429 | `rate_limited` | absent | Retry after `Retry-After` |
| 500 | `server_error` | absent | Generic message, `request_id` shown. No stack traces or provider bodies. |
| 503 | `maintenance`, `rates_provider_unavailable` | absent | |

**Field mapping rule.** Keys in `errors` use Laravel dot notation. The shared admin module `resources/js/admin/forms/errors.js` maps `a.0.b` to `[name="a[0][b]"]`, renders the first message beside the field in a `[data-error-for="a.0.b"]` element or one it creates, sets `aria-invalid`, focuses the first invalid field, and shows unmatched keys in a form-level summary.

### Pagination and filtering

Query: `page`, `per_page` (default 20, max 100), `sort` (`-created_at`), `filter[field]=`, `q`.

### Money in JSON

Money is a decimal **string** with the currency's precision, plus a separate currency: `{ "amount": "12.50", "currency": "USD", "formatted": "$12.50" }`. Never a JSON number.

### Rate limits

| Limiter | Limit |
|---|---|
| `storefront` | 120/min per IP |
| `search` | 60/min per IP |
| `auth` | 5/min per email+IP |
| `otp` | 5 per 10 min per identifier (web: pending user in the session; mobile: the challenge token; else IP) |
| `mobile-refresh` | 5/min per presented refresh token and 60/min per IP (`config/mobile.php`) |
| `checkout` | 20/min per client and 60/min per IP. Client: the session; without one, the bearer user, else the bearer token, the `X-Cart-Token` or the `X-Guest-Order-Token` (hashed), else the IP |
| `code-reveal` | 10/hour per user or guest order |
| `admin` | 300/min per user |
| `account` | 60/min per user (M11 account routes) |
| `automation`, `mcp` | `service_accounts.rate_limit_per_minute` |
| `webhooks` | 600/min per IP |

Built limiters: `auth`, `otp`, `mobile-refresh` (M-A2), `admin`, `storefront`, `search`, `checkout`, `webhooks` (M10), `account` (M11), `mcp` (M16) and `automation` (M17), per account, `service_accounts.rate_limit_per_minute` (30/min per IP before authentication), `code-reveal` (`fulfillment.reveal_per_hour`, default 10, per user or guest order). The others are planned with their route groups. `POST /admin/api/exchange-rates/fetch-now` is also limited to 6 per minute.

---

## 1a. Authentication (built, M3)

Session routes in the `web` middleware group (CSRF applies). Responses use the standard envelopes. Blade pages for these flows arrive in M12.

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| POST | `/register` | `name`, `email`, `password`, `password_confirmation` | `auth` | 201 `{ user }`; signed in; verification email sent |
| POST | `/login` | `email`, `password`, `remember?` | `auth` | `{ two_factor_required, two_factor_enrollment_required, user }`; `user` is null while a challenge is pending |
| POST | `/two-factor-challenge` | `code` or `recovery_code` | `otp` | `{ user }`; signed in |
| POST | `/logout` | | | Signed out |
| POST | `/forgot-password` | `email` | `auth` | Identical response whether or not the email exists |
| GET | `/reset-password/{token}` | | | Target of the reset email. With `auth.links.password_reset_url` (env `PASSWORD_RESET_URL`) set: 302 to that template with `{token}` and `{email}` URL-encoded (an app link, a custom scheme or the M12 page), `Cache-Control: no-store, private`, `Referrer-Policy: no-referrer`; unset: `{ token, email }` (M-A5) |
| POST | `/reset-password` | `token`, `email`, `password`, `password_confirmation` | `auth` | Password reset; every mobile sign-in and browser session of the account is signed out (M-A5) |
| GET | `/email/verify/{id}/{hash}` | Signed URL; **no session needed** (M-A5) | 6/min | Verifies the user of the link (signature, id and hash of the current address); signs nobody in. JSON: `{ status: verified \| already_verified }`, 403 for a bad signature, wrong hash or unknown user. Browser: 302 to `auth.links.email_verified_url` (env `EMAIL_VERIFIED_URL`, `{status}` = `verified`, `already_verified`, `invalid`), default `APP_URL/?email_verification={status}` |
| POST | `/email/verification-notification` | Session required (bearer clients: `/api/v1/mobile/account/email/verification-notification`, M-A5) | 6/min | Link resent |

Admin: `GET /admin/api/me` returns `{ user, roles, permissions }`. Two-factor enrolment is under `/api/v1/account/security/two-factor`.


## 1b. Mobile authentication (built, M-A2)

Prefix `/api/v1/mobile/auth`, `api` middleware group: stateless, no session, cookies or CSRF. Design in `PROJECT_ARCHITECTURE.md` ADR-035. Responses use the standard envelopes; every response that carries a token or challenge has `Cache-Control: no-store, private`. Token values are never logged or audited.

Customers only. Credentials, account status and the error for a refused sign-in are shared with the web login (`AuthenticationService::verifyCredentials`): a wrong password, an unknown email, a suspended or closed account **and a staff account** all get the same 422 `validation_failed` with `errors.email = ["These credentials do not match our records."]`.

| Method | Path | Auth | Body | Limiter | Result |
|---|---|---|---|---|---|
| POST | `/register` | none | `name`, `email`, `password`, `password_confirmation`, `device_name` | `auth` | 201 `{ tokens, user }`; customer account and profile created, verification email sent (customer password policy, as the web) |
| POST | `/login` | none | `email`, `password`, `device_name` | `auth` | 200 `{ two_factor_required: false, challenge: null, tokens, user }`, or, when TOTP is enabled, 200 `{ two_factor_required: true, challenge: { token, expires_at }, tokens: null, user: null }` |
| POST | `/two-factor-challenge` | none | `challenge_token`, `code` (6 digits) or `recovery_code` | `otp` (per challenge) | 200 `{ tokens, user }`. 422 `two_factor_invalid` (wrong code); 422 `two_factor_expired` (unknown, used or expired challenge, account no longer active, or `challenge_max_attempts` wrong codes) |
| POST | `/refresh` | none | `refresh_token` | `mobile-refresh` | 200 `{ tokens }` (new pair; the old refresh token and the old access token stop working). 401 `refresh_token_invalid` for an unknown, expired, revoked or already used token; an already used token also revokes its whole family |
| GET | `/me` | customer bearer | | `account` | 200 `{ user }` |
| POST | `/logout` | customer bearer | | `account` | 200; revokes this device's family (its refresh tokens and access token) |
| POST | `/logout-all` | customer bearer | | `account` | 200; revokes every family and every access token of the customer |

`device_name` (required on register and login): 1 to 100 printable characters, for example `Pixel 9`. It names the access token and the sign-in; the first sign-in from a device name is audited.

`tokens`:

```json
{
  "token_type": "Bearer",
  "access_token": "17|Qm9...",
  "access_token_expires_at": "2026-11-10T12:15:00+00:00",
  "refresh_token": "kq3...64 chars",
  "refresh_token_expires_at": "2026-12-10T12:00:00+00:00"
}
```

`user` is the account `UserResource`: `{ public_id, name, email, type, email_verified, two_factor_enabled }`.

Rules for the client:

- Send `Authorization: Bearer <access_token>`. The access token lives `mobile.auth.access_token_ttl_minutes` (default 15). On 401 `unauthenticated`, refresh once (single flight) and retry.
- Each refresh token is **single use**. Store the new pair atomically before using it. Presenting a used refresh token is treated as theft: the whole sign-in (family) is revoked and audited, and every device copy must sign in again. Two concurrent refreshes with the same token therefore sign the device out.
- `refresh_token_expires_at` is absolute (`mobile.auth.refresh_token_ttl_days`, default 30, from the sign-in); rotation does not extend it.
- The challenge token lives `mobile.auth.challenge_ttl_seconds` (default 300) and is single use.
- A bearer request of a suspended or closed account gets 403 `forbidden`; its next refresh revokes the family.

Bearer tokens are accepted only by `/api/v1/mobile` routes. Since M-A3 the account, cart and checkout routes have stateless twins there (section 1c); the web routes under `/api/v1/account`, `/api/v1/storefront/cart` and `/api/v1/checkout` keep the session and never accept a bearer token. A customer token is refused (401) by the MCP and automation APIs and the admin API; a service-account token is refused (401) by the mobile routes, whatever its abilities.

## 1c. Stateless cart, checkout, account and locale (built, M-A3)

Design: `PROJECT_ARCHITECTURE.md` ADR-036. The same route files, controllers and rules as sections 3a, 3b, 4a and 4b are registered a second time under `/api/v1/mobile` in the stateless `api` group (no session, cookies or CSRF). Paths, bodies, results, error codes and limiters are those of the web routes; only identification differs.

| Web route | Stateless twin | Auth |
|---|---|---|
| `/api/v1/storefront/cart...` (3a) | `/api/v1/mobile/storefront/cart...` | guest (`X-Cart-Token`) or customer bearer |
| `/api/v1/checkout/...` (3a, 3b, 4a guest routes) | `/api/v1/mobile/checkout/...` | guest (`X-Cart-Token`, `X-Guest-Order-Token`) or customer bearer |
| `/api/v1/account/...` (4, 4a, 4b) | `/api/v1/mobile/account/...` | customer bearer, active account |
| `/api/v1/storefront/catalog/...`, `/currencies`, `/search/...`, `/home`, `/platforms`, `/categories`, `/countries` (2a, 2b, 2c) | `/api/v1/mobile/storefront/...` since M-A4 (the `/api/v1/storefront` paths are stateless too and stay valid) | none; `X-Currency` and `Accept-Language` apply |

Not duplicated: `POST /api/v1/storefront/currency` (sets a cookie; the app sends `X-Currency` instead) and `POST /api/v1/storefront/affiliates/click` (browser cookie).

**Bearer on checkout and cart.** Without an `Authorization` header the request is a guest. With one, it must be a valid customer token with the `customer` ability (staff and service-account tokens included in the refusal): otherwise 401 `unauthenticated`, never a silent fallback to guest. A suspended or closed account gets 403 `forbidden`.

**Cart token.** The first `POST /api/v1/mobile/storefront/cart/items` of a guest without `X-Cart-Token` issues a 64-character token, returned once in the `X-Cart-Token` response header and in `meta.cart_token` (`Cache-Control: no-store, private`). Send it as `X-Cart-Token` on every cart and checkout call. Only its SHA-256 is stored (`carts.session_token`). A request with a bearer token and `X-Cart-Token` adopts that guest cart when the customer has no active cart (merge on sign-in: send the guest token with the first cart call after login; a customer who already has a cart keeps it).

**Currency.** `X-Currency: EGP` sets the display currency of the catalog, variants, cart, validate, payment options and place-order, and overrides the `currency` cookie. It must be a three-letter code of an active currency, else 422 `validation_failed` with `errors.currency = ["The X-Currency header must be the code of an active currency."]`. An active currency without a usable rate falls back to the base currency (see `meta.display_currency`).

**Guest order token.** Replaces the browser session's order list and guest proofs.

| Issued by | `data.guest_order_token` | Grants |
|---|---|---|
| `POST /mobile/checkout/place-order` by a guest, 201 and replay (not `meta.duplicate: true`, not for a signed-in customer) | `{ token, order_public_id, expires_at }` | status, verify-payment, paypal/capture, retry-payment of that order |
| `POST /mobile/checkout/guest/otp/verify` | a **new** token; the presented `X-Guest-Order-Token` of the same order (if any) is deleted and its link proof carried over | as above, plus the OTP proof |
| `GET /mobile/checkout/guest/orders/{id}/access?expires=&signature=` (the signed delivery link: same query as the web link, verified against the web URL) | a link-only token when none is presented; with a presented token of that order, the link proof is added to it and `guest_order_token` is null | the link proof only; it opens nothing by itself |

The token is opaque (64 characters), stored only as SHA-256 (`guest_order_tokens`), scoped to one guest order, and expires after `orders.guest_order_token_ttl_hours` (default 24). Responses that carry it have `Cache-Control: no-store, private`. Send it as `X-Guest-Order-Token`. A token of another order, an expired token, a link-only token, or any token for an order that belongs to a customer account gets 404 on the order routes. `POST /mobile/checkout/guest/orders/{order}/units/{unit}/reveal` needs one live token holding **both** the link proof and the OTP proof (the two session proofs of the web), otherwise 403 `reveal_denied`; the order must still have a verified guest email, be paid and the unit delivered, and every attempt is audited. Issuing a token after OTP or a link is audited (`orders.guest_order_token_issued`, `orders.guest_order_link_opened`), without the token.

App flow for a guest: place-order (keep the token) -> pay -> verify-payment / status with the token. For codes: open the delivery link in the app and call its `/mobile` twin with the current token -> `otp/send` -> `otp/verify` with the token (replace the stored token with the new one) -> reveal.

**Recent authentication.** `POST /mobile/account/confirm-password` records the confirmation on the access token (`personal_access_tokens.password_confirmed_at`); reveal accepts it for `fulfillment.reveal_recent_auth_minutes` (15), as the session does. A refresh keeps the time on the new access token of the same sign-in (the window is not extended); another device must confirm on its own. Two-factor enrolment, confirmation and disabling take `current_password` in the body, as on the web.

**Locale.** `Accept-Language` sets the locale of every `/api/v1` JSON route (also the web checkout and account routes). Order: a request with a session (web) uses the signed-in customer's saved `users.locale`, then `Accept-Language`, then `en`; a stateless request uses `Accept-Language`, then `en`. The response carries `Content-Language`. Storefront content (catalog `name`, `short_description`, platform and category names, variant `title`, cart product names and variant titles) comes from `content_translations` for `ar`, falling back to the English column when there is no translation; search suggest and popular default to the request locale when `locale` is not given; reviews are shown as written. Envelope `message` values, domain error messages and validation messages are localized (`lang/ar.json`, `lang/ar/validation.php`); `code` values and `errors` keys never change. Place-order stores the request locale on the order.

## 1d. App config (built, M-A4)

`GET /api/v1/app/config` and its twin `GET /api/v1/mobile/app/config`: public, no auth, `storefront` limiter, `Cache-Control: public, max-age=60`, ETag (304 on `If-None-Match`), `Vary: Accept-Language`. Design: `PROJECT_ARCHITECTURE.md` ADR-037. **Public data only** (`SECURITY.md` section 5): nothing here is a secret, and a test asserts that configured secret values and secret field names never appear.

Query (optional, both or neither): `platform` (`android` or `ios`) and `version` (the installed version, Semantic Versioning 2.0.0, e.g. `1.4.0` or `1.4.0-rc.1+42`); 422 `validation_failed` otherwise. With them the response carries `update`.

```json
{
  "config": {
    "versions": {
      "android": { "min_supported_version": "1.4.0", "latest_version": "1.6.0" },
      "ios": { "min_supported_version": "1.4.0", "latest_version": "1.6.0" }
    },
    "update": { "platform": "android", "version": "1.3.9", "required": true, "available": true },
    "maintenance": { "enabled": false, "message": null },
    "features": { "checkout_enabled": true, "guest_checkout_enabled": true, "wallet_enabled": true, "reviews_enabled": true, "support_enabled": true },
    "locales": { "supported": ["en", "ar"], "default": "en" },
    "currencies": { "base": "USD", "supported": [{ "code": "USD", "name": "US Dollar", "symbol": "$", "exponent": 2 }] },
    "media": { "base_url": "https://boxcards.shop/storage" },
    "links": { "support": "https://...", "terms": "https://...", "privacy": null, "refund": null },
    "payment_gateways": { "stripe": { "publishable_key": "pk_live_..." }, "paypal": { "client_id": "A..." }, "wallet": {} }
  }
}
```

- `update.required`: the installed version is **strictly below** `min_supported_version` (force update); `update.available`: strictly below `latest_version`. Comparison is SemVer precedence (`1.10.0 > 1.9.0`, `1.4.0-rc.1 < 1.4.0`, build metadata ignored). The app should compare the same way if it does not send its version.
- `maintenance.message` is in the request language (custom text per locale, else a translated default), null when maintenance is off.
- `features` are advisory for the app (what to offer); they do not disable server routes.
- `payment_gateways`: one entry per **active** gateway, keyed by gateway key, holding only its public identifier (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`; the wallet has none). The value is null when not configured, or when it does not match the gateway's mode (a `pk_live_` key on a sandbox gateway). The app treats Stripe as unavailable when `publishable_key` is null or `stripe` is absent.
- `media.base_url` is the prefix of media URLs (`MEDIA_BASE_URL` CDN when set, else the public disk). Every storefront image is already returned as an absolute URL.

Sources and how to change them without a deploy: settings group `app` (keys `android_min_version`, `android_latest_version`, `ios_min_version`, `ios_latest_version`, `maintenance`, `maintenance_message` (JSON `{ "en": "...", "ar": "..." }`), `checkout_enabled`, `guest_checkout_enabled`, `wallet_enabled`, `reviews_enabled`, `support_enabled`, `support_url`, `terms_url`, `privacy_url`, `refund_url`) through `php artisan mobile:app-config set <key> <value>` (validated: SemVer, booleans, http(s) URL or site path); defaults in `config/mobile.php` `app` (env `MOBILE_*`). Public payment identifiers: `php artisan payments:public-key <gateway> <value> --mode=sandbox|production` (or `--clear`), stored in that mode's encrypted gateway credentials. Clients see a change within the 60 s cache.

---

## 2. Storefront JSON

| Method | Path | Purpose |
|---|---|---|
| GET | `/search/suggest?q=` | Instant suggestions: games, platforms, products, SKU |
| GET | `/search/popular` | Popular searches |
| GET | `/catalog/products` | Listing with filters: `category`, `platform`, `region`, `delivery_type`, `min_price`, `max_price`, `sort` |
| GET | `/catalog/products/{slug}` | Product page (built, 2c) |
| GET | `/catalog/products/{slug}/variants` | Variant matrix with prices in display currency |
| POST | `/currency` | `{ currency }`, sets cookie |
| POST | `/newsletter` | Subscribe (double opt-in) |
| GET | `/cart` | Cart with fresh prices |
| POST | `/cart/items` | `{ variant_id, quantity, custom_amount?, delivery_values? }` |
| PATCH | `/cart/items/{id}` | Update quantity |
| DELETE | `/cart/items/{id}` | Remove |
| POST | `/cart/coupon` | Apply |
| DELETE | `/cart/coupon` | Remove |

### 2a. Built storefront routes (M5, M6)

Base `/api/v1/storefront`, in the stateless `api` middleware group.

| Method | Path | Query or body | Result |
|---|---|---|---|
| GET | `/catalog/products` | `page`, `per_page`, `category`, `platform`, `region`, `delivery_type`, `min_price`, `max_price` (decimal strings, in the display currency), `sort` (`popular`, `newest`, `price_asc`, `price_desc`, `name`) | Page of `{ public_id, name, slug, short_description, delivery_type, platform, category, image, price_from, price_may_vary, is_featured, rating_avg }`; `meta.display_currency`. `image` is null or an image object (section 2c); `image.path` is the stored path kept for existing clients, `image.url` the absolute URL (M-A4) |
| GET | `/catalog/products/{slug}/variants` | `country?` | `{ variants: [{ public_id, title, sku, region, denomination_value, denomination_currency, is_custom_amount, availability, purchasable, price, price_may_vary }] }`. 404 unless the product is active. Cost and margin are never returned. |
| GET | `/currencies` | | Active currencies for the switcher |
| POST | `/currency` | `{ currency }` | Sets the plain `currency` cookie and returns the currency actually used; an inactive or rateless choice returns the base currency. Not CSRF protected because it only changes a display preference. |
| GET | `/search/suggest` | `q` (2 to 100 chars), `locale?`, `commit?` | `{ suggestions: [{ type, title, slug }] }`, matching title prefix, fulltext (name, platform, category, attribute values such as a game name) and product or variant SKU prefix. Send `commit=1` on submit to count the term as a search; plain keystrokes are not counted. |
| GET | `/search/popular` | `locale?` | `{ terms: [...] }`, only terms that found something |

Query budget: one listing page runs at most **10 queries** with a cold cache, whatever the number of rows.

Not built: `/newsletter`. The cart routes are built in M7, see 3a.

### 2c. Built catalog discovery routes (M-A4)

Base `/api/v1/storefront` and, with the same controllers, `/api/v1/mobile/storefront` (`routes/storefront.php`, registered twice in the stateless `api` group). `storefront` limiter. Localized by `Accept-Language` (`content_translations` with the English column as fallback, ADR-036); prices in the display currency (`X-Currency`, else the web `currency` cookie) through the listing's pricing path. Public caching: `Cache-Control: public, max-age=N`, an ETag (304 on `If-None-Match`) and `Vary: Accept-Language` (plus `X-Currency, Cookie` where prices appear). Nothing personal is returned or cached. Cost, margin, supplier and secret fields never appear.

| Method | Path | Cache | Result |
|---|---|---|---|
| GET | `/catalog/products/{slug}` | 60 s, varies by currency | `{ product }`, `meta.display_currency`. 404 `not_found` unless the product is `active` (draft, inactive and deleted are hidden) |
| GET | `/platforms` | 300 s | `{ platforms: [{ name, slug, description, product_count, is_featured, logo, banner }] }`, active only, by `sort_order` then name |
| GET | `/categories` | 300 s | `{ categories: [{ name, slug, description, product_count, parent, banner }] }`, active categories whose parent (if any) is active, parents first, by `sort_order` then name; `parent` is the parent slug |
| GET | `/countries` | 3600 s | `{ countries: [{ code, name, dial_code, default_currency }] }`: exactly the countries `checkout/validate`, `payment-options` and `place-order` accept (one definition, `CountryDirectory`), sorted by localized name. Names: the stored English name, or the ICU (intl) name of the ISO code in Arabic |
| GET | `/home` | 60 s, varies by currency | `{ sections: [...], banners: { hero: [...], home_promo: [...] } }`, `meta.display_currency` |

`product_count` is the number of products the listing filter for that platform or category returns (active, with a priced active variant; categories count primary and secondary assignments).

**Image object** (listing `image`, product `images[]`, platform `logo`/`banner`, category `banner`, banner `image.desktop`/`image.mobile`): `{ path, url, alt, width, height, formats: { webp, avif? } }`. `url` and `formats.*` are absolute: `MEDIA_BASE_URL` (CDN) when configured, otherwise the media disk URL made absolute with `APP_URL`. Media has format conversions (WebP, AVIF when the server's GD supports it), no resized variants; `width` and `height` are the original's. Platform and category images are the admin's own uploads (null when not set); the API adds no logos, and BD8 (no official brand logos until rights are confirmed) governs what admins upload.

**Product** = the listing card fields plus:

```json
{
  "sku": "ORB-CARD", "description": "<p>HTML</p>", "instructions": "plain text", "terms": "plain text",
  "delivery_time_minutes": 5, "max_quantity_per_order": 10,
  "images": [{ "path": "...", "url": "https://...", "alt": "...", "width": 1200, "height": 600, "formats": { "webp": "https://..." } }],
  "regions": [{ "code": "US", "name": "United States" }],
  "rating": { "average": "4.50", "count": 8 },
  "availability": "in_stock",
  "variants_summary": { "count": 3, "regions": [{ "code": "US", "name": "United States" }], "has_custom_amount": false },
  "delivery_fields": [{ "key": "player_id", "label": "Player ID", "help_text": null, "type": "text", "required": true, "max_length": 255, "pattern": "[0-9]{6,10}", "options": null }],
  "seo": { "title": "...", "description": "...", "robots": null, "image": "https://..." }
}
```

- `description` is HTML limited to the sanitizer allow-list (`p`, `br`, headings, lists, `a`, `strong`, `em`, tables, `img` and similar; sanitized again on output); render it as HTML. `instructions` and `terms` are plain text with line breaks; render them escaped.
- `availability`: the best availability of the active variants (`in_stock`, `limited`, `out_of_stock`), null without active variants. Prices and per-variant purchasability stay in `/variants` (unchanged).
- `seo`: the product's `seo_metadata` in the request locale, else English, else the product name and short description.
- **`delivery_fields` are exactly what `POST /cart/items` validates `delivery_values` against**: both are built from one `DeliveryFieldSpec` list (`DeliveryFieldValidator::specsFor`), so they cannot drift. Rules: send `delivery_values` as `{ key: value }`; values are trimmed and an empty value counts as missing; `required` fields must be present; at most `max_length` characters; `type` `number` means digits only (`^\d+$`), `email` a valid email, `select` one of `options[].value`, `text` anything; `pattern`, when set, must match the **whole** trimmed value (PCRE with the `u` flag, anchored by the server as `^(?:pattern)$`); any key not listed is refused. Errors come back as `errors.delivery_values.{key}`. `label` and `help_text` are localized.

**Home section**: `{ key, title, subtitle, source, rule, items: [{ type, data }] }`, active sections by `sort_order`. `type` and `data`:

| type | data |
|---|---|
| `product` | the listing card plus `link` |
| `platform` | `{ name, slug, logo, banner, link }` |
| `category` | `{ name, slug, banner, link }` |
| `banner` | a banner (below) |
| `faq` | `{ id, question, answer }` (plain text) |
| `review` | the review shape of 2b plus `product: { name, slug, link }` |

`manual` sections show their admin-chosen items in order; an item that is no longer visible (inactive or unpublished product, inactive platform, category or FAQ, banner out of schedule) is dropped. `auto` sections follow `settings.rule` or, without one, the section key: `best_sellers` (keys `best_sellers`, `popular_gift_cards`: listing sort `popular`), `newest` (`recently_added`), `trending` (`trending_games`: `is_trending`), `featured` (`recommended`: `is_featured`), `reviews` (`customer_reviews`: featured approved reviews of active products), `faqs` (`faq`: active FAQs shown on the homepage). Product rules accept `settings.category`, `settings.platform`, `settings.delivery_type` (slugs or values as in the listing). `settings.limit` 1 to 24, default 12. Other auto keys (for example `trust_indicators`, `newsletter`) return no items; the app renders its own content for them. Every product is priced by the listing's path in one batch, so carousel prices equal listing prices in any currency.

**Banner**: `{ id, placement, title, subtitle, cta_label, link, image: { desktop, mobile }, starts_at, ends_at }`. Only active banners inside their schedule (`starts_at` <= now < `ends_at`, either may be null) are returned; `banners` holds the `hero` and `home_promo` placements in `sort_order`.

**Link target** (`link`): `{ type, value, url }` with `type` `product`, `category`, `platform` (value: slug), `search` (value: the text) or `url` (value: absolute URL); `url` is always the absolute web URL. A banner's stored `cta_url` maps as follows: an optional `/{locale}` prefix is ignored; `/products/{slug}` product; `/gaming/{slug}` platform; `/search?q=...` search; `/{slug}` category when the segment is not reserved (`catalog.reserved_slugs` and route prefixes); anything else, and any URL on another host, is `url`. Null when the banner has no `cta_url`.

Query budgets with a cold cache (tests enforce them; none grows with the number of rows): product detail 15, platforms 6, categories 6, countries 3, home 30, app config 8.

## 3. Checkout JSON

| Method | Path | Body | Result |
|---|---|---|---|
| POST | `/validate` | `{ email?, country }` | Repriced summary or 422 |
| GET | `/payment-options?country=` | | `[{ gateway, label, methods, payment_currency, payment_amount, display_amount, display_currency, notice }]` |
| POST | `/place-order` | `{ email, country, gateway, method?, consents, ga_client_id? }`, `Idempotency-Key` | `{ order_public_id, status, next: { type, client_secret?, approve_url?, redirect_url? } }` |
| POST | `/orders/{public_id}/paypal/capture` | | Server capture; returns order status |
| POST | `/orders/{public_id}/retry-payment` | `{ gateway, method? }` | New attempt |
| GET | `/orders/{public_id}/status` | | `{ status, payment_status, is_final }`, for the return page. Never confirms from the browser. |
| POST | `/guest/otp/send` | `{ order_public_id, email }` | OTP |
| POST | `/guest/otp/verify` | `{ order_public_id, code }` | Guest order session |

Place-order idempotency: same key and same body replays the stored response; same key with a different body returns 409 `idempotency_mismatch`; in-flight returns 409 `idempotency_in_progress`.

### 3a. Built cart and checkout routes (M7)

Registered in the `web` middleware group under `/api/v1` (session and CSRF on writes); the stateless twins for the app are in section 1c. A guest's cart is identified by the encrypted, HTTP-only `cart_token` cookie, set on the first `POST /storefront/cart/items`; a signed-in customer's cart by account, and a guest cart is adopted on sign-in when the customer has none. All amounts are in the display currency from the `currency` cookie (section 2a) and are money objects. Suspended accounts get 403.

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| GET | `/storefront/cart` | | `storefront` | `{ cart }` or `{ cart: null }` |
| POST | `/storefront/cart/items` | `variant_id` (variant `public_id`), `quantity` 1 to 10, `delivery_values?` `{ code: value }` | `storefront` | 201 `{ cart }`. Delivery values are validated against the product's fields (`errors.delivery_values.{code}`). Same variant and same values merge into one line. Refused: inactive, out of stock, custom amount (`custom_amount` is prohibited until a cost model exists), more than `max_quantity_per_order` per product, more than 20 lines. |
| PATCH | `/storefront/cart/items/{id}` | `quantity` | `storefront` | `{ cart }`; 404 for an item not in the caller's cart |
| DELETE | `/storefront/cart/items/{id}` | | `storefront` | `{ cart }`; 404 likewise |
| POST | `/storefront/cart/coupon` | `code` | `checkout` | `{ cart }` or 422 `coupon_invalid` with a reason in `message` (not active, expired, below the minimum, usage limit reached, already used by this customer, first order only, not applicable to the cart). Codes are case-insensitive. Validity is checked again, under the coupon row lock, at place-order (M11). |
| DELETE | `/storefront/cart/coupon` | | `storefront` | `{ cart }` |
| POST | `/checkout/validate` | `country` (active ISO code), `email?` | `checkout` | `{ cart }` repriced with checkout rules, or 422: `rate_stale` / `rate_missing` for the display currency, `item_unavailable`, `validation_failed` with `errors.items.{id}.delivery_values.{code}`, `coupon_invalid`, `cart_empty` |
| POST | `/checkout/place-order` | `email` (guests only; customers use their account email), `country`, `gateway`, `method?`, `currency` and `expected_total` (exactly what the customer was shown), `consents.terms` (must be accepted), `consents.marketing?`, `ga_client_id?`; header `Idempotency-Key` (8 to 128 of `A-Za-z0-9_-:.`) | `checkout` | 201 `{ order: { public_id, order_number, status, display_total, payment_total, expires_at }, next }` (`next`: section 3b, or null), `meta.duplicate: false`. 200 `{ order: { public_id, status } }`, `meta.duplicate: true` when the same buyer already has an open order with the same contents. A replay carries `Idempotency-Replayed: true`. Errors as for validate, plus `price_changed`, `gateway_not_eligible`, `order_total_invalid`, the idempotency 409s. `gateway_not_eligible` whenever the chosen gateway fails eligibility (M10, default deny). |
| GET | `/checkout/orders/{public_id}/status` | | `checkout` | `{ public_id, status, payment_status, is_final }`. Visible to the owning customer, or to the browser session that placed or verified a guest order; 404 otherwise. |
| POST | `/checkout/guest/otp/send` | `order_public_id`, `email` | `otp` | Always 200 with the same message. Emails a 6-digit code only when the email matches the guest order; at most 5 codes per order per hour; a new code replaces the old. |
| POST | `/checkout/guest/otp/verify` | `order_public_id`, `code` | `otp` | `{ order_public_id, email_verified: true }`, sets `orders.guest_email_verified_at` and opens the order to the session; or 422 `otp_invalid` (wrong, expired after 10 minutes, or 5 attempts used) |

`cart` shape: `{ public_id, currency, country, items: [{ id, variant: { public_id, title, sku }, product: { name, slug, delivery_type }, quantity, delivery_values, purchasable, block_reason, unit_price, subtotal, total }], coupon: { code, applied, error, limited_by_margin } | null, totals: { subtotal, discount, total }, price_may_vary, checkout_ready }`. `block_reason` is one of `unavailable`, `out_of_stock`, `custom_amount_unsupported`, `quantity_limit`, `not_sold_in_country`, or a pricing reason (`rate_stale`, `rate_missing`, `below_margin`, `cost_unavailable`, ...). Cost, floor and margin are never returned.

Not built in M7: a customer cancel endpoint (the state machine allows it). M10 changes to place-order: the gateway is checked by the real eligibility resolver (default deny), `orders.ip_country` comes from the GeoIP resolver, and on a 201 the first payment attempt is created at the provider after the order commits; its session is returned as `next` (section 3b). A replay returns the stored response, whose `next` is null, as it is when the first attempt could not be started; the client then calls retry-payment.

### 3b. Built payment routes (M10)

Same group, middleware and `checkout` limiter as 3a. The order routes are visible to the owning customer or the browser session that placed or verified a guest order; 404 otherwise. None of them can mark an order paid by itself: only a verified webhook or a server-side provider read whose amount and currency match the attempt does (architecture 9.1).

| Method | Path | Body | Result |
|---|---|---|---|
| GET | `/checkout/payment-options` | `country`, `email?` | `{ options: [{ gateway, label, methods: [{ code, label }], available, payment_currency, payment_amount, display_currency, display_amount, notice }] }`. Only eligible gateways are listed. `notice`: null, `charged_in_payment_currency` (the gateway charges another currency than the display currency, for example PayPal for an EGP, SAR or AED shopper), or `payment_currency_below_margin` with `available: false`. Errors as for validate. Advisory: place-order re-checks everything. |
| POST | `/checkout/orders/{public_id}/retry-payment` | `gateway?`, `method?` | 201 `{ next }`. A new attempt on the order's gateway with the frozen snapshot. 409 `order_not_payable` (not `pending_payment`, or already paid), `order_expired`, `payment_attempts_exhausted` (`orders.max_payment_attempts`, default 5); 422 `gateway_change_unsupported` (another gateway: place the order again), `gateway_not_eligible`; 503 `gateway_unavailable` when the provider could not create it. |
| POST | `/checkout/orders/{public_id}/verify-payment` | | `{ public_id, status, payment_status, is_final }` after a server-side read of the latest attempt. For the return page. |
| POST | `/checkout/orders/{public_id}/paypal/capture` | | Same shape. Server capture of an approved PayPal order (`PayPal-Request-Id: pay_capture:{payment}`); paid only on capture `COMPLETED`. 422 `capability_not_supported` for other gateways. |

`next` shapes: Wallet (M11) `{ payment_public_id, gateway: "wallet", type: "wallet" }`, returned when the order is already paid from the wallet balance (no redirect, no provider); Stripe `{ payment_public_id, gateway, type: "stripe", client_secret }`; PayPal `{ ..., type: "paypal", provider_order_id, approve_url }`; Paymob `{ ..., type: "redirect", redirect_url }` (Unified Checkout with the public key and the intention's client secret). Publishable values only.

### 2b. Built storefront routes (M11)

| Method | Path | Group | Body or query | Result |
|---|---|---|---|---|
| GET | `/api/v1/storefront/catalog/products/{slug}/reviews` | `api`, `storefront` limiter | `page`, `per_page` (max 50) | Approved reviews only, newest first: `[{ id, rating, title, body, reviewer, verified_purchase, created_at }]`; `reviewer` is the first name and last initial, never an email. `meta.rating_avg`, `meta.rating_count` (approved only). 404 unless the product is active. |
| POST | `/api/v1/storefront/affiliates/click` | `web` (CSRF), `storefront` limiter | `code`, `landing_url?` (site path or http(s) URL) | 202 whether or not the code exists. Sets the encrypted, HTTP-only `aff_ref` cookie (random token, `affiliates.attribution_days`, default 30). A later place-order from that browser is attributed to the last click, never to the buyer's own affiliate account. |

## 4. Customer JSON

Base `/api/v1/account` (web session) and `/api/v1/mobile/account` (customer bearer token, section 1c): the same routes, controllers and rules. Other customers' records are always 404.

| Method | Path | Status |
|---|---|---|
| GET | `/orders`, `/orders/{public_id}` | built (4c, M-A5) |
| POST | `/orders/{public_id}/units/{unit_public_id}/reveal` | built (4a): requires recent auth, returns code, audited |
| GET, PATCH | `/profile` | built (4c, M-A5) |
| PUT | `/password` | built (4c, M-A5) |
| GET, DELETE | `/devices`, `/devices/{id}` | built (4c, M-A5; replaces the planned `/sessions`) |
| GET, POST | `/notifications`, `/notifications/unread-count`, `/notifications/{id}/read`, `/notifications/read-all` | built (4c, M-A5) |
| GET, PATCH | `/notification-preferences` | built (4c, M-A5) |
| POST | `/email/verification-notification` | built (4c, M-A5; the web route `POST /email/verification-notification` stays) |
| GET | `/wallet`, `/wallet/transactions` | built (4b) |
| GET, POST | `/tickets`; POST `/tickets/{public_id}/messages` | built (4b) |
| GET, POST | `/reviews` | built (4b) |
| GET, POST | `/refund-requests` | built (4b) |
| POST, DELETE | `/security/two-factor` | built: POST starts enrolment and DELETE disables, both need `current_password`; DELETE is 403 for staff |
| POST | `/security/two-factor/confirm` | built: `code`; returns recovery codes once |
| GET | `/digital-codes` (masked only) | planned (the masked code is in the order detail) |
| GET, POST, DELETE | `/wishlist` | planned (not in the app scope) |

Reveal response: `{ code, pin, redemption_url, instructions }`, `Cache-Control: no-store`.

### 4a. Built code reveal routes (M9)

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| POST | `/api/v1/account/confirm-password` | `password` | `auth` | Stores `auth.password_confirmed_at` in the session (on the access token for `/api/v1/mobile/account`, section 1c); reveals need it within `fulfillment.reveal_recent_auth_minutes` (15) |
| POST | `/api/v1/account/orders/{order_public_id}/units/{unit_public_id}/reveal` | | `code-reveal` | Reveal body above, `Cache-Control: no-store, private`. Owner only (others 404), verified email, recent password (else 423 `recent_auth_required`), paid order and delivered unit (else 403 `reveal_denied`). Sets `first_revealed_at` |
| GET | `/api/v1/checkout/guest/orders/{order_public_id}/access` | signed URL from the delivery email, valid 72 h | `otp` | Opens the order to this browser session; `{ order_public_id, otp_required }`. Unsigned or expired: 403 |
| POST | `/api/v1/checkout/guest/orders/{order_public_id}/units/{unit_public_id}/reveal` | | `code-reveal` | As above for a guest: the session must hold both the signed link and a verified email one-time code (`POST /checkout/guest/otp/verify`) for this order |

Every attempt, granted or denied, is written to `audit_logs` (`digital_codes.revealed` / `digital_codes.reveal_denied`) and, where a code exists, to `digital_code_accesses`. Delivery emails link here and never contain a code.

### 4b. Built customer routes (M11)

Base `/api/v1/account` (session, active account), `account` limiter. Lists take `page`, `per_page` (max 50). Other customers' records are 404.

| Method | Path | Body | Result |
|---|---|---|---|
| GET | `/wallet` | | `{ wallet: { currency, balance: money, status, top_up_available: false } }`. Store credit from refunds and staff credits only (BD3); there is no top-up route. |
| GET | `/wallet/transactions` | | Statement, newest first: `[{ id, type, amount (signed), balance_after, note, created_at }]` |
| GET | `/reviews` | | The customer's reviews with `status` |
| GET | `/reviews/eligible` | | `{ items: [{ order_item_id, order: { public_id, order_number }, product: { name, slug } }] }`: items of paid orders with a delivered unit and no review yet |
| POST | `/reviews` | `order_item_id`, `rating` 1 to 5, `title?`, `body?` | 201, status `pending` until moderated. 403 `email_unverified`; 422 `review_not_eligible` (not the customer's, unpaid, nothing delivered); 409 `review_exists`. Tags are stripped. |
| GET | `/tickets` | | The customer's tickets (no messages) |
| POST | `/tickets` | `category` (`order`, `payment`, `refund`, `account`, `other`), `subject`, `body`, `order_public_id?` (own order) | 201 `{ ticket }` |
| GET | `/tickets/{public_id}` | | `{ ticket: { ..., messages: [{ id, from, body, created_at }] } }`. Internal staff notes are never included (excluded from the query and again by the resource). |
| POST | `/tickets/{public_id}/messages` | `body` | 201; the ticket becomes `pending_staff`. 409 `ticket_closed` |
| GET | `/refund-requests` | | `[{ id, order, reason, status, refund }]` |
| POST | `/refund-requests` | `order_public_id` (own, paid), `reason` (10 to 2000), `support_ticket_public_id?` | 201 `requested`. 409 `refund_request_open` (one open request per order), `refund_not_allowed` (unpaid) |

Not built in M11: guest tickets (the schema allows `guest_email`), ticket attachments, a customer affiliate dashboard.

### 4c. Built account routes (M-A5)

Base `/api/v1/account` and `/api/v1/mobile/account` (gaps G4, G5, G6 inbox). Limiter `account` (60/min per user) unless noted. Lists take `page`, `per_page` (default 20, max 50) and carry `meta.current_page`, `per_page`, `total`, `last_page`. Design: `PROJECT_ARCHITECTURE.md` ADR-038.

**Orders**

| Method | Path | Query / body | Result |
|---|---|---|---|
| GET | `/orders` | `status` (an order status), `from`, `to` (`Y-m-d`, inclusive, placement date) | Newest first: `[{ public_id, number, status, payment_status, is_final, totals: { display: money, payment: money\|null }, item_count, thumbnail_url, created_at, paid_at }]`. `payment_status`: `unpaid`, `processing`, `paid`, `partially_refunded`, `refunded`. `thumbnail_url`: absolute URL of the first item's first product image, or null. 422 `validation_failed` for an unknown status, a bad date or `to` before `from` |
| GET | `/orders/{public_id}` | | `{ order }`: the list fields plus `currency: { display, payment }`, `completed_at`, `items`, `payments`, `refunds`, `refund_state`, `timeline` (below). 404 for another customer's order, a guest order or an unknown id (`OrderPolicy`) |

```json
{
  "items": [{
    "id": 12, "name": "Steam Wallet - 20 USD", "product": { "slug": "steam-wallet" }, "delivery_type": "instant_code", "quantity": 2,
    "unit_price": { "amount": "20.00", "currency": "USD", "formatted": "$20.00" }, "line_total": { "amount": "40.00", "currency": "USD", "formatted": "$40.00" },
    "units": [{ "public_id": "01J...", "status": "delivered", "delivery_type": "instant_code", "revealable": true, "masked_code": "****7777",
                "delivered_at": "...", "first_revealed_at": null, "refunded_at": null }]
  }],
  "payments": [{ "public_id": "01J...", "attempt": 1, "provider": "stripe", "method": "card", "status": "succeeded", "amount": { "amount": "40.00", "currency": "USD", "formatted": "$40.00" }, "failure_code": null, "created_at": "..." }],
  "refunds": [{ "public_id": "01J...", "status": "succeeded", "destination": "wallet", "amount": { ... }, "created_at": "...", "completed_at": "..." }],
  "refund_state": "none",
  "timeline": [{ "status": "paid", "at": "..." }]
}
```

- `revealable` is a hint: the order is paid and the unit is delivered with a code. The reveal route (4a) still checks the owner or guest proofs, verified email and recent authentication, and audits every attempt. Take the unit `public_id` from here.
- Never in the detail: code, PIN, redemption URL (only `masked_code`, the stored masked form), provider ids, references or failure messages of payment attempts, the pricing breakdown (cost, floor, margin), delivery values, staff notes and order event messages. `timeline` lists status changes only (`order_events` with a `to_status`).
- `refund_state`: `none`, `pending` (order `refund_pending` or a refund requested, pending or unknown), `partial` (order `partially_refunded`, or a succeeded refund on an order that is not fully refunded), `full` (order `refunded`).

**Guest order detail** (`routes/checkout.php`, so `GET /api/v1/checkout/guest/orders/{public_id}` for the browser and `GET /api/v1/mobile/checkout/guest/orders/{public_id}` for the app; limiter `checkout`): the same `{ order }` shape for a guest order, `Cache-Control: no-store, private`, plus `meta.guest_access: { link_verified, otp_verified }`. Proof rule: the proof that opens the order for status and payment calls, which is smaller than the reveal proof: the browser session that placed the order or verified the email one-time code, or an `X-Guest-Order-Token` issued by place-order or OTP verify. A link-only token, another order's token, an expired token, no proof, and any order that belongs to a customer account get 404. A reveal still needs both the link and the OTP proof (4a, section 1c); `meta.guest_access` tells the client what it still has to do.

**Profile and password**

| Method | Path | Body | Result |
|---|---|---|---|
| GET | `/profile` | | `{ profile: { public_id, name, email, email_verified, phone, locale, display_currency, country_code, two_factor_enabled, created_at } }` |
| PATCH | `/profile` | any of `name` (1 to 120), `locale` (`en`, `ar`, null), `display_currency` (an active currency, null), `country_code` (an active country, null), `phone` (E.164 after removing spaces, dashes, dots and brackets, e.g. `+201001234567`, null) | `{ profile }`. Only the keys sent change; null clears. Audited `account.profile_updated` (field names; locale, currency and country values; never the phone). The email address cannot be changed here (no verified change flow exists yet) |
| PUT | `/password` | `current_password`, `password`, `password_confirmation` | Limiter `auth`. `{ signed_out: { mobile_families, web_sessions } }`. The new password follows the account's policy (customers 10+ characters, not breached; staff policy for staff) and must differ from the current one. Audited `account.password_changed` |

Locale: a web session uses the saved `locale` for its next requests (`Content-Language` follows); a stateless client keeps using `Accept-Language` (the bearer token is authenticated after the locale is chosen), so the app reads `profile.locale` at sign-in and sends it as `Accept-Language`. `display_currency` is stored for the client; the web still uses the `currency` cookie and the app `X-Currency`.

**Password change revocation.** Only the caller stays signed in. A bearer caller keeps its own sign-in (family and access token); a web caller keeps its session. Every other mobile sign-in is revoked (`revocation_reason = password_changed`: refresh tokens unusable, access tokens deleted), every other browser session is deleted when sessions are in the database (`SESSION_DRIVER=database`, the production default), and the remember-me token is rotated. A password reset by email link (`POST /reset-password`) now signs out every device and session (`password_reset`).

**Devices**

| Method | Path | Result |
|---|---|---|
| GET | `/devices` | `{ devices: [{ id, type, device_name, user_agent, ip, created_at, last_used_at, expires_at, current }] }`, `meta.web_sessions_listed`. `type` `mobile`: one per live sign-in (refresh-token family; `id` is the family id, `created_at` the sign-in, `last_used_at` the latest of the access token's last use and the last refresh, `expires_at` the family's absolute expiry). `type` `web`: browser sessions, only with the `database` session driver (`id` is an opaque `web_...` HMAC of the session id, never the id itself). `ip` is masked (`203.0.113.x`, `2001:0db8:0000:0000:x`). `current` marks the caller. Audited `account.devices_viewed` |
| DELETE | `/devices/{id}` | Signs that device out (family revoked with `device_revoked`, or the session row deleted). 409 `current_device` for the caller's own device (use logout); 404 for an unknown id or another customer's device. Audited `account.device_revoked` |

**Notifications (inbox; push is M-A18)**

| Method | Path | Query / body | Result |
|---|---|---|---|
| GET | `/notifications` | `unread` (boolean) | Newest first: `[{ id, type, category, title, body, link, read_at, created_at }]`, `meta.unread_count` |
| GET | `/notifications/unread-count` | | `{ unread_count }` |
| POST | `/notifications/{id}/read` | | `{ notification }` (idempotent). 404 for another customer's notification |
| POST | `/notifications/read-all` | | `{ marked_read, unread_count: 0 }` |
| GET | `/notification-preferences` | | `{ preferences: [{ category, channel, enabled, mandatory }] }`, all 12 cells: categories `orders`, `support`, `marketing`, `security` x channels `mail`, `database` (the inbox), `sms` |
| PATCH | `/notification-preferences` | `preferences: [{ category, channel, enabled }]` (1 to 12) | The full matrix. 422 `notification_preference_mandatory` (`errors.preferences.N.enabled`) when a mandatory cell is switched off; nothing of that request is saved. Audited |

- `type`: `order_delivered`, `order_partially_delivered`, `order_fulfilment_failed`, `support_ticket_replied`. `title` and `body` are rendered in the request language from the stored kind and number. `link`: `{ type: "order" \| "ticket", value: public_id, url }` (the ADR-037 shape; `url` is the web account page the emails link to).
- Payloads hold identifiers and the order or ticket number only: never a code, PIN, redemption URL or message body.
- Defaults without a saved row: `mail` and `database` on for orders, support and security; `sms` off (opt-in, ADR-034); marketing follows the latest marketing consent of the channel (email marketing for mail and the inbox, SMS marketing for SMS). Mandatory (always on, cannot be disabled): `orders.mail` (delivery link and failure notice, ADR-009) and `security.mail` (password reset and verification are always sent).
- The customer notifications `OrderDelivered`, `OrderFulfilmentFailed` and `SupportTicketReplied` follow these preferences for `mail` and `database` (M-A5); guests get email only. `sms` remains the n8n channel read by `CustomerContactPolicy`.

## 5. Admin JSON

Pattern: RESTful per resource under `/admin/api`. Each route has a permission from `SECURITY.md` section 4.

| Resource | Routes (beyond CRUD) |
|---|---|
| `platforms`, `categories`, `regions`, `attributes`, `delivery-fields` | CRUD, `POST reorder` |
| `products`, `products/{id}/variants`, `products/{id}/images` | CRUD, `POST bulk`, `POST {id}/activate`, `POST {id}/deactivate` |
| `suppliers` | CRUD, `POST {id}/test-connection`, `POST {id}/sync-catalog`, `POST {id}/sync-pricing`, `POST {id}/credentials`, `GET {id}/capabilities`, `GET {id}/balance` |
| `supplier-mappings` | CRUD, `POST bulk`, `GET suggestions` |
| `orders` | `GET {id}`, `GET {id}/timeline`, `POST {id}/review`, `POST {id}/retry-verified-unit`, `POST {id}/reconcile`, `POST {id}/notes` |
| `transactions/{id}/mark-not-accepted` | Requires `orders.reconcile`, reason, evidence |
| `manual-tasks` | List, `POST {id}/claim`, `POST {id}/complete` |
| `digital-codes` | `POST import`, `POST {id}/reveal` (reason required) |
| `refunds` | Create (`Idempotency-Key`), `POST {id}/approve` |
| `payment-gateways` | `PATCH {id}`, `POST {id}/credentials`, `POST {id}/test`, currencies, eligibility rules |
| `payments` | Read, `POST {id}/verify` |
| `currencies`, `exchange-rates` | CRUD, `POST {id}/override`, `POST fetch-now`, `POST {id}/approve-pending` |
| `pricing-rules`, `variant-prices` | CRUD, `POST simulate` |
| `coupons`, `promotions`, `affiliates` | CRUD |
| `pages`, `blog-posts`, `faqs`, `menus`, `homepage-sections`, `banners`, `redirects`, `media` | CRUD, reorder, upload |
| `tickets`, `refund-requests` | Reply, assign, status, internal notes |
| `customers` | Read, `PATCH status`, notes, wallet adjust |
| `users`, `roles` | CRUD, permissions |
| `settings/{group}` | `GET`, `PATCH` |
| `service-accounts` | CRUD, token issue and revoke (shown once) |
| `mcp-actions` | Built (M16, section 8): list, `POST {id}/approve`, `POST {id}/reject` |
| `mcp-tokens` | Built (M16, section 8): list, issue (shown once), revoke |
| `ai-suggestions` | List, `POST {id}/approve`, `POST {id}/reject` |
| `saved-filters` | CRUD by `table_key` |
| `exports` | `POST`, `GET {id}` |
| `dashboard/*`, `reports/*` | `?from=&to=`; cached JSON for Chart.js |

Tables under `/admin/api/tables/{table}`: `products`, `orders`, `payments`, `customers`, `suppliers`, `supplier-products`, `supplier-logs`, `webhook-logs`, `tickets`, `audit-logs`, and others per `IMPLEMENTATION_ROADMAP.md`.

Bulk actions: `POST /admin/api/{resource}/bulk` with `{ action, ids[] }`, returning `{ processed, failed: [{ id, error }] }`.

### 5a. Built admin routes (M5, M6)

All use the list conventions (`page`, `per_page`, `sort`, `filter[..]`, `q`; unknown filters and sorts are ignored) and return API Resources in the standard envelope. `POST` requires the fields, `PATCH` takes any subset.

| Resource | Routes | Read permission | Write permission |
|---|---|---|---|
| `platforms`, `categories` | CRUD, `POST reorder` | `catalog.view` | `catalog.manage` |
| `regions`, `attributes`, `delivery-fields` | CRUD | `catalog.view` | `catalog.manage` |
| `products` (by `public_id`) | CRUD, `POST bulk` | `catalog.view` | `catalog.manage`; bulk `activate` and `deactivate` need `catalog.publish` |
| `products/{id}/activate`, `/deactivate` | `POST` | | `catalog.publish` |
| `products/{id}/variants`, `products/{id}/images` | CRUD, scoped to the product | `catalog.view` | `catalog.manage` |
| `pages` | CRUD | `content.pages` | `content.pages` |
| `blog-categories`, `blog-posts` | CRUD | `content.blog` | `content.blog` |
| `faq-groups`, `faqs` | CRUD, `POST reorder` | `content.faq` | `content.faq` |
| `menus`, `menus/{id}/items` | CRUD | `content.menus` | `content.menus` |
| `homepage-sections` | CRUD, `POST reorder`, `PUT {id}/items` | `content.homepage` | `content.homepage` |
| `banners` | CRUD, `POST reorder` | `marketing.banners` | `marketing.banners` |
| `redirects` | CRUD | `seo.redirects` | `seo.redirects` |
| `media` (by `public_id`) | List, show, `POST` upload (multipart `file`, `alt?`, `folder?`, `license_note?`), `PATCH` details, `DELETE` | `content.media` | `content.media` |
| `currencies` (by code) | List, show, `PATCH` (`markup_pct`, `rounding_rule`, `is_active`, `sort_order`) | `pricing.view` or `currency.manage` | `currency.manage` |
| `exchange-rates` | List; `GET {code}/history` | `pricing.view` or `currency.manage` | |
| `exchange-rates/fetch-now` | `POST` | | `currency.manage` |
| `exchange-rates/{code}/override` | `POST { rate, expires_at?, reason }`, `DELETE { reason }` | | `currency.manage` |
| `exchange-rates/{code}/approve-pending`, `/reject-pending` | `POST { reason }` | | `currency.manage` |
| `pricing-rules` | CRUD | `pricing.view` | `pricing.manage`; policy `allow` also needs `pricing.override_margin` and `override_reason` |
| `pricing-rules/simulate` | `POST { variant, country_code?, currency? }`: engine breakdown, changes nothing | `pricing.view` | |
| `variant-prices` | CRUD | `pricing.view` | `pricing.manage`; a price below the margin floor, or with no known cost, also needs `pricing.override_margin` and `override_reason` |

Rules worth knowing:

- Money, rates and percentages must be sent as decimal **strings**. A JSON number is a 422.
- `products.status` and publication are set only through activate, deactivate and bulk. Publishing needs an active variant, and `direct_topup` or `account_recharge` products also need a required delivery field.
- Category slugs may not be a reserved word: the list in `config/catalog.php` plus the first segment of every registered route.
- `description` and page and post `body` are sanitized against an allow-list on write.
- Delivery field `options` are `[{ value, label }]`; `validation_regex` has no delimiters and must match the whole value.
- Upload accepts JPEG, PNG, WebP and AVIF up to 8 MB and 8000 px, re-encoded to WebP under a random name. SVG is refused for everyone at present.
- `rounding_rule` is `{ "mode": "step", "step": "0.05" }` or `{ "mode": "ends_with", "value": "0.99" }`, and may not be finer than the currency's exponent.
- `conditions` on a dynamic rule take `availability` (list), `cost_min` and `cost_max`. Minimum margin cannot be set on a platform-scope rule.
- A non-base currency cannot be activated without a current rate (`rate_missing`, `rate_stale`). The base currency cannot be deactivated or overridden. Currencies are seeded ISO reference data, so there is no create or delete route.
- Exchange rate routes are keyed by currency code rather than the table's id, because an override can supply a currency's first rate.

### 5b. Built supplier and fulfilment routes (M8, M9)

| Resource | Routes | Permission |
|---|---|---|
| `suppliers` | CRUD (`status` defaults to `inactive`; `adapter_key` must be registered; `base_url_override` HTTPS on the adapter's host list) | read `suppliers.view`, write `suppliers.manage` |
| `suppliers/{id}/credentials` | `POST { mode, credentials: {key: value}, webhook_secret? }`. Write-only: returns `credentials_configured` per mode, never values | `suppliers.credentials` |
| `suppliers/{id}/test-connection` | `POST`, `{ reachable, latency_ms, error }`, 6 per minute | `suppliers.manage` |
| `suppliers/{id}/sync-catalog`, `/sync-pricing` | `POST`, 202, queued on `sync`, 6 per minute | `suppliers.sync` |
| `suppliers/{id}/capabilities` | `GET`: `{ capabilities: {capability: full/advisory/none/unverified}, delivery_types, default_per_request_cap }` | `suppliers.view` |
| `suppliers/{id}/balance` | `GET`: cached balance and the last 30 snapshots | `suppliers.view` |
| `suppliers/{id}/logs` | `GET`, list conventions; `filter[succeeded]=0` for failed requests, `filter[operation]` | `suppliers.logs` |
| `supplier-mappings` | CRUD by id. One `primary` per variant; the supplier product must belong to the supplier; internal stock creates its supplier product on first mapping | read `suppliers.view` or `mappings.manage`, write `mappings.manage` |
| `orders/{public_id}/timeline` | `GET`: order events, including unit events (`unit.*`) | `orders.view` |
| `orders/{public_id}/review` | `POST { reason }`: `manual_review` to `processing`; pending units are then planned | `orders.review` |
| `orders/{public_id}/retry-verified-unit` | `POST { unit_public_id, reason }`: only a unit whose last attempt is `rejected` or `not_accepted_verified`, on an order in `manual_review` or `partially_fulfilled`. Reuses the client reference on the same supplier | `orders.review` |
| `orders/{public_id}/reconcile` | `POST`: status lookup now for every unresolved attempt, past the deadline too; never places an order | `orders.reconcile` |
| `transactions/{public_id}/mark-not-accepted` | `POST { reason, evidence }`: sets `not_accepted_verified`, releasing the active lock; the unit stays in review until retried | `orders.reconcile` |
| `manual-tasks` | List (`filter[status]`), `GET {id}` (includes the customer's delivery values, such as player ID), `POST {id}/claim`, `POST {id}/complete { reason }`, `POST {id}/reject { reason }` | `orders.fulfill_manual` |
| `digital-codes/import` | `POST { product_variant_id, codes: [{ code, pin?, redemption_url?, expires_at? }] }` up to 1000. Duplicates are rejected by position (`rejected: [{ index, reason }]`), never echoed | `digital_codes.import` |
| `digital-codes/{public_id}/reveal` | `POST { reason }`: staff reveal, audited, `no-store` | `digital_codes.reveal` (explicit grant) |

### 5c. Built payment admin routes (M10)

| Resource | Routes | Permission |
|---|---|---|
| `orders/{public_id}/refunds` | `POST { unit_ids: [unit public ids], reason: undelivered/customer_request/fraud/admin, note }`, 30 per minute. Staff refund of chosen units (BD4 admin discretion; failed or delivered units). The order enters `refund_pending` through the state machine (from `completed`, `partially_refunded` or `manual_review`). 201 `{ public_id, status, reason, destination, amount: { amount, currency }, amount_base, failure_reason, note, created_at, completed_at }`; the refund is then sent to the provider (`refund:{public_id}` key). 409 `refund_duplicate` (a unit already refunded), `refund_not_allowed`, `refund_in_progress` (Paymob, one at a time), `refund_exceeds_payment`; 422 `refund_units_invalid`, `refund_not_supported` | `refunds.create` |

`destination` (M11, optional): `original_method` (default) or `wallet`. A wallet refund credits the customer's wallet with the refund's base amount (`credit_refund`, key `refund:{public_id}`) and never calls the provider; only a registered customer's order (422 `refund_destination_unavailable`). A refund of a wallet payment always goes back to the wallet.

Undelivered units are refunded automatically by the system (BD4 default); there is no route for that. Gateway configuration and write-only credential routes are M15; until then gateways are configured in the database (seeded inactive, so nothing is eligible).

Not built: `GET supplier-mappings/suggestions`, `POST supplier-mappings/bulk` (M14), and `POST /webhooks/suppliers/{key}`: Reloadly's webhook signature is `UNVERIFIED`, so polling is the only path.

### 5d. Built marketing, review, support and wallet routes (M11)

| Resource | Routes | Permission |
|---|---|---|
| `coupons` | CRUD by id. `code` (stored upper case, unique), `type` `percent`/`fixed`, `value` (decimal string; percent at most 100), `currency?` (fixed only; null means base, otherwise converted at the stored rate when used), `max_discount_base?`, `min_order_base?`, `usage_limit?`, `usage_limit_per_user?` (null = unlimited), `applicable_to?` `{ products?, categories?, platforms? }` (ids, any match), `stackable`, `allow_below_margin`, `first_order_only`, `starts_at?`, `ends_at?`, `is_active`. `used_count` is read-only; `usage_limit` cannot go below it. Delete is a soft delete. Audited | `marketing.coupons` |
| `promotions` | CRUD by id, with `targets: [{ type: product/variant/category/platform, id }]` replacing the targets. Every change marks all variants for `pricing:recalculate-dirty`. Audited | `marketing.promotions` |
| `affiliates` | CRUD by id. `user_id` (customer, create only), `code`, `status` (`pending`, `active`, `suspended`), `commission_pct`, `payout_details?` (write-only, encrypted; only `payout_details_configured` is returned), `notes?` | `marketing.affiliates` |
| `affiliates/{id}/commissions` | `GET`, `filter[status]` | `marketing.affiliates` |
| `affiliate-commissions/{id}/approve` | `POST`: `pending` to `approved`; 409 otherwise | `marketing.affiliates` |
| `reviews` | List (`filter[status]`, `filter[product_id]`, `filter[is_featured]`, `filter[rating]`), `GET {id}`, `POST {id}/approve`, `POST {id}/reject { reason }`, `PATCH {id}/feature { is_featured }` (approved only), `DELETE {id}`. Product rating recomputed from approved reviews. Audited | `catalog.manage` (no review permission exists in `SECURITY.md` 4) |
| `tickets` | List (`filter[status]`, `filter[priority]`, `filter[category]`, `filter[assigned_to]`, `q`), `GET {public_id}` (all messages, `is_internal` marked) | `support.view` |
| `tickets/{public_id}/messages` | `POST { body, is_internal? }`. A public reply sets `pending_customer` and emails the customer a link (no text); an internal note is visible to staff only | `support.reply` |
| `tickets/{public_id}/assign` | `POST { assigned_to: user id or null }`: staff with `support.reply` only. Audited | `support.manage` |
| `tickets/{public_id}` | `PATCH { status?, priority? }`. Audited | `support.manage` |
| `refund-requests` | List (`filter[status]`) | `support.refund_requests` |
| `refund-requests/{id}/approve` | `POST { unit_ids, reason, note, destination? }`: creates the refund through 5c (all its rules apply); the request becomes `completed` when the refund succeeds | `support.refund_requests` and `refunds.create` |
| `refund-requests/{id}/reject` | `POST { note }`. Audited with the note | `support.refund_requests` |
| `customers/{public_id}/wallet` | `GET`; `GET .../transactions` (with `reference_type`, `reference_id`, `created_by`) | `customers.view` |
| `customers/{public_id}/wallet/adjustments` | `POST { direction: credit/debit, amount, reason }`, header `Idempotency-Key` (8 to 128 of `A-Za-z0-9_-:.`), 30 per minute. Posted once per key (`credit_admin`/`debit_admin`); a debit beyond the balance is 422 `wallet_insufficient_balance`. Audited | `customers.wallet_adjust` |
| `customers/{public_id}/wallet/status` | `POST { status: active/frozen, reason }`. A frozen wallet cannot pay; refunds still credit it. Audited | `customers.wallet_adjust` |

Not built: affiliate payouts (`affiliate_payouts`), DataTables endpoints for these resources (M14/M15).

## 6. Webhooks

| Path | Verification | Response |
|---|---|---|
| `POST /webhooks/stripe` | `Stripe-Signature` over the raw body (SDK, 5-minute tolerance, rotated secrets) | 200 `{ success, duplicate }` on stored or duplicate, 400 `invalid_signature` on a bad or missing signature |
| `POST /webhooks/paypal` | `POST /v1/notifications/verify-webhook-signature` with the raw event | Same; 503 `verification_unavailable` when PayPal's verification API cannot be reached, so PayPal retries |
| `POST /webhooks/paymob` | HMAC-SHA512 over the 20 fields; `hmac` from the query string, else the body | Same |
| `POST /webhooks/suppliers/{key}` | Per adapter; advisory only | Not built |

Built in M10 (payment rows). Outside the `web` group: no session, cookies or CSRF; `webhooks` limiter (600/min per IP). A valid event is stored once in `payment_webhooks` (unique per gateway and event id; payload encrypted; signature headers not kept), then processed on the `webhooks` queue: the payment is found by provider reference and re-read from the provider before anything changes. A bad signature is not stored; it is recorded in `security_events`. Not built: `GET /payments/paymob/return` (the provider redirects to the M12 return page at `PAYMENTS_RETURN_URL`, which calls verify-payment).

## 7. Automation API (built, M17)

Prefix `/api/v1/automation`, middleware group `automation`: `RecordAutomationCall` (one `automation_logs` row of kind `call` per request, refused ones included; method and redacted query only, never the body), `AuthenticateAutomationRequest`, `throttle:automation`. Route names after `automation.` are stored as the endpoint. Responses use the standard envelopes and carry `Cache-Control: no-store`. The workflows that call each route are in `N8N_AUTOMATIONS.md`. The n8n workflows are authored and validated, not executed.

### Authentication

| Header | Rule |
|---|---|
| `Authorization: Bearer <token>` | Live Sanctum token of an enabled `n8n` service account; sessions ignored; the account IP allow-list applies |
| `X-Workflow-Id` | `^[A-Z][0-9]{2}$`, a workflow registered and **enabled** in `automation_workflows` (`php artisan automation:workflows enable A01`) |
| `X-Execution-Key` | `^[A-Za-z0-9:._-]{1,128}$`; the schedule window (`A01:2026-11-10`) or `<ID>:<event_id>` |
| `X-Automation-Timestamp` | Unix seconds; refused when more than 300 s from the server clock |
| `X-Automation-Nonce` | `^[A-Za-z0-9_-]{16,64}$`; refused if seen for the account in the last 900 s (cache `add`, stored only after the signature verified) |
| `X-Automation-Signature` | `hex(HMAC-SHA256(secret, timestamp + "\n" + nonce + "\n" + METHOD + "\n" + path_with_query_as_sent + "\n" + workflow_id + "\n" + execution_key + "\n" + raw_body))`, secret = the account's `hmac_secret` |

Errors (standard envelope): `unauthenticated` 401, `signature_missing` 401, `signature_invalid` 401, `timestamp_stale` 401, `replay_detected` 401, `forbidden` 403 (IP), `workflow_unknown` 403, `workflow_disabled` 403, `scope_missing` 403, `rate_limited` 429, `validation_failed` 422, `execution_not_started` 409, `task_driver_mismatch` 409, `event_type_mismatch` 422. A wildcard ability grants nothing; scopes are exact `automation:` abilities.

### Routes

| Method | Path | Scope | Notes |
|---|---|---|---|
| POST | `/executions` | `executions:write` | `{action: start}`: 201 `started`, or 200 `skipped_duplicate` (`data.duplicate: true`, the workflow stops) when the key is `started` or `success`; a `failed` key restarts (`attempt` + 1). `{action: finish, status: success\|failed, summary?, error?}`: 409 unless running. Sets `automation_workflows.last_success_at` / `last_failure_at` |
| POST | `/alerts` | `executions:write` | `{severity: info\|warning\|critical, title, message?, failed_workflow_id?, failed_execution_key?, n8n_execution_id?, node?, context?}`: written to the Laravel log as `automation.alert`; closes the failed execution (by key, else the newest running one of that workflow in 24 h) |
| GET | `/reports/{sales, profit, revenue-comparison, weekly-business, best-selling?by=game\|platform, profitable-products, underperforming-products, supplier-performance, gateway-performance, customer-retention, coupon-performance, pricing-recommendations, expenses, gateway-fees, unusual-transactions, refunds}` | `reports:read` | Window: `period` (`today`, `yesterday`, `last_15_minutes`, `last_hour`, `last_24_hours`, `last_7_days`, `last_week`, `last_30_days`, `last_month`) or `from`/`to` dates (max 366 days); `limit` 1 to 100; `threshold_pct` |
| GET | `/payments/reconciliation` | `reports:read` | Runs since `since` (default 24 h) and open items by type |
| GET | `/monitor/{orders/failed?since, orders/pending?older_than, suppliers/balances?level=warning\|critical, suppliers/health?detail=1, payments/failures?window, products/low-margin, margin-alerts, queues?window, exceptions, scheduler, database, latency?window, webhooks?window}` | `monitor:read` | Each result has `alert` (true when the workflow should notify) |
| POST | `/jobs/{catalog-sync, exchange-rates, reconcile-payments}` | `jobs:run` | Only workflow A09, A10 or E02 respectively; 409 `task_driver_mismatch` unless `settings.automation.task_drivers.{catalog_sync, exchange_rates, payment_reconciliation}` is `n8n`; needs a running execution; queues the same artisan command the scheduler runs, once per execution (202, then 200 `repeated`) |
| GET | `/data/trending?days&limit`, `/data/product-context/{product_ulid}`, `/data/products/missing-descriptions?limit` | `marketing:read` | Aggregated internal data and public catalog facts; no customers, costs or suppliers |
| POST | `/suggestions` | `suggestions:write` | `{type, product_id?, content: {title, ...}}` (content at most 20 KB): stored as `pending_review` (201), or the existing row for identical content (200). Each workflow may create only its own types (B10 `pricing`, C01 to C03 `product`, C04 `campaign`, C05 `bundle`, C06 `coupon`, C07 `blog_idea`, C08 `seo_description`, C09 `marketing_copy`, C10 `social_post`) |
| POST | `/customers/messages/prepare` | `customers:message` | D01 to D05: `{event_id}` of an outbox event of the workflow's type; returns `eligible: false` with `reason` (`guest_has_no_preferences`, `account_not_active`, `no_phone_number`, `channel_not_enabled_by_customer`, `already_sent`) or the recipient (`customer_id`, `phone`, `locale`), `dedupe_key` and `message.text` |
| GET | `/customers/segments/{abandoned, review-eligible, reactivation, recommendations}` | `customers:message` | Only D06, D07, D08, D09 respectively; up to 100 eligible recipients with `dedupe_key`, `customer_id`, `phone`, `locale`, `message.text` |
| POST | `/customers/messages/log` | `customers:message` | `{dedupe_key, customer_id, status: sent\|failed, provider_message_id?, error?}`; the key must start with the caller's workflow id; idempotent: a `sent` row is never changed (200 `repeated`) |

Results Laravel cannot compute are returned as `{status: "unavailable", reason}` (or an `unavailable` list beside real figures): exceptions (no exception store), web request latency, database replication, operating expenses, external market trends, competitor prices and price elasticity, provider latency and decline reasons, guest retention, visitor and cart conversion (from the M16 reader).

Customer contact rules (`CustomerContactPolicy`): SMS only; active customer account (not staff) with a phone number; order and support messages need an enabled `notification_preferences` row (`sms` channel) for that category; marketing needs the latest `sms_marketing` consent to be a grant and no disabled marketing SMS preference. Guests are never contacted by n8n.

Admin (session, 2FA, `service_accounts.manage`): `GET /admin/api/automation-credentials`, `POST /admin/api/automation-credentials` (`account`, `scopes[]`, `rate_limit_per_minute`, `allowed_ips[]`, `expires_in_days`; 201 with `token` and `hmac_secret` once; issuing replaces the account's secret; 10 per minute), `DELETE /admin/api/automation-credentials/{token_id}`. Console: `php artisan automation:credentials {issue|list|revoke}`, `php artisan automation:workflows {list|enable|disable}`, `php artisan automation:flush-outbox`.

### Outbound webhooks (Laravel to n8n)

`automation:flush-outbox` (every minute) posts each due `automation_outbox` event to `N8N_WEBHOOK_URL` as `{event_id, event_type, occurred_at, attempt, payload}` with headers `X-EGS-Webhook-Token` (`N8N_WEBHOOK_AUTH_TOKEN`), `X-Signature-Timestamp`, `X-Signature` (`hex(HMAC-SHA256(N8N_OUTBOUND_SECRET, timestamp + "." + raw_body))`), `X-Event-Id`, `X-Event-Type`. A 2xx marks it delivered; otherwise it is retried after 1, 5, 15, 60, 180 and 360 minutes and then marked `failed` with the HTTP status or a short reason (reported by `GET /monitor/webhooks`). Events: `order.created`, `payment.succeeded`, `order.delivered` (completed or partially fulfilled), `order.failed`, `ticket.updated` (public staff reply or status change). Payloads hold identifiers only. Writing is off unless `AUTOMATION_OUTBOX_ENABLED=true`.

## 8. MCP API (built, M16)

Prefix `/api/v1/mcp`, middleware group `mcp`: `RecordMcpCall` (one `mcp_audit_logs` row per call), `AuthenticateMcpToken` (bearer Sanctum token of an enabled `mcp` service account, IP allow-list), `throttle:mcp`. One route per tool; the route name after `mcp.` is the tool name. Full tool schemas, tiers and the confirmation flow are in `MCP_SERVER.md`. Responses use the standard envelopes and carry `Cache-Control: no-store`.

| Method | Path | Tool | Scope |
|---|---|---|---|
| GET | `/session` | (server start-up: account, read-only flag, scopes) | any valid token |
| GET | `/products`, `/products/search`, `/products/{id_or_sku}` | `list_products`, `search_products`, `get_product` | `products:read` |
| GET | `/variants/{sku}/pricing`, `/variants/{sku}/availability` | `get_product_pricing`, `check_product_availability` | `products:read` |
| GET | `/orders`, `/orders/search`, `/orders/{number}`, `/orders/{number}/status` | `list_orders`, `search_customer_orders`, `get_order_details`, `get_order_status` | `orders:read` |
| GET | `/analytics/{sales-summary, profit-summary, top-selling-products, supplier-performance, failed-orders, conversion-metrics}` | `get_sales_summary` ... `get_conversion_metrics` | `analytics:read` |
| GET | `/suppliers/{balances, status, sync-status}`, `/inventory/low-margin-products` | `get_supplier_balances`, `get_supplier_status`, `get_supplier_sync_status`, `get_low_margin_products` | `suppliers:read` |
| GET | `/marketing/{best-selling-products, underperforming-products, coupon-performance, customer-purchase-trends}` | marketing tools | `marketing:read` |
| GET | `/operations/{pending-orders, failed-transactions, pending-reconciliation, system-health}` | operations tools | `operations:read` |
| POST | `/actions/{update-product-price, activate-product, deactivate-product, create-coupon, trigger-catalog-sync, retry-verified-failed-order}` | write tools: `202` pending action | the tool's write scope; account not read-only |
| POST | `/actions/{action_id}/confirm` | (elicitation approval, `confirmation_token`) | `actions:confirm`, the requesting token |
| GET | `/actions/{action_id}` | `get_action_status` | any write scope |

Writes return `202 { action_id, tool, status: "pending_approval", risk_tier, summary, arguments, expires_at, approval: { admin_panel, elicitation }, confirmation_token? }`. New error codes: `scope_missing`, `read_only` (403); `duplicate_action`, `no_change`, `retry_not_allowed`, `supplier_inactive`, `action_not_pending` (409); `confirmation_invalid`, `admin_approval_required` (403); `confirmation_expired` (410).

Admin (session, 2FA): `GET /admin/api/mcp-tokens`, `POST /admin/api/mcp-tokens` (`account`, `scopes[]`, `allow_writes`, `rate_limit_per_minute`, `allowed_ips[]`, `expires_in_days`; 201 with the token once; 10 per minute), `DELETE /admin/api/mcp-tokens/{id}` (`service_accounts.manage`); `GET /admin/api/mcp-actions?status=`, `POST /admin/api/mcp-actions/{id}/approve` (`note?`), `POST /admin/api/mcp-actions/{id}/reject` (`note`) (`mcp_actions.approve`, plus the tool's own permission to approve).
