# API Documentation

**Status:** built so far: the authentication routes in section 1a, the two-factor routes in section 4, `GET /admin/api/me` and the envelopes (M3); section 2a storefront catalog, search and currency (M5, M6); section 5a admin catalog, content, media, currency and pricing (M5, M6); section 3a cart, checkout and guest verification (M7); section 4a code reveal and section 5b suppliers, mappings, fulfilment, manual tasks and digital codes (M8, M9); section 3b payment options, attempts, verification and PayPal capture, section 5c staff refunds and section 6 payment webhooks (M10); section 2b product reviews and affiliate clicks, section 4b customer wallet, reviews, tickets and refund requests, section 5d admin coupons, promotions, affiliates, reviews, support and customer wallets, real coupons in 3a and the wallet gateway in 3b (M11); section 8 MCP API with admin MCP tokens and action approval (M16); section 7 automation API for n8n with admin automation credentials (M17); section 1b mobile authentication with customer bearer tokens (M-A2); section 1c stateless cart, checkout, account and locale for the app under `/api/v1/mobile` (M-A3); section 2c product detail, platforms, categories, countries and home and section 1d app config (M-A4); section 4c account orders, guest order detail, profile, password, devices, notification inbox and preferences, and the sessionless email links of 1a (M-A5); section 1f contract fixtures for the app (M-A7); the wallet statement's linked `order` (4b) and the app paths in `apple-app-site-association` (1e) (M-A15). Everything else is planned. Update the status column per route group as milestones land.

## 1. Conventions

### Base paths

| Group | Prefix | Auth | Status |
|---|---|---|---|
| Public JSON (storefront JS) | `/api/v1/storefront` | Session optional, CSRF for writes, rate limited | catalog, search and currency built (2a); cart built (3a); reviews and affiliate clicks built (2b); product detail, platforms, categories, countries, home built (2c); newsletter planned |
| App config | `/api/v1/app/config` (and `/api/v1/mobile/app/config`) | none, public, cached 60 s | built (M-A4, section 1d) |
| Customer JSON | `/api/v1/account` | Session, verified email where noted | two-factor, confirm-password and code reveal built (4a); wallet, reviews, tickets, refund requests built (4b); orders, profile, password, devices, notifications and preferences built (4c, M-A5); wishlist, review edit and sign-out-others built (4d, M13); digital-codes JSON list not built |
| Checkout JSON | `/api/v1/checkout` | Session or guest cart token, CSRF | validate, place-order, status, guest OTP (3a), guest code access (4a) and payment routes (3b) built |
| Admin JSON | `/admin/api` | Session, `type=staff`, confirmed 2FA with a session that passed the challenge, permission per route, CSRF | `GET /me`, sections 5a to 5d built; rest planned |
| Yajra DataTables | `/admin/api/tables/{table}` | As admin | planned |
| Provider webhooks | `/webhooks/{provider}` | Signature only, no session, no CSRF, `webhooks` limiter | payment gateways built (6); suppliers planned |
| Automation (n8n) | `/api/v1/automation` | Sanctum bearer token of an `n8n` service account + HMAC signature, timestamp and nonce + scopes | built (M17, section 7) |
| MCP | `/api/v1/mcp` | Sanctum bearer token of an `mcp` service account + scopes | built (M16, section 8) |
| Mobile app | `/api/v1/mobile` | Stateless (`api` group, no session, no CSRF); signed-in routes need a customer Sanctum bearer token with the `customer` ability | auth built (M-A2, section 1b); cart, checkout and account built (M-A3, section 1c); public storefront reads (2a, 2b reviews, 2c) and app config (1d) built (M-A4); account orders, profile, devices, notifications and guest order detail built (M-A5, section 4c); `client` channel and payment returns built (M-A6, section 1e) |
| App links | `/app/payments/{return,cancel}/{order}`, `/.well-known/assetlinks.json`, `/.well-known/apple-app-site-association` | none, no session or cookies | built (M-A6, section 1e) |

Web pages (Blade) are listed in `SEO_STRATEGY.md` section 2 and built in M12 (`routes/storefront_pages.php`, see `PROGRESS.md` M12). They read through the same services and resources as the JSON and write only through the JSON routes.

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
| `X-Currency` | Display currency for stateless clients; overrides the `currency` cookie (section 1c). An explicit choice: always wins over the country currency (section 1g) |
| `X-Device-Integrity` | `ok`, `compromised` or `unknown`: the app's device-integrity verdict on mobile code reveals, audited and never blocking (section 1g) |
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
| 423 | `recent_auth_required` | absent | Ask for the password (`POST /api/v1/account/confirm-password`) or, in the app, a biometric confirmation (`POST /api/v1/mobile/auth/confirm-biometric`, section 1g), then retry |
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
| `mobile-push` | 10/min per customer and 60/min per IP (`config/push.php`, M-A18) |
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
| GET | `/reset-password/{token}` | | | Target of the reset email. A browser (no JSON expected) with no template configured gets the M12 reset page (`no-store`, `no-referrer`). With `auth.links.password_reset_url` (env `PASSWORD_RESET_URL`) set: 302 to that template with `{token}` and `{email}` URL-encoded (an app link, a custom scheme or the M12 page), `Cache-Control: no-store, private`, `Referrer-Policy: no-referrer`; unset: `{ token, email }` (M-A5) |
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

**Currency.** `X-Currency: EGP` sets the display currency of the catalog, variants, cart, validate, payment options and place-order, and overrides the `currency` cookie. It must be a three-letter code of an active currency, else 422 `validation_failed` with `errors.currency = ["The X-Currency header must be the code of an active currency."]`. An active currency without a usable rate falls back to the base currency (see `meta.display_currency`). Without `X-Currency` the display currency follows the customer's country (OD-1, section 1g).

**Guest order token.** Replaces the browser session's order list and guest proofs.

| Issued by | `data.guest_order_token` | Grants |
|---|---|---|
| `POST /mobile/checkout/place-order` by a guest (only while `storefront.guest_checkout` is on; off by default since OD-1, section 1g), 201 and replay (not `meta.duplicate: true`, not for a signed-in customer) | `{ token, order_public_id, expires_at }` | status, verify-payment, paypal/capture, retry-payment of that order |
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
    "features": { "checkout_enabled": true, "guest_checkout_enabled": false, "wallet_enabled": true, "reviews_enabled": true, "support_enabled": true, "web_checkout_redirect": false },
    "locales": { "supported": ["en", "ar"], "default": "en" },
    "currencies": { "base": "USD", "supported": [{ "code": "USD", "name": "US Dollar", "symbol": "$", "exponent": 2 }] },
    "media": { "base_url": "https://boxcards.shop/storage" },
    "links": { "support": "https://...", "terms": "https://...", "privacy": null, "refund": null, "web_checkout": "https://boxcards.shop/checkout", "forgot_password": "https://boxcards.shop/forgot-password" },
    "payment_gateways": { "stripe": { "publishable_key": "pk_live_..." }, "paypal": { "client_id": "A..." }, "wallet": {} }
  }
}
```

- `update.required`: the installed version is **strictly below** `min_supported_version` (force update); `update.available`: strictly below `latest_version`. Comparison is SemVer precedence (`1.10.0 > 1.9.0`, `1.4.0-rc.1 < 1.4.0`, build metadata ignored). The app should compare the same way if it does not send its version.
- `maintenance.message` is in the request language (custom text per locale, else a translated default), null when maintenance is off.
- `features` are advisory for the app (what to offer); they do not disable server routes. Two exceptions tie a flag to the server (OD-1): `guest_checkout_enabled` is true only while the server also accepts guest place-order (`storefront.guest_checkout`, default off, owner decision 4), and `web_checkout_redirect` (default false, owner decision 3) tells the app to show "Complete purchase on boxcards.shop" and open `links.web_checkout` (default `APP_URL/checkout`, settable to the cart or another site path) in the system browser instead of its own checkout.
- `payment_gateways`: one entry per **active** gateway, keyed by gateway key, holding only its public identifier (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`; the wallet has none). The value is null when not configured, or when it does not match the gateway's mode (a `pk_live_` key on a sandbox gateway). The app treats Stripe as unavailable when `publishable_key` is null or `stripe` is absent.
- `links.forgot_password` (M-A10; default `APP_URL/forgot-password`, setting `forgot_password_url`, env `MOBILE_FORGOT_PASSWORD_URL`): the web page the app opens in the system browser for "Forgot password?". There is no stateless forgot-password endpoint (`POST /forgot-password` is a session route), so the app never posts the email itself.
- `media.base_url` is the prefix of media URLs (the CDN setting `storage.media_base_url` when set, else the public disk). Every storefront image is already returned as an absolute URL.

