# API Documentation

**Status:** built so far: the authentication routes in section 1a, the two-factor routes in section 4, `GET /admin/api/me` and the envelopes (M3); section 2a storefront catalog, search and currency (M5, M6); section 5a admin catalog, content, media, currency and pricing (M5, M6); section 3a cart, checkout and guest verification (M7); section 4a code reveal and section 5b suppliers, mappings, fulfilment, manual tasks and digital codes (M8, M9); section 3b payment options, attempts, verification and PayPal capture, section 5c staff refunds and section 6 payment webhooks (M10); section 2b product reviews and affiliate clicks, section 4b customer wallet, reviews, tickets and refund requests, section 5d admin coupons, promotions, affiliates, reviews, support and customer wallets, real coupons in 3a and the wallet gateway in 3b (M11); section 8 MCP API with admin MCP tokens and action approval (M16). Everything else is planned. Update the status column per route group as milestones land.

## 1. Conventions

### Base paths

| Group | Prefix | Auth | Status |
|---|---|---|---|
| Public JSON (storefront JS) | `/api/v1/storefront` | Session optional, CSRF for writes, rate limited | catalog, search and currency built (2a); cart built (3a); reviews and affiliate clicks built (2b); newsletter planned |
| Customer JSON | `/api/v1/account` | Session, verified email where noted | two-factor, confirm-password and code reveal built (4a); wallet, reviews, tickets, refund requests built (4b); rest planned |
| Checkout JSON | `/api/v1/checkout` | Session or guest cart token, CSRF | validate, place-order, status, guest OTP (3a), guest code access (4a) and payment routes (3b) built |
| Admin JSON | `/admin/api` | Session, `type=staff`, confirmed 2FA with a session that passed the challenge, permission per route, CSRF | `GET /me`, sections 5a to 5d built; rest planned |
| Yajra DataTables | `/admin/api/tables/{table}` | As admin | planned |
| Provider webhooks | `/webhooks/{provider}` | Signature only, no session, no CSRF, `webhooks` limiter | payment gateways built (6); suppliers planned |
| Automation (n8n) | `/api/v1/automation` | Sanctum token (`n8n`) + HMAC | planned |
| MCP | `/api/v1/mcp` | Sanctum bearer token of an `mcp` service account + scopes | built (M16, section 8) |

Web pages (Blade) are listed in `SEO_STRATEGY.md` section 2.

### Headers

| Header | Use |
|---|---|
| `Accept: application/json` | Required for JSON behaviour |
| `X-CSRF-TOKEN` | Session writes |
| `X-Request-Id` | Accepted or generated (ULID); echoed; logged |
| `Idempotency-Key` | Required on `POST /api/v1/checkout/place-order`. Refund creation is protected by `uq_ru_unit` and the remaining-amount check instead; provider calls carry their own keys (architecture 9.3) |

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
| 403 | `forbidden` | absent | SweetAlert2 error |
| 403 | `two_factor_required` | absent | Redirect to two-factor enrolment or challenge |
| 404 | `not_found` | absent | SweetAlert2 error |
| 403 | `margin_override_forbidden` | absent | SweetAlert2 error |
| 409 | `conflict`, `illegal_transition`, `idempotency_in_progress`, `idempotency_mismatch`, `two_factor_already_enabled`, `duplicate_code`, `review_exists`, `ticket_closed`, `refund_request_open`, `wallet_frozen`, `wallet_idempotency_mismatch` | absent | SweetAlert2 and reload row |
| 423 | `recent_auth_required` | absent | Ask for the password (`POST /api/v1/account/confirm-password`), then retry |
| 422 | `cart_empty`, `item_unavailable` (`errors.items.{cart_item_id}` or `errors.variant_id`), `order_total_invalid`, `otp_invalid` | optional | Message near the form |
| 419 | `csrf_expired` | absent | Prompt to refresh |
| 422 | `validation_failed` | object | Field-level display |
| 422 | Domain codes such as `coupon_invalid`, `price_changed`, `gateway_not_eligible`, `below_margin`, `product_not_publishable`, `rate_missing`, `rate_stale`, `invalid_image`, `base_currency_rate`, `two_factor_invalid`, `two_factor_expired`, `payment_currency_below_margin`, `supplier_not_configured`, `wallet_insufficient_balance`, `wallet_unavailable`, `wallet_amount_invalid`, `review_not_eligible`, `refund_destination_unavailable` | optional | Message near the form |
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
| `otp` | 5 per 10 min per identifier |
| `checkout` | 20/min per session and 60/min per IP |
| `code-reveal` | 10/hour per user or guest order |
| `admin` | 300/min per user |
| `account` | 60/min per user (M11 account routes) |
| `automation`, `mcp` | `service_accounts.rate_limit_per_minute` |
| `webhooks` | 600/min per IP |

Built limiters: `auth`, `otp`, `admin`, `storefront`, `search`, `checkout`, `webhooks` (M10), `account` (M11), `mcp` (M16, per account, `service_accounts.rate_limit_per_minute`), `code-reveal` (`fulfillment.reveal_per_hour`, default 10, per user or guest order). The others are planned with their route groups. `POST /admin/api/exchange-rates/fetch-now` is also limited to 6 per minute.

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
| GET | `/reset-password/{token}` | | | `{ token, email }`, target of the reset email until the M12 page exists |
| POST | `/reset-password` | `token`, `email`, `password`, `password_confirmation` | `auth` | Password reset |
| GET | `/email/verify/{id}/{hash}` | Signed URL; session required | 6/min | Email verified |
| POST | `/email/verification-notification` | Session required | 6/min | Link resent |

