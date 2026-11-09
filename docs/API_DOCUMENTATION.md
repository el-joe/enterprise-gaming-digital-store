# API Documentation

**Status: everything in this document is planned. Nothing is built yet.** Update the status column per route group as milestones land.

## 1. Conventions

### Base paths

| Group | Prefix | Auth | Status |
|---|---|---|---|
| Public JSON (storefront JS) | `/api/v1/storefront` | Session optional, CSRF for writes, rate limited | planned |
| Customer JSON | `/api/v1/account` | Session, verified email where noted | planned |
| Checkout JSON | `/api/v1/checkout` | Session or guest cart token, CSRF | planned |
| Admin JSON | `/admin/api` | Session, `type=staff`, permission per route, CSRF | planned |
| Yajra DataTables | `/admin/api/tables/{table}` | As admin | planned |
| Provider webhooks | `/webhooks/{provider}` | Signature only, no CSRF | planned |
| Automation (n8n) | `/api/v1/automation` | Sanctum token (`n8n`) + HMAC | planned |
| MCP | `/api/v1/mcp` | Sanctum token (`mcp`) + scopes | planned |

Web pages (Blade) are listed in `SEO_STRATEGY.md` section 2.

### Headers

| Header | Use |
|---|---|
| `Accept: application/json` | Required for JSON behaviour |
| `X-CSRF-TOKEN` | Session writes |
| `X-Request-Id` | Accepted or generated (ULID); echoed; logged |
| `Idempotency-Key` | Required on `POST /api/v1/checkout/place-order` and refund creation |

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
| 404 | `not_found` | absent | SweetAlert2 error |
| 409 | `conflict`, `illegal_transition`, `idempotency_in_progress`, `idempotency_mismatch` | absent | SweetAlert2 and reload row |
| 419 | `csrf_expired` | absent | Prompt to refresh |
| 422 | `validation_failed` | object | Field-level display |
| 422 | Domain codes such as `coupon_invalid`, `price_changed`, `gateway_not_eligible`, `below_margin` | optional | Message near the form |
| 429 | `rate_limited` | absent | Retry after `Retry-After` |
| 500 | `server_error` | absent | Generic message, `request_id` shown. No stack traces or provider bodies. |
| 503 | `maintenance` | absent | |

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
| `checkout` | 20/min per session |
| `code-reveal` | 10/hour per user or guest order |
| `admin` | 300/min per user |
| `automation`, `mcp` | `service_accounts.rate_limit_per_minute` |
| `webhooks` | 600/min per IP |

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
| POST, DELETE | `/security/two-factor` |

Reveal response: `{ code, pin, redemption_url, instructions }`, `Cache-Control: no-store`.

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
| `mcp-actions` | List, `POST {id}/approve`, `POST {id}/reject` |
| `ai-suggestions` | List, `POST {id}/approve`, `POST {id}/reject` |
| `saved-filters` | CRUD by `table_key` |
| `exports` | `POST`, `GET {id}` |
| `dashboard/*`, `reports/*` | `?from=&to=`; cached JSON for Chart.js |

Tables under `/admin/api/tables/{table}`: `products`, `orders`, `payments`, `customers`, `suppliers`, `supplier-products`, `supplier-logs`, `webhook-logs`, `tickets`, `audit-logs`, and others per `IMPLEMENTATION_ROADMAP.md`.

Bulk actions: `POST /admin/api/{resource}/bulk` with `{ action, ids[] }`, returning `{ processed, failed: [{ id, error }] }`.

## 6. Webhooks

| Path | Verification | Response |
|---|---|---|
| `POST /webhooks/stripe` | `Stripe-Signature` | 200 on stored or duplicate, 400 on bad signature |
| `POST /webhooks/paypal` | Offline certificate verification | Same |
| `POST /webhooks/paymob` | HMAC-SHA512 | Same |
| `GET /payments/paymob/return` | HMAC, display only | Redirect to status page |
| `POST /webhooks/suppliers/{key}` | Per adapter; advisory only | 200 |

All excluded from CSRF. Raw body preserved. No sessions.

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

## 8. MCP API

One Laravel route per tool at `/api/v1/mcp/{tool-group}/...`; see `MCP_SERVER.md`. Writes return `202 { action_id, status: "pending_approval", summary }`.