Sources and how to change them without a deploy: settings group `app` (keys `android_min_version`, `android_latest_version`, `ios_min_version`, `ios_latest_version`, `maintenance`, `maintenance_message` (JSON `{ "en": "...", "ar": "..." }`), `checkout_enabled`, `guest_checkout_enabled`, `wallet_enabled`, `reviews_enabled`, `support_enabled`, `web_checkout_redirect`, `support_url`, `terms_url`, `privacy_url`, `refund_url`, `web_checkout_url`, `forgot_password_url`) through `php artisan mobile:app-config set <key> <value>` (validated: SemVer, booleans, http(s) URL or site path); or Admin > Settings > Mobile app; built-in defaults in `config/mobile.php` `app` (no `.env` keys since 2026-11-12; `settings:import-env` copies former `MOBILE_*` values once). Public payment identifiers: `php artisan payments:public-key <gateway> <value> --mode=sandbox|production` (or `--clear`), stored in that mode's encrypted gateway credentials. Clients see a change within the 60 s cache.

---

## 1g. Owner decisions OD-1 (built)

Design: `PROJECT_ARCHITECTURE.md` ADR-040 (biometric re-auth) and ADR-041 (display and payment currency).

**Display currency (owner decision 1).** Every price-bearing response (Blade pages, `/api/v1`, `/api/v1/mobile`) uses one resolver, `DisplayCurrencyResolver`, decided once per request:

1. explicit choice, always wins: `X-Currency` (422 unless an active currency), else the `currency` cookie (the web switcher, `POST /api/v1/storefront/currency`), else the signed-in customer's saved `users.display_currency` (`PATCH /account/profile`);
2. otherwise the customer's country: the signed-in customer's `users.country_code`, else the GeoIP country (`GeoIpResolver`, `GEOIP_DRIVER=header` with `CF-IPCountry` behind Cloudflare). Its `countries.default_currency` is used only if active with a fresh rate;
3. otherwise the base currency (USD).

No redirect; the country never appears in a URL. The public, cached storefront reads (`/storefront/home`, `/catalog/products/{slug}`) are anonymous, so there step 2 uses GeoIP only and, with the header driver, `Vary` also names the GeoIP header (`Vary: Accept-Language, X-Currency, Cookie, CF-IPCountry`). A signed-in app that wants its profile currency on those reads sends it as `X-Currency`; cart, checkout and account calls with a bearer token use the profile directly.

**Payment currency (owner decision 1).** Fixed per gateway, as data: `payment_gateways.default_currency` (seeded Paymob `EGP`; Stripe, PayPal, wallet `USD`). It no longer follows the display currency, and the former Paymob `settings.charge_display_currency_only` is removed (migration `2026_11_15_010001`), so Paymob is offered for any display currency, still only under its country and IP rules. The merchant currency row and the adapter must still support it; PayPal's refusal of EGP, SAR and AED stays and no longer matters because PayPal charges USD. `payment-options` and place-order are unchanged in shape: `display_amount` / `display_currency` and the exact `payment_amount` / `payment_currency`; the conversion runs once with the checkout rate snapshot, inside the margin floor (`payment_currency_below_margin`), and `price_changed` still compares the displayed total.

**Guest checkout in the app (owner decision 4).** `POST /api/v1/mobile/checkout/place-order` without a bearer token returns 401 `guest_checkout_disabled` while `storefront.guest_checkout` (`STOREFRONT_GUEST_CHECKOUT`, default off) is off, exactly like the web route. Existing guest orders stay reachable with `X-Guest-Order-Token`, OTP and the signed delivery link.

**Device integrity on reveal (owner decision 5).** `POST /api/v1/mobile/account/orders/{order}/units/{unit}/reveal` and the guest `/mobile/checkout/guest/.../reveal` accept `X-Device-Integrity: ok|compromised|unknown`. The value is stored in `digital_code_accesses.device_integrity` and in the audit `after.device_integrity` of every attempt, granted or denied; any other value is stored as `unknown`; absent is null. It never changes the outcome. It is the app's own verdict and can be forged: a risk signal only.

**Biometric re-auth (owner decision 6).** All behind `mobile.customer` and the `mobile-biometric` limiter (10 per minute per customer, 60 per IP).

| Method | Path | Body | Result |
|---|---|---|---|
| POST | `/api/v1/mobile/auth/biometric-key` | `public_key` (base64 or base64url: DER SubjectPublicKeyInfo, or the 65-byte uncompressed point), `algorithm?` (`ES256`) | 201 `{ biometric_key: { id, algorithm, created_at } }`. ECDSA P-256 only (else 422 `validation_failed`). Needs a recent **password** confirmation on this token (else 423 `recent_auth_required`; a biometric confirmation does not count). Replaces this device's previous key. Audited `auth.biometric_key_registered`, security event |
| DELETE | `/api/v1/mobile/auth/biometric-key` | | 200. Turns biometric unlock off on this device. Audited `auth.biometric_key_revoked` |
| POST | `/api/v1/mobile/auth/biometric-challenge` | | 200 `{ challenge: { challenge, algorithm: "ES256", expires_at } }`, `Cache-Control: no-store`. 404 `biometric_not_registered` when this device has no live key. Lives `mobile.biometric.challenge_ttl_seconds` (60) |
| POST | `/api/v1/mobile/auth/confirm-biometric` | `challenge` (exactly as issued), `signature` (base64 DER ECDSA signature over the UTF-8 bytes of `challenge`, SHA-256) | 200 `{ confirmed_for_minutes }`, as confirm-password: sets `password_confirmed_at` on the current access token. 422 `biometric_challenge_invalid` (unknown, used, expired, another token's or device's challenge), 422 `biometric_failed` (bad signature), 423 `biometric_locked` (5th consecutive bad signature: the key is revoked; confirm the password). Audited `auth.biometric_confirmed` / `auth.biometric_failed` |

A key belongs to one sign-in (refresh-token family) and stops working when that device is signed out or revoked, when the password is changed or reset, or after five bad signatures. Each challenge is single use and bound to the key and the access token that asked for it. The app keeps the private key in the Android Keystore or iOS Secure Enclave, usable only after a biometric prompt and invalidated when biometrics change (`setInvalidatedByBiometricEnrollment` / `.biometryCurrentSet`), so a changed enrolment falls back to the password.

---

## 1h. Push notifications (built, M-A18, owner decision 9)

Design: `PROJECT_ARCHITECTURE.md` ADR-042. Closes gap G6 (push part). Firebase Cloud Messaging (FCM) for Android and, through APNs, iOS.

**Device token.** Behind `mobile.customer` and the `mobile-push` limiter (10 per minute per customer, 60 per IP).

| Method | Path | Body | Result |
|---|---|---|---|
| POST | `/api/v1/mobile/push-token` | `token` (the FCM registration token, 20 to 4096 characters of `A-Z a-z 0-9 _ : - .`), `platform` (`android` or `ios`) | 201 `{ push_token: { platform, registered_at } }` for a new or replaced token, 200 when this device already has exactly this token. The token is never echoed. One token per device: it belongs to the calling sign-in (refresh-token family) and replaces that sign-in's previous token (a refreshed FCM token). The same FCM token registered by another sign-in (another account on the same phone) is moved here. 403 `push_not_available` for a customer token that is not an app sign-in; 422 `validation_failed`. Audited `account.push_token_registered` (platform, family, replaced; never the token) |
| DELETE | `/api/v1/mobile/push-token` | | 200. Removes this device's token (idempotent). Audited `account.push_token_removed` when one was removed |