Admin: `GET /admin/api/me` returns `{ user, roles, permissions }`. Two-factor enrolment is under `/api/v1/account/security/two-factor`.

---

## 2. Storefront JSON

| Method | Path | Purpose |
|---|---|---|
| GET | `/search/suggest?q=` | Instant suggestions: games, platforms, products, SKU |
| GET | `/search/popular` | Popular searches |
| GET | `/catalog/products` | Listing with filters: `category`, `platform`, `region`, `delivery_type`, `min_price`, `max_price`, `sort` |
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
| GET | `/catalog/products` | `page`, `per_page`, `category`, `platform`, `region`, `delivery_type`, `min_price`, `max_price` (decimal strings, in the display currency), `sort` (`popular`, `newest`, `price_asc`, `price_desc`, `name`) | Page of `{ public_id, name, slug, short_description, delivery_type, platform, category, image, price_from, price_may_vary, is_featured, rating_avg }`; `meta.display_currency` |
| GET | `/catalog/products/{slug}/variants` | `country?` | `{ variants: [{ public_id, title, sku, region, denomination_value, denomination_currency, is_custom_amount, availability, purchasable, price, price_may_vary }] }`. 404 unless the product is active. Cost and margin are never returned. |
| GET | `/currencies` | | Active currencies for the switcher |
| POST | `/currency` | `{ currency }` | Sets the plain `currency` cookie and returns the currency actually used; an inactive or rateless choice returns the base currency. Not CSRF protected because it only changes a display preference. |
| GET | `/search/suggest` | `q` (2 to 100 chars), `locale?`, `commit?` | `{ suggestions: [{ type, title, slug }] }`, matching title prefix, fulltext (name, platform, category, attribute values such as a game name) and product or variant SKU prefix. Send `commit=1` on submit to count the term as a search; plain keystrokes are not counted. |
| GET | `/search/popular` | `locale?` | `{ terms: [...] }`, only terms that found something |

Query budget: one listing page runs at most **10 queries** with a cold cache, whatever the number of rows.

Not built: `/newsletter`. The cart routes are built in M7, see 3a.

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

Registered in the `web` middleware group under `/api/v1` (session and CSRF on writes). A guest's cart is identified by the encrypted, HTTP-only `cart_token` cookie, set on the first `POST /storefront/cart/items`; a signed-in customer's cart by account, and a guest cart is adopted on sign-in when the customer has none. All amounts are in the display currency from the `currency` cookie (section 2a) and are money objects. Suspended accounts get 403.

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

| Method | Path |
|---|---|
| GET, PATCH | `/profile` |
| GET | `/orders`, `/orders/{public_id}` |
| POST | `/orders/{public_id}/units/{unit_public_id}/reveal`: requires recent auth, returns code, audited |
| GET | `/digital-codes` (masked only) |
| GET | `/wallet`, `/wallet/transactions` |
| GET, POST, DELETE | `/wishlist` |
| GET, POST | `/tickets`; POST `/tickets/{public_id}/messages` |
| GET, POST | `/reviews` |
| GET, PATCH | `/notifications`, `/notification-preferences` |
| POST | `/refund-requests` |
| GET, DELETE | `/sessions` |
| POST, DELETE | `/security/two-factor` (built: POST starts enrolment and DELETE disables, both need `current_password`; DELETE is 403 for staff) |
| POST | `/security/two-factor/confirm` (built: `code`; returns recovery codes once) |

Reveal response: `{ code, pin, redemption_url, instructions }`, `Cache-Control: no-store`.

### 4a. Built code reveal routes (M9)

| Method | Path | Body | Limiter | Result |
|---|---|---|---|---|
| POST | `/api/v1/account/confirm-password` | `password` | `auth` | Stores `auth.password_confirmed_at` in the session; reveals need it within `fulfillment.reveal_recent_auth_minutes` (15) |
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

## 7. Automation API

See `N8N_AUTOMATIONS.md` for the mapping of workflows to endpoints. Common request headers: `Authorization: Bearer`, `X-Automation-Timestamp`, `X-Automation-Signature` (`hex(HMAC-SHA256(timestamp + "." + raw_body, secret))`), `X-Workflow-Id`, `X-Execution-Key`.

Responses carry `{ success, data, meta: { generated_at } }`.

| Method | Path |
|---|---|
| GET | `/reports/sales`, `/reports/profit`, `/reports/revenue-comparison`, `/reports/weekly-business`, `/reports/best-selling?by=game\|platform`, `/reports/profitable-products`, `/reports/underperforming-products`, `/reports/supplier-performance`, `/reports/gateway-performance`, `/reports/customer-retention`, `/reports/coupon-performance`, `/reports/pricing-recommendations`, `/reports/expenses`, `/reports/gateway-fees`, `/reports/refunds`, `/reports/unusual-transactions` |
| GET | `/monitor/orders/failed`, `/monitor/orders/pending`, `/monitor/payments/failures`, `/monitor/products/low-margin`, `/monitor/margin-alerts`, `/monitor/suppliers/balances`, `/monitor/suppliers/health`, `/monitor/queues`, `/monitor/exceptions`, `/monitor/scheduler`, `/monitor/database`, `/monitor/latency`, `/monitor/webhooks` |
| GET | `/payments/reconciliation` |
| POST | `/jobs/{catalog-sync\|pricing-sync\|exchange-rates\|reconcile-payments}` |
| GET | `/data/trending`, `/data/product-context/{id}` |
| POST | `/suggestions` (always `pending_review`) |
| GET | `/customers/segments/{abandoned\|review-eligible\|reactivation\|recommendations}` (consented only, no raw email unless the scope allows) |
| POST | `/customers/messages/log` |
| POST | `/executions` (start, finish) |
| POST | `/alerts` |

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