The token is also deleted when its sign-in ends: logout, sign out everywhere, device revocation (`DELETE /account/devices/{id}`, also by staff), refresh-token reuse, a password change (every other device) or reset (every device), and by `auth:prune-mobile-tokens` once the sign-in has expired. A push is only ever sent to a token whose sign-in is live. Tokens are stored encrypted (`mobile_push_tokens`).

**What is pushed.** For a signed-in customer, while the `push` preference of the category is on (on by default; `GET/PATCH /account/notification-preferences`, 4c):

| Event | `type` | Category | Target |
|---|---|---|---|
| Order delivered (completed or partially fulfilled) | `order_delivered`, `order_partially_delivered` | orders | order |
| Payment failed: a verified provider failure leaving the order unpaid with no open attempt (not a cancellation by the customer) | `payment_failed` | orders | order |
| Staff reply on a support ticket | `support_ticket_replied` | support | ticket |

Each push is the twin of its inbox entry: `notification.title` and `notification.body` are the inbox title and body in the customer's language (`users.locale`), and `data` (strings only) is `{ type, category, link_type: "order" | "ticket", link_value: public_id, order_id | ticket_id: public_id }`. Never a code, PIN, redemption URL, message body, amount or provider message. Android notifications use the channel `updates` (`config/push.php` `android_channel_id`) and a tag per order or ticket, so a newer push replaces the older one of the same target.

**Sending.** Queued per device (`SendPushMessage` on the `notifications` queue, 5 tries, backoff 30 s, 2 min, 10 min, 30 min, or FCM's `Retry-After`) to the FCM HTTP v1 API (`POST https://fcm.googleapis.com/v1/projects/{FCM_PROJECT_ID}/messages:send`) with an OAuth2 access token from the service-account JWT flow. `UNREGISTERED`, an invalid registration token (400 `INVALID_ARGUMENT` about the token) and `SENDER_ID_MISMATCH` delete the token; 429, 5xx, 401 (the cached access token is dropped) and network failures retry; other refusals are logged and dropped. With `FCM_ENABLED` off (the default) every send is a no-op logged at debug level (`push.disabled`), and missing credentials fail the job without a retry (`push.not_configured`). Configuration and owner steps: `INTEGRATIONS.md` (FCM).

**Ticket email link.** The "support replied" email links to `/account/tickets/{ticket}`; `/account/tickets/*` and its `/en` and `/ar` variants are now app links (Android intent filter and `apple-app-site-association`, 1e), and the app opens the ticket thread.

## 1e. Payment returns for the app (built, M-A6)

Design: `PROJECT_ARCHITECTURE.md` ADR-039. Closes gap G8.

**`client` on place-order and retry-payment.** Optional `client`: `web` or `app` (anything else, including a URL, is 422 `validation_failed` on `errors.client`). Without it, the session routes (`/api/v1/checkout/...`) mean `web` and the stateless routes (`/api/v1/mobile/checkout/...`) mean `app`. It is stored on the attempt (`payments.client`). A retry uses the `client` it is sent with, else the default of its route group, so a retry from the app keeps `app`. `client` is part of the place-order body fingerprint (send the same body with the same `Idempotency-Key`). No return, cancel or redirect URL is read from any request: unknown fields such as `return_url` are ignored.

**Return URLs, built by the server from configuration only** (`config/payments.php`):

| Channel | Return | Cancel (PayPal) |
|---|---|---|
| `web` | `PAYMENTS_RETURN_URL` (default `/checkout/orders/{order}/return`, the M12 page) | `PAYMENTS_CANCEL_URL`, else the return page |
| `app` | `{APP_URL}/app/payments/return/{order_public_id}?attempt={payment_public_id}` (`PAYMENTS_APP_RETURN_URL`, path only) | `{APP_URL}/app/payments/cancel/{order_public_id}?attempt={payment_public_id}` (`PAYMENTS_APP_CANCEL_URL`, path only) |

Per provider: PayPal receives both as `payment_source.paypal.experience_context.return_url` and `cancel_url`; Paymob receives the return URL as `redirection_url`; Stripe receives neither (the PaymentIntent is confirmed by the client SDK), and for `app` attempts `next.return_url` carries the value the app must pass to PaymentSheet as `returnURL` (`PAYMENTS_STRIPE_APP_RETURN_URL`, the app's custom scheme, when set; else the app return URL above). The web `next` is unchanged (no `return_url`).

**App flow.** Open `approve_url` / `redirect_url` in an authentication session whose callback is the app link; on return (or cancel), ignore every query parameter (Paymob's `success`, `txn_response_code` and so on) and call `paypal/capture` (PayPal) or `verify-payment`, then poll `status`, with the stored guest order token or bearer token. The `attempt` parameter only lets the app match the return to the attempt it started.

**`GET /app/payments/return/{order_public_id}` and `GET /app/payments/cancel/{order_public_id}`** (web paths, not JSON; no session, cookies or CSRF; `storefront` limiter). When the app is installed and the link is verified, the operating system opens the app and the server is not called. In a browser: 302 to the web return page (or the web cancel page) of that order with no query string, `Cache-Control: no-store, private`, `Referrer-Policy: no-referrer`; 404 for an unknown order or an id that is not a ULID. They never verify, capture, change or mark anything paid and never read a query parameter; the web return page asks the server to verify, as for any browser return.

**`GET /.well-known/assetlinks.json`** and **`GET /.well-known/apple-app-site-association`**: plain JSON (no envelope), `Content-Type: application/json`, 200 with no redirect, `Cache-Control: public, max-age=3600`, ETag, no cookies. From `config/mobile.php` `app_links` (`DEPLOYMENT.md` 3):
- assetlinks: one statement per package in `MOBILE_ANDROID_PACKAGES` (default `shop.boxcards.app`; add `shop.boxcards.app.staging`, `shop.boxcards.app.dev` for the flavors), each with relation `delegate_permission/common.handle_all_urls` and every fingerprint in `MOBILE_ANDROID_SHA256_FINGERPRINTS`. **With no fingerprint configured the file is `[]`**: no app is verified, and app links open in the browser (the return route then redirects to the web page). Malformed packages or fingerprints are left out.
- apple-app-site-association: `{"applinks":{"details":[{"appIDs":["TEAMID.shop.boxcards.app"],"components":[{"/":"/app/*"},{"/":"/products/*"},{"/":"/en/products/*"},{"/":"/ar/products/*"},{"/":"/account/orders/*"},{"/":"/en/account/orders/*"},{"/":"/ar/account/orders/*"},{"/":"/account/tickets/*"},{"/":"/en/account/tickets/*"},{"/":"/ar/account/tickets/*"},{"/":"/api/v1/checkout/guest/orders/*/access"}]}]}}` from `MOBILE_IOS_APP_IDS`; unconfigured: `{"applinks":{"details":[]}}`. The components (`mobile.app_links.paths`) mirror the Android intent filter and the app's link listener (M-A15): payment returns under `/app/` (M-A6), product pages (M-A12), the customer order page and the signed guest access link of the delivery emails (M-A14), and the ticket page of the "support replied" email (M-A18). In a component `*` matches any characters including `/`, and a component without `?` matches any query, so the guest link's `expires` and `signature` pass through. Nothing else on the site opens the app.

## 1f. Contract fixtures (built, M-A7, gap G11)

The Pest suite `backend/tests/Feature/MobileContract` calls every `/api/v1/mobile` route the app uses, with factories, and records the responses as JSON in `docs/api-fixtures/<area>/<name>.json` (156 files, 18 areas: success responses plus validation 422, domain 422, 401, 403, 404, 409, 423 and 429 errors; catalog, product, home and app config also in Arabic). Each file holds `{ request: { method, path }, status, headers, body }`, where `headers` keeps only what the app reads (`Cache-Control`, `Content-Language`, `Idempotency-Replayed`, `Retry-After`, `X-Cart-Token`).

- Normalized for stability, types kept: ULIDs (`01JF...`, case preserved), UUIDs, integer ids, order and ticket numbers, ISO timestamps (`2026-11-10T12:00:00+00:00`), and every token or secret value (`fixture-access-token`, `fixture-token`, `fixture-client-secret`, `fixture-recovery-code-N`, signed-link `expires`/`signature`). Money strings and every other value are exact. Keys are sorted and pretty-printed.
- The reveal fixtures carry the canary code `FIXTURE-CODE-0000` only; `FixtureSafetyContractTest` fails on provider secrets, live or test secret keys, bearer values, TOTP secrets, real-looking codes and money sent as a JSON number.
- A normal run fails on drift (the response differs from the committed file). After an intended contract change: `UPDATE_API_FIXTURES=1 php artisan test --compact tests/Feature/MobileContract`, then `bash application/tool/sync_fixtures.sh` and update the app's DTOs; `application/tool/ci.sh` fails while the app copy (`application/test/fixtures/api`) differs.

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

**Image object** (listing `image`, product `images[]`, platform `logo`/`banner`, category `banner`, banner `image.desktop`/`image.mobile`): `{ path, url, alt, width, height, formats: { webp, avif? } }`. `url` and `formats.*` are absolute: the CDN setting `storage.media_base_url` when configured, otherwise the media disk URL made absolute with the store address (`session.app_url`). Media has format conversions (WebP, AVIF when the server's GD supports it), no resized variants; `width` and `height` are the original's. Platform and category images are the admin's own uploads (null when not set); the API adds no logos, and BD8 (no official brand logos until rights are confirmed) governs what admins upload.

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
| POST | `/place-order` | `{ email, country, gateway, method?, consents, ga_client_id?, client? }`, `Idempotency-Key` | `{ order_public_id, status, next: { type, client_secret?, approve_url?, redirect_url?, return_url? } }` |
| POST | `/orders/{public_id}/paypal/capture` | | Server capture; returns order status |
| POST | `/orders/{public_id}/retry-payment` | `{ gateway, method?, client? }` | New attempt |
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
| POST | `/checkout/place-order` | `email` (guests only; customers use their account email), `country`, `gateway`, `method?`, `currency` and `expected_total` (exactly what the customer was shown), `consents.terms` (must be accepted), `consents.marketing?`, `ga_client_id?`, `client?` (`web` or `app`, section 1e); header `Idempotency-Key` (8 to 128 of `A-Za-z0-9_-:.`) | `checkout` | 201 `{ order: { public_id, order_number, status, display_total, payment_total, expires_at }, next }` (`next`: section 3b, or null), `meta.duplicate: false`. 200 `{ order: { public_id, status } }`, `meta.duplicate: true` when the same buyer already has an open order with the same contents. A replay carries `Idempotency-Replayed: true`. Errors as for validate, plus `price_changed`, `gateway_not_eligible`, `order_total_invalid`, the idempotency 409s. `gateway_not_eligible` whenever the chosen gateway fails eligibility (M10, default deny). M12 (owner decision 4): a browser guest (session, not signed in) gets 401 `guest_checkout_disabled` while `storefront.guest_checkout` (env `STOREFRONT_GUEST_CHECKOUT`, default off) is off; the stateless `/api/v1/mobile` twin is not affected. |
| GET | `/checkout/orders/{public_id}/status` | | `checkout` | `{ public_id, status, payment_status, is_final }`. Visible to the owning customer, or to the browser session that placed or verified a guest order; 404 otherwise. |
| POST | `/checkout/guest/otp/send` | `order_public_id`, `email` | `otp` | Always 200 with the same message. Emails a 6-digit code only when the email matches the guest order; at most 5 codes per order per hour; a new code replaces the old. |
| POST | `/checkout/guest/otp/verify` | `order_public_id`, `code` | `otp` | `{ order_public_id, email_verified: true }`, sets `orders.guest_email_verified_at` and opens the order to the session; or 422 `otp_invalid` (wrong, expired after 10 minutes, or 5 attempts used) |

`cart` shape: `{ public_id, currency, country, items: [{ id, variant: { public_id, title, sku }, product: { name, slug, delivery_type }, quantity, delivery_values, purchasable, block_reason, unit_price, subtotal, total }], coupon: { code, applied, error, limited_by_margin } | null, totals: { subtotal, discount, total }, price_may_vary, checkout_ready }`. `block_reason` is one of `unavailable`, `out_of_stock`, `custom_amount_unsupported`, `quantity_limit`, `not_sold_in_country`, or a pricing reason (`rate_stale`, `rate_missing`, `below_margin`, `cost_unavailable`, ...). Cost, floor and margin are never returned.

Not built in M7: a customer cancel endpoint (the state machine allows it). M10 changes to place-order: the gateway is checked by the real eligibility resolver (default deny), `orders.ip_country` comes from the GeoIP resolver, and on a 201 the first payment attempt is created at the provider after the order commits; its session is returned as `next` (section 3b). A replay returns the stored response, whose `next` is null, as it is when the first attempt could not be started; the client then calls retry-payment.

### 3b. Built payment routes (M10)

Same group, middleware and `checkout` limiter as 3a. The order routes are visible to the owning customer or the browser session that placed or verified a guest order; 404 otherwise. None of them can mark an order paid by itself: only a verified webhook or a server-side provider read whose amount and currency match the attempt does (architecture 9.1).

| Method | Path | Body | Result |
|---|---|---|---|
| GET | `/checkout/payment-options` | `country`, `email?` | `{ options: [{ gateway, label, methods: [{ code, label }], available, payment_currency, payment_amount, display_currency, display_amount, notice }] }`. Only eligible gateways are listed. `notice`: null, `charged_in_payment_currency` (the gateway charges another currency than the display currency: since OD-1 every gateway charges one fixed currency, Paymob EGP and Stripe, PayPal and the wallet USD, so for example Paymob for a USD shopper or Stripe for an EGP shopper; the page shows "You will be charged ..."), or `payment_currency_below_margin` with `available: false`. Errors as for validate. Advisory: place-order re-checks everything. |
| POST | `/checkout/orders/{public_id}/retry-payment` | `gateway?`, `method?`, `client?` (section 1e) | 201 `{ next }`. A new attempt on the order's gateway with the frozen snapshot. 409 `order_not_payable` (not `pending_payment`, or already paid), `order_expired`, `payment_attempts_exhausted` (`orders.max_payment_attempts`, default 5); 422 `gateway_change_unsupported` (another gateway: place the order again), `gateway_not_eligible`; 503 `gateway_unavailable` when the provider could not create it. |
| POST | `/checkout/orders/{public_id}/verify-payment` | | `{ public_id, status, payment_status, is_final }` after a server-side read of the latest attempt. For the return page. |
| POST | `/checkout/orders/{public_id}/paypal/capture` | | Same shape. Server capture of an approved PayPal order (`PayPal-Request-Id: pay_capture:{payment}`); paid only on capture `COMPLETED`. 422 `capability_not_supported` for other gateways. |

`next` shapes: Wallet (M11) `{ payment_public_id, gateway: "wallet", type: "wallet" }`, returned when the order is already paid from the wallet balance (no redirect, no provider); Stripe `{ payment_public_id, gateway, type: "stripe", client_secret, return_url? }` (`return_url` for `app` attempts only, section 1e); PayPal `{ ..., type: "paypal", provider_order_id, approve_url }`; Paymob `{ ..., type: "redirect", redirect_url }` (Unified Checkout with the public key and the intention's client secret). Publishable values only.

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
| GET, DELETE | `/devices`, `/devices/{id}` | built (4c, M-A5; replaces the planned `/sessions`); POST `/devices/sign-out-others` built (4d, M13) |
| GET, POST | `/notifications`, `/notifications/unread-count`, `/notifications/{id}/read`, `/notifications/read-all` | built (4c, M-A5) |
| GET, PATCH | `/notification-preferences` | built (4c, M-A5) |
| POST | `/email/verification-notification` | built (4c, M-A5; the web route `POST /email/verification-notification` stays) |
| GET | `/wallet`, `/wallet/transactions` | built (4b) |
| GET, POST | `/tickets`; POST `/tickets/{public_id}/messages` | built (4b) |
| GET, POST | `/reviews` | built (4b); PATCH `/reviews/{id}` built (4d, M13) |
| GET, POST | `/refund-requests` | built (4b) |
| POST, DELETE | `/security/two-factor` | built: POST starts enrolment and DELETE disables, both need `current_password`; DELETE is 403 for staff |
| POST | `/security/two-factor/confirm` | built: `code`; returns recovery codes once |
| GET | `/digital-codes` (masked only) | not built as JSON; the web page `/account/codes` (M13) reads the repository directly, and the app uses the order detail |
| GET, POST, DELETE | `/wishlist`, `/wishlist/{slug}` | built (4d, M13) |

Reveal response: `{ code, pin, redemption_url, instructions }`, `Cache-Control: no-store`.

### 4a. Built code reveal routes (M9)

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| POST | `/api/v1/account/confirm-password` | `password` | `auth` | Stores `auth.password_confirmed_at` in the session (on the access token for `/api/v1/mobile/account`, section 1c); reveals need it within `fulfillment.reveal_recent_auth_minutes` (15) |
| POST | `/api/v1/account/orders/{order_public_id}/units/{unit_public_id}/reveal` | | `code-reveal` | Reveal body above, `Cache-Control: no-store, private`. Owner only (others 404), verified email, recent password (else 423 `recent_auth_required`), paid order and delivered unit (else 403 `reveal_denied`). Sets `first_revealed_at` |
| GET | `/api/v1/checkout/guest/orders/{order_public_id}/access` | signed URL from the delivery email, valid 72 h | `otp` | Opens the order to this browser session; `{ order_public_id, otp_required }`. A browser (no JSON expected) is redirected to the M12 guest order page `/orders/{order_public_id}` (code form, then the order). Unsigned or expired: 403 |
| POST | `/api/v1/checkout/guest/orders/{order_public_id}/units/{unit_public_id}/reveal` | | `code-reveal` | As above for a guest: the session must hold both the signed link and a verified email one-time code (`POST /checkout/guest/otp/verify`) for this order |

Every attempt, granted or denied, is written to `audit_logs` (`digital_codes.revealed` / `digital_codes.reveal_denied`) and, where a code exists, to `digital_code_accesses`, with the app's `X-Device-Integrity` verdict on the `/api/v1/mobile` routes (section 1g). Delivery emails link here and never contain a code.

### 4b. Built customer routes (M11)

Base `/api/v1/account` (session, active account), `account` limiter. Lists take `page`, `per_page` (max 50). Other customers' records are 404.

| Method | Path | Body | Result |
|---|---|---|---|
| GET | `/wallet` | | `{ wallet: { currency, balance: money, status, top_up_available: false } }`. Store credit from refunds and staff credits only (BD3); there is no top-up route. |
| GET | `/wallet/transactions` | `page`, `per_page` | Statement, newest first, paginated (`meta`): `[{ id, type, amount (signed), balance_after, note, order, created_at }]`. `type`: `credit_refund`, `credit_admin`, `debit_purchase`, `debit_admin`, `reversal`. `amount` and `balance_after` are decimal strings (6 dp) in the wallet currency (`GET /wallet` `currency`). `order` (M-A15): `{ public_id, order_number }` of the customer's own order behind a purchase debit or a refund credit, else null (staff adjustments; never another customer's order). `note` is the server's text (staff reason or a system note, not translated). |
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

**Notifications (inbox; push since M-A18, 1h)**

| Method | Path | Query / body | Result |
|---|---|---|---|
| GET | `/notifications` | `unread` (boolean) | Newest first: `[{ id, type, category, title, body, link, read_at, created_at }]`, `meta.unread_count` |
| GET | `/notifications/unread-count` | | `{ unread_count }` |
| POST | `/notifications/{id}/read` | | `{ notification }` (idempotent). 404 for another customer's notification |
| POST | `/notifications/read-all` | | `{ marked_read, unread_count: 0 }` |
| GET | `/notification-preferences` | | `{ preferences: [{ category, channel, enabled, mandatory }] }`, 14 cells in a fixed order: categories `orders`, `support`, `marketing`, `security` x channels `mail`, `database` (the inbox), `sms`, plus `push` (M-A18) for `orders` and `support` only |
| PATCH | `/notification-preferences` | `preferences: [{ category, channel, enabled }]` (1 to 16) | The full matrix. 422 `notification_preference_mandatory` (`errors.preferences.N.enabled`) when a mandatory cell is switched off; 422 `notification_preference_unavailable` (`errors.preferences.N.channel`) for `push` with `marketing` or `security`; nothing of that request is saved. Audited |

- `type`: `order_delivered`, `order_partially_delivered`, `order_fulfilment_failed`, `support_ticket_replied`, `payment_failed` (M-A18: inbox and push only, no email). `title` and `body` are rendered in the request language from the stored kind and number. `link`: `{ type: "order" \| "ticket", value: public_id, url }` (the ADR-037 shape; `url` is the web account page the emails link to).
- Payloads hold identifiers and the order or ticket number only: never a code, PIN, redemption URL or message body.
- Defaults without a saved row: `mail` and `database` on for orders, support and security; `push` on for orders and support (M-A18); `sms` off (opt-in, ADR-034); marketing follows the latest marketing consent of the channel (email marketing for mail and the inbox, SMS marketing for SMS). Mandatory (always on, cannot be disabled): `orders.mail` (delivery link and failure notice, ADR-009) and `security.mail` (password reset and verification are always sent).
- The customer notifications `OrderDelivered`, `OrderFulfilmentFailed` and `SupportTicketReplied` follow these preferences for `mail` and `database` (M-A5); `OrderDelivered`, `SupportTicketReplied` and `PaymentFailed` also for `push` (M-A18, 1h); guests get email only. `sms` remains the n8n channel read by `CustomerContactPolicy`.

### 4d. Built account routes (M13)

Base `/api/v1/account` and `/api/v1/mobile/account` (routes/account.php). Added for the web account pages; the pages themselves are Blade (`/account/...`, section "Web pages" in `PROGRESS.md` M13) and send every change through these JSON routes with CSRF.

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| PATCH | `/reviews/{id}` | `rating` 1 to 5, `title?`, `body?` | `account` | `{ review }` with `status: pending`: the edit goes back to moderation, `is_featured` is cleared, and an approved review leaves the product rating until approved again. Tags are stripped. Audited `reviews.review.revised`. 404 for an unknown id or another customer's review |
| POST | `/devices/sign-out-others` | `current_password` | `auth` | `{ signed_out: { mobile_families, web_sessions } }`: every other app sign-in (`logout_all`) and browser session (database driver) is signed out and the remember-me token is rotated; the caller stays signed in; the password does not change. Audited `account.signed_out_others` |
| GET | `/wishlist` | | `account` | `{ items: [product card] }` (the storefront listing card, priced in the display currency), newest first, at most 200; saved products that are no longer listed are not shown |
| POST | `/wishlist` | `product_slug` | `account` | 201 `{ product_slug, saved: true }`; idempotent. 404 for an unknown or unlisted product |
| DELETE | `/wishlist/{slug}` | | `account` | `{ product_slug, saved: false }`; idempotent |

Web-only (session, `storefront` group): `GET /account/security/two-factor/qr` returns the QR code of a pending two-factor enrolment as `image/svg+xml` with `Cache-Control: no-store, private` (404 when none is pending), drawn by the in-house `QrCodeSvg` encoder, so the secret never appears in a page or script.

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

Undelivered units are refunded automatically by the system (BD4 default); there is no route for that. Gateway status, mode and write-only credential routes were built in M14 (5e).

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

### 5e. Built admin core routes (M14)

Admin pages (Blade, `routes/admin_pages.php`, prefix `/admin`, names `admin.*`) only read; every change below is JSON under `/admin/api` in the `admin` group (session, CSRF, active staff, two-step verification passed, idle timeout, `throttle:admin`, `Accept-Language`) with the standard envelope. Validation errors are 422 `validation_failed` with dot keys (`filters.status`, `credentials.secret_key`), shown beside the fields. Every mutation is written to `audit_logs`.

**Pages** (each checks its permission; 403 otherwise; guests go to `/admin/login`, staff without two-step verification to `/admin/two-factor/setup`): `GET /admin/login`, `/admin/two-factor`, `/admin/two-factor/setup` (+ `/qr`, SVG, `no-store`), `POST /admin/locale { locale: en|ar }`, `GET /admin` (first permitted screen), `/admin/dashboard?days=7|30|90` (`dashboard.view`), `/admin/orders` and `/admin/orders/{public_id}` (`orders.view`), `/admin/suppliers` and `/admin/suppliers/{id}` (`suppliers.view`), `/admin/payments` and `/admin/payments/gateways/{id}`, `/transactions`, `/disputes`, `/reconciliation` (`payments.view`), `/admin/payments/webhooks` (`payments.webhook_logs`), `/admin/payments/refunds` (`refunds.view`).

**Tables.** One set of routes per table under `/admin/api/tables/{table}`, registered from `App\DataTables\AdminTables` with the table's permission. The table comes from the route, never from input.

| Table | Permission | Filters (`filters[...]`) | Search |
|---|---|---|---|
| `orders` | `orders.view` | `status`, `attention=1`, `gateway` (id), `from`, `to` (Y-m-d) | order number, customer name; email only with `customers.view_pii` (otherwise masked in rows) |
| `suppliers` | `suppliers.view` | `status`, `health` | name, adapter key |
| `supplier-logs` | `suppliers.logs` | `supplier` (required context), `outcome` (`ok`, `failed`) | operation |
| `supplier-mappings` | `suppliers.view` or `mappings.manage` | `supplier` (required context), `active` | none |
| `payments` | `payments.view` | `gateway`, `status`, `from`, `to` | provider payment or order id, public id |
| `webhooks` | `payments.webhook_logs` | `gateway`, `status`, `signature` (`valid`, `invalid`) | event type, provider event id |
| `refunds` | `refunds.view` | `status`, `reason` | public id, provider refund id |
| `disputes` | `payments.view` | `status` | provider dispute id |
| `reconciliation` | `payments.view` | `status`, `type` | provider reference |

| Route | Body and answer |
|---|---|
| `POST tables/{table}` | DataTables server-side request: `draw`, `start`, `length` (10, 25, 50 or 100), `search.value` (at most 100 characters, literal prefix match), `order[]` (at most 3 `{ column, dir }`, by position in the table's declared columns), `filters`. Column definitions, column search and regex sent by the client are ignored. Answer (DataTables shape, not the envelope): `draw`, `recordsTotal` (the table's scope), `recordsFiltered`, `data[]` with only the declared column keys and `DT_RowId`; text is escaped, badge and link cells are server-built HTML from escaped values. A filter value outside its options is 422 on `filters.{key}` |
| `PUT tables/{table}/state` | `{ hidden_columns: [keys], page_length }` (unknown keys dropped). The staff member's own layout (`admin_table_states`) |
| `POST tables/{table}/saved-filters` | `{ name, is_default?, payload: { search?, filters?, order?, hidden_columns? } }`, 201. Replaces a filter of the same name; filter values validated; at most `admin.tables.saved_filters_per_table` (20) per table (422 on `name`) |
| `DELETE tables/{table}/saved-filters/{id}` | Own filters only; another staff member's is 404 |
| `POST tables/{table}/exports` | `{ format: csv\|xlsx, search?, filters?, order?, hidden_columns?, ids?: [row ids, at most 500] }`, 10 per minute, 202 `{ id, status, rows, download_url, expires_at }`. Recorded in `export_jobs`, audited (`admin.export_requested`: table, format, filters, whether searched, selected count) and written by the queued `ExportAdminTable` job to the private disk: UTF-8 CSV with BOM or XLSX (in-house writer), formula cells neutralised, at most `admin.tables.export_max_rows` (50 000) rows |
| `GET tables/{table}/exports/{id}` | Status of the caller's own export (others 404) |
| `GET tables/{table}/exports/{id}/download` | The file (`no-store`), for the requester only, while they hold the table permission and before `expires_at` (24 h; 410 `export_expired`; 409 `export_not_ready`). Audited `admin.export_downloaded` |

**Actions.**

| Route | Body and answer | Permission |
|---|---|---|
| `POST orders/{public_id}/reject` | `{ reason (5 to 1000), refund_reason?: fraud\|admin\|customer_request (default fraud) }`. Only in `manual_review`; 409 `supplier_attempt_open` while a unit has an attempt with the supplier. Unpaid: undelivered units cancelled, order `cancelled`. Paid: undelivered units failed and refunded to the original method through 5c (order `refund_pending`), which also needs `refunds.create` (403 otherwise, nothing changed). Audited `orders.review_rejected` | `orders.review` |
| `POST tables/orders/bulk/reconcile` | `{ ids: [order public ids, 1 to 100] }`, 6 per minute: "check status now" for each order. `{ orders, attempts_checked, skipped }`; audited per order (`orders.reconciled`) | `orders.reconcile` |
| `PATCH payment-gateways/{id}` | `{ is_active?, mode?: sandbox\|production }`. Activating needs credentials for the mode (422 on `is_active`). The charge currency (`default_currency`, OD-1) is not editable. Audited `payments.gateway_updated` | `payments.manage` |
| `POST payment-gateways/{id}/credentials` | `{ mode, credentials: { …every field of the adapter }, webhook_secret? }`, 10 per minute. Fields per adapter (`admin.credential_fields.gateways`): Stripe `secret_key`, `publishable_key` (prefixes must match the mode: `sk_test_`/`pk_test_` in sandbox, `sk_live_`/`pk_live_` in production); PayPal `client_id`, `client_secret`, `webhook_id`; Paymob `api_key`, `secret_key`, `public_key`. Replaces the mode's values; extra fields dropped. Answer `{ credentials_configured: { sandbox: { configured, rotated_at }, production: {…} } }`, never a value. Audited `payments.credentials_set` with field names only | `payments.credentials` (E) |
| `PATCH payment-eligibility-rules/{id}` | `{ is_active }`. Audited `payments.eligibility_rule_toggled` (full rule editing: 5f) | `payments.manage` |
| `POST payments/reconcile` | No body, 6 per minute. Runs the five-minute open-payment reconciliation now; `{ payments, expired, refunds, webhooks }`. Audited `payments.reconciled` | `payments.manage` |
| `POST reconciliation-items/{id}/resolve` | `{ status: resolved\|ignored, note (5 to 1000) }`; closed items are skipped. `{ changed }`. Audited per item | `payments.manage` |
| `POST tables/reconciliation/bulk/resolve`, `.../bulk/ignore` | `{ ids: [item ids], note }` | `payments.manage` |

Existing routes used by the M14 pages without change: order `review`, `reconcile`, `retry-verified-unit`, `transactions/{id}/mark-not-accepted`, `orders/{id}/refunds` (5b, 5c; refunds created by staff are now audited `refunds.created`), and supplier `PATCH`, `credentials`, `test-connection`, `sync-catalog`, `sync-pricing` (5b). The sign-in pages post to `/login`, `/two-factor-challenge` and `/api/v1/account/security/two-factor[/confirm]` (1a, 4a).

### 5f. Built admin screens and routes (M15)

Same conventions as 5e: pages only read; every change is JSON under `/admin/api` (session, CSRF, staff, two-step verification passed, `throttle:admin`), checked by its own permission (403 `forbidden` before validation), validated by a Form Request (422 `validation_failed`, dot keys shown beside the fields) and audited. Secrets are write-only: no page, table or response contains a stored secret value (canary tests). Contact details are masked without `customers.view_pii`.

**Navigation.** Sections with several screens have tabs (`config('admin.sections')`); a sidebar entry opens the first tab the staff member may open and is hidden when there is none. New entries: Catalog, Customers, Pricing, Marketing, Content, Support, Reviews, AI and automation, Users and roles, Settings.

**Pages** (all `GET`, 403 without the permission):

| Page | Permission |
|---|---|
| `/admin/catalog/products`, `/create` (needs `catalog.manage`), `/{public_id}` (editor: basics, delivery, categories and regions, descriptions, required delivery fields, variants, status, images, supplier mappings, Arabic text, SEO) | `catalog.view` (read-only without `catalog.manage`) |
| `/admin/catalog/{platforms,categories,regions,attributes,delivery-fields}` (+ `/create`, `/{id}`) | `catalog.view`; writes `catalog.manage` |
| `/admin/catalog/mappings` (all mappings, bulk on/off, bulk mapping) | `mappings.manage` |
| `/admin/pricing/{pricing-rules,variant-prices}` (+ `/create`, `/{id}`) | `pricing.view`; writes `pricing.manage` |
| `/admin/pricing/currencies/{code}`, `/admin/pricing/countries/{code}`, lists | `pricing.view` or `currency.manage`; writes `currency.manage` |
| `/admin/pricing/exchange-rates` (fetch now, held rates, manual override) | `pricing.view` or `currency.manage` |
| `/admin/marketing/{coupons,promotions,affiliates,banners}` (+ `/create`, `/{id}`) | `marketing.coupons`, `.promotions`, `.affiliates`, `.banners` |
| `/admin/marketing/homepage-sections` (+ items) | `content.homepage` |
| `/admin/content/{pages,blog-posts,blog-categories,faqs,faq-groups,menus}` (+ `/create`, `/{id}`) | `content.pages`, `content.blog`, `content.faq`, `content.menus` |
| `/admin/content/redirects` | `seo.redirects` |
| `/admin/content/media` (upload, ids, delete) | `content.media` |
| `/admin/customers`, `/admin/customers/{public_id}` | `customers.view` |
| `/admin/support/tickets`, `/admin/support/tickets/{public_id}` | `support.view` (reply `support.reply`; status, priority, assignee `support.manage`) |
| `/admin/support/refund-requests`, `/{id}` | `support.refund_requests` (approve also `refunds.create`) |
| `/admin/reviews` | `catalog.manage` |
| `/admin/users/staff`, `/admin/users/staff/{public_id}` | `users.manage` |
| `/admin/users/roles` (permission matrix) | `roles.manage` or `users.manage` (editing `roles.manage`) |
| `/admin/settings/{group?}` | `settings.view` (saving: the group's permission) |
| `/admin/automation/ai-suggestions` | `ai_suggestions.review` |
| `/admin/automation/mcp-actions` | `mcp_actions.approve` |
| `/admin/automation/mcp-tokens`, `/admin/automation/credentials` | `service_accounts.manage` |

The declarative list, create and edit screens post to the existing 5a/5d resources (`platforms`, `categories`, `regions`, `attributes`, `delivery-fields`, `pricing-rules`, `variant-prices`, `currencies`, `coupons`, `promotions`, `affiliates`, `banners`, `homepage-sections`, `pages`, `blog-categories`, `blog-posts`, `faq-groups`, `faqs`, `menus`, `menus/{id}/items`, `redirects`, `media`, `products`, `products/{id}/variants`, `products/{id}/images`, `supplier-mappings`, `suppliers`, `reviews/*`, `tickets/*`, `refund-requests/*`, `customers/{id}/wallet/*`, `mcp-tokens`, `mcp-actions/*`, `automation-credentials`) without changes to their contracts.

**New tables** (`POST /admin/api/tables/{table}`, as 5e): `products` (rows keyed by public id; filters `status`, `platform`, `featured`; bulk publish, unpublish, delete by permission), `platforms`, `categories`, `regions`, `attributes`, `delivery-fields`, `mappings` (filters `supplier`, `active`; bulk on/off), `media`, `pricing-rules`, `variant-prices`, `currencies`, `exchange-rates` (row actions for `currency.manage`), `countries`, `coupons`, `promotions`, `affiliates`, `affiliate-commissions` (context `affiliate`; approve action), `banners`, `homepage-sections`, `pages`, `blog-posts`, `blog-categories`, `faqs`, `faq-groups`, `menus`, `redirects`, `customers` (email search only with `customers.view_pii`), `customer-wallet` (context `customer`), `tickets` (filters `status`, `priority`, `category`, `assignee=me|none`), `refund-requests`, `reviews` (row actions), `staff`, `ai-suggestions`, `mcp-actions`, `mcp-tokens`, `automation-credentials`, `automation-workflows`. Each with the permission of its page.

**New routes.**

| Route | Body and answer | Permission |
|---|---|---|
| `PUT translations/{type}/{id}` | `{ locale: ar, values: { field: text\|null } }`. Writes `content_translations` (ADR-015); types and fields from `config('admin.translations')`: `product` (name, short_description, description, instructions, terms), `product_variant` (title), `gaming_platform`, `category` (name, description), `banner` (title, subtitle, cta_label), `homepage_section` (title, subtitle), `faq` (question, answer), `page` (title, body), `blog_post` (title, excerpt, body), `menu_item` (label). The base locale is refused; empty removes; HTML fields sanitized; catalog records reindexed for search. `{ values: { ar: {…} } }`. Audited `content.translation.updated` (field names only) | the type's edit permission (`catalog.manage`, `marketing.banners`, `content.homepage`, `content.faq`, `content.pages`, `content.blog`, `content.menus`) |
| `PUT seo/{type}/{id}` | `{ locale, title?, description?, canonical_override?, robots?: index,follow\|noindex,follow\|index,nofollow\|noindex,nofollow, og_image_media_id? }` for `product`, `gaming_platform`, `category`, `page`, `blog_post`. All empty removes the row. Audited `seo.metadata.updated` | `seo.manage` |
| `PATCH countries/{code}` | `{ default_currency?, is_active? }`: the OD-1 display currency of a country. Audited `currency.country_currency.updated` | `currency.manage` |
| `POST supplier-mappings/bulk` | `{ supplier_id, rows }`, rows one per line `variant_sku,external_id,price,currency[,role][,priority]` (at most 200, `#` comments). All or nothing; errors on `rows` per line. 201 `{ created }`. Each mapping audited `suppliers.mapping.created` | `mappings.manage` |
| `POST supplier-mappings/bulk-status` | `{ ids: [1..200], is_active }`. `{ changed }`; audited per mapping | `mappings.manage` |
| `POST products/bulk` | (5a) now also behind `catalog.publish` or `catalog.manage` at the route | as before |
| `POST customers/{public_id}/block`, `/unblock` | `{ reason (5 to 500) }`. Block: status `suspended`, signed out everywhere, biometric keys revoked; 409 when already blocked or not blocked. Audited `customers.blocked`, `customers.unblocked` | `customers.manage` |
| `PUT customers/{public_id}/risk` | `{ risk_level: low\|medium\|high\|blocked, risk_notes?, reason }`. Audited `customers.risk_level.changed` | `customers.manage` |
| `POST customers/{public_id}/notes` | `{ body (2 to 5000) }`, 201. Staff-only notes. Audited `customers.note_added` | `customers.manage` |
| `DELETE customers/{public_id}/devices/{id}` | `{ reason }`. Signs out a browser session (`web_…`) or an app sign-in family; 404 for unknown ids. Audited `customers.device_revoked` | `customers.manage` |
| `DELETE customers/{public_id}/biometric-keys/{key}` | `{ reason }`. Revokes one biometric unlock key (OD-1, reason `revoked_by_staff`); another customer's key is 404. Audited `customers.biometric_key_revoked` | `customers.manage` |
| `GET customers/{public_id}/export` | Personal data export as a JSON attachment (`no-store`): profile, consents, preferences, orders with lines, wallet, tickets without internal notes, reviews; never codes, delivery values, provider data or staff notes. 10 per minute. Audited `customers.data_exported`. Console twin `customers:export {public_id}` | `customers.manage` |
| `POST customers/{public_id}/anonymize` | `{ reason (10 to 500), confirm: the public id }`; 409 `wallet_not_empty` while the wallet holds a balance. Replaces identity fields (account and order contact), closes the account, signs out everywhere; financial records kept. Audited `customers.anonymized`. Console twin `customers:anonymize` | `customers.manage` |
| `POST staff` | `{ name, email, roles: [names] }`, 201. Creates a staff account with a random password and sends a reset link; two-step verification is required at first sign-in. Audited `users.staff_invited` | `users.manage` |
| `PUT staff/{public_id}/roles` | `{ roles: [names] }` | `users.manage` |
| `PUT staff/{public_id}/permissions` | `{ permissions: [names] }`: direct grants, the way to grant explicit (E) permissions | `users.manage` |
| `POST staff/{public_id}/status` | `{ status: active\|suspended, reason }` (suspension signs out everywhere) | `users.manage` |
| `POST staff/{public_id}/reset-two-factor` | `{ reason }`: removes two-step verification and signs out; set up again at the next admin page | `users.manage` |
| `POST roles` | `{ name }`, 201 | `roles.manage` |
| `PUT roles/{id}/permissions` | `{ permissions: [names from SECURITY.md 4] }` | `roles.manage` |
| `DELETE roles/{id}` | Custom roles without staff only; seeded roles 403 | `roles.manage` |
| `PUT settings/{group}` | The group's fields (`config/admin_settings.php`): `orders`, `pricing`, `suppliers`, `notifications`, `analytics`, `general`, `mobile` (the public app config: versions with force update, maintenance and message, feature flags including `web_checkout_redirect`, links), `payments` (return templates), `mcp`, `seo` (`block_all_crawlers`), `admin` (`admin_theme`: `dark`\|`light`) need `settings.manage`; `checkout` (`guest_checkout`), `currency`, `security` (incl. `admin_idle_minutes` 5 to 480), `geoip` (`geoip_driver`: `null`\|`header`, `geoip_country_header`), `app_links` (`android_packages`, `android_sha256_fingerprints`, `ios_app_ids`: comma-separated in the `/.well-known` formats; `biometric_challenge_ttl_seconds` 15 to 300), `account_links` (`password_reset_url` with `{token}`, `email_verified_url`), `push` (`push_enabled`, `fcm_project_id`), `mail`, `automation` (`task_drivers`, `outbox_enabled`, `webhook_url`, `automation_store_name`, `automation_token_ttl_days`) need `settings.manage_critical`. Fields may be sent partially. URLs are https (http only while `APP_ENV=local`), without user info; return templates are a path or https URL containing `{order}`, app return templates a path only, `stripe_app_return_url` a custom scheme or https URL. Typed, validated per field (422 on the field key), audited `settings.updated` per changed key with the old and new value. The fields that replaced `.env` keys apply to running code at once (Settings from the database, `DEPLOYMENT.md` 3.2) | route: `settings.manage` or `settings.manage_critical`; then the group's permission (403) |
| `PUT settings/{group}/secrets/{key}` | `{ value \| null }` (JSON), or multipart `file` (at most 20 KB) for an uploadable secret, 10 per minute. Write-only encrypted settings: `currency.oxr_app_id`, `mail.smtp_password`, `analytics.ga4_api_secret`, `analytics.search_console_client_secret`, `automation.outbound_secret`, `automation.webhook_auth_token` (16 to 255 characters), `push.fcm_service_account` (the service-account JSON, uploadable; validated: `type` service_account, `project_id`, `client_email`, a usable `private_key`, https `token_uri`); null clears. Answer `{ configured }`, never the value; audit `{ value: changed \| cleared }` only. 422 `file` when a file is sent for a secret that is not uploadable | `settings.manage_critical` (E) |
| `POST payment-gateways/{id}/eligibility-rules`, `PUT payment-eligibility-rules/{id}`, `DELETE payment-eligibility-rules/{id}` | `{ effect, countries[], ip_countries[], require_ip_match, currencies[], min_amount_base, max_amount_base, allow_guest, max_risk_level, priority, is_active }` (empty lists mean any). Audited `payments.eligibility_rule_created`, `_updated`, `_deleted` | `payments.manage` |
| `PUT payment-gateways/{id}/public-key` | `{ mode, value \| null }`: the publishable identifier (Stripe `publishable_key` with the mode's prefix, PayPal `client_id`, Paymob `public_key`); secret-shaped values refused; the mode needs credentials (409). The gateway page shows the public value per mode. Audited `payments.public_credential_updated` | `payments.credentials` (E) |
| `POST ai-suggestions/{id}/approve`, `/reject` | `{ note? }` (required to reject). Once only (409 after a decision); nothing is published. Audited `ai_suggestions.approved`, `ai_suggestions.rejected` | `ai_suggestions.review` |
| `POST automation-workflows/{id}/enable`, `/disable` | Workflow kill switch (`automation:workflows` on a screen). Audited `automation.workflows_enabled`, `_disabled` | `service_accounts.manage` |

**Scheduled.** `admin:prune-exports` (hourly at :20) deletes the files of expired table exports and keeps the rows.

### 5g. Built SEO and analytics routes (M18)

Admin JSON under `/admin/api` (group `admin`, envelope as 5e). Responses never contain a token, code or secret.

| Route | Body and behaviour | Permission |
|---|---|---|
| `DELETE seo/search-console/connection` | Asks Google to revoke the stored Search Console refresh token (best effort) and deletes it. `{ connected: false }`. Audited `seo.search_console.disconnected`. 10 per minute | `settings.manage_critical` (E) |
| `POST seo/search-console/sync` | Fetches the last 3 days of final Search Console data (totals, queries, pages) into `search_console_metrics`. `{ rows }`. 502 `search_console_unavailable` when Google refuses (expired consent, quota) or is unreachable. 5 per minute | `seo.manage` |
| `PUT settings/analytics` | (5f) now also `browser_events` (bool), `server_purchase` (bool), `mp_region` (`global`\|`eu`), `search_console_property` (`sc-domain:host` or `https://host/`), `search_console_client_id` (`*.apps.googleusercontent.com`), `search_console_verification` (meta tag token), `faq_schema` (bool, default off) | `settings.manage` |
| `PUT settings/analytics/secrets/{key}` | (5f) keys `ga4_api_secret` and `search_console_client_secret` | `settings.manage_critical` (E) |

Admin pages (Blade, `admin.web`): `GET /admin/seo/search-console?days=7|28|90` (report, `seo.manage`, Content > Search Console tab); `GET /admin/seo/search-console/connect` (redirect to Google consent with state and PKCE S256, scope `webmasters.readonly`, offline access) and `GET /admin/seo/search-console/callback?state&code|error` (stores the refresh token encrypted, back to Settings > Analytics), both `settings.manage_critical` and 10 per minute.

Public, outside the session (`routes/seo.php`, `storefront` limiter): `GET /robots.txt` (text), `GET /sitemap.xml` (index; generated on the first request when none exists), `GET /sitemaps/{type}-{locale}-{n}.xml` (children: `static`, `categories`, `platforms`, `products`, `pages`, `blog` per language, with `xhtml:link` hreflang alternates and `lastmod`), all `Cache-Control: public, max-age=3600`; sitemaps carry `X-Robots-Tag: noindex`. New storefront pages: `/blog`, `/blog/{slug}` and their `/ar` twins.

Runtime URL rules (global middleware `ResolveSeoUrls`, storefront GET and HEAD only, never `/api`, `/admin`, `/webhooks`): trailing slash 301; after a 404, admin redirects (301, 302, 410), 410 for a soft-deleted product, 301 to the lower-case path; other misses are counted in `not_found_logs`.

**Scheduled.** `seo:generate-sitemaps` (hourly at :40), `analytics:dispatch-pending` (every 5 minutes; queues `SendAnalyticsEvent` for pending GA4 rows, nothing while awaiting credentials), `seo:fetch-search-console {--days=3}` (daily 06:15; nothing while not connected).

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
