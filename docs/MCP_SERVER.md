# MCP Server

Status: **built (M16)**. Independent TypeScript project at `/var/www/enterprise-gaming-digital-store/mcp-server/` plus the protected Laravel API at `/api/v1/mcp`. Exposes the store to authorised AI agents such as Claude Desktop and Claude Code. Setup and client configuration: [`mcp-server/README.md`](../mcp-server/README.md).

Scope decision (PM, M16): M16 runs before M14/M15. Tokens are issued and revoked with `php artisan mcp:token` and a JSON endpoint behind `service_accounts.manage`; MCP actions are approved through a JSON endpoint behind `mcp_actions.approve`. The admin screens for both arrive in M15.

## 1. Architecture

```
MCP client (Claude Desktop / Claude Code)
   | stdio
mcp-server (Node 20+, TypeScript, @modelcontextprotocol/sdk 1.32.2, zod 4, local token bucket)
   | HTTPS (HTTP only for localhost), Bearer token, 15 s timeout
Laravel /api/v1/mcp  (RecordMcpCall -> AuthenticateMcpToken -> throttle:mcp -> scope / write checks -> FormRequest)
   | Mcp repositories and readers, existing domain services for writes
MySQL
```

- The MCP server has **no database access and no provider credentials**. Enforcement is in Laravel, so a compromised or modified server gains nothing beyond its token.
- Transport: stdio only in this build (`MCP_TRANSPORT` accepts only `stdio`). Streamable HTTP is not built.
- Every tool result carries `structuredContent` and the same JSON as a `text` block.
- The server uses the SDK's low-level `Server` class rather than `McpServer` (ADR-031): `McpServer` validates tool input itself and turns a schema failure into a plain-text error, while this project requires a structured `invalid_input` error. Tool input schemas are published as JSON Schema generated from the same zod schemas (`z.toJSONSchema`).

## 2. Authentication, scopes and tokens

A token is a Sanctum personal access token of a `service_accounts` row with `kind = mcp`. The middleware accepts only a bearer token (sessions and cookies are ignored), a live token (`expires_at` in the future), an enabled, non-deleted `mcp` account, and an address in the account's `allowed_ips` list when one is set. Scopes are Sanctum abilities with the `mcp:` prefix; only exact abilities count, so `*` grants nothing.

| Scope | Tools |
|---|---|
| `products:read` | Products group |
| `orders:read` | Orders group (masked PII) |
| `analytics:read` | Analytics group |
| `suppliers:read` | Inventory and suppliers group |
| `marketing:read` | Marketing group |
| `operations:read` | Operations group |
| `products:write` | `update_product_price`, `activate_product`, `deactivate_product` |
| `coupons:write` | `create_coupon` |
| `catalog:sync` | `trigger_catalog_sync` |
| `orders:retry` | `retry_verified_failed_order` |
| `actions:confirm` | Lets this account confirm its own tier 1 actions through MCP elicitation (off by default) |

`get_action_status` needs any write scope.

**Issuing tokens.** `php artisan mcp:token issue {account} [--scope=...] [--allow-writes] [--rate-limit=N] [--ip=CIDR] [--days=N]`, `mcp:token list`, `mcp:token revoke --id=N`; or `GET|POST /admin/api/mcp-tokens`, `DELETE /admin/api/mcp-tokens/{id}` (`service_accounts.manage`). Without `--scope` the token gets the six read scopes. Write scopes and `actions:confirm` are refused unless writes are allowed explicitly, which also sets the account's `is_read_only = false`. Tokens expire after 90 days by default (admin setting `mcp.mcp_token_ttl_days`, Settings > MCP). The plain token is shown once; issuance and revocation are written to `audit_logs` without the token.

**Read-only by default, in two layers.** (1) `MCP_READ_ONLY=true` is the server default: write tools are not registered, so they are absent from `tools/list`. The server also hides write tools when the account is read-only or the token lacks the scope (it reads `GET /api/v1/mcp/session` at start-up). (2) Laravel independently refuses every write for an account with `is_read_only = true` (`403 read_only`) and every tool whose scope the token lacks (`403 scope_missing`), before validating arguments. Both layers must be opened to enable writes.

## 3. Prohibited capabilities (hard denials)

There is no tool and no Laravel MCP route for any of the following, whatever the scope (a test asserts the route list):

- Payment or supplier credentials, webhook secrets, service-account HMAC secrets. Supplier queries never select credentials, API payloads or provider error text.
- Unrestricted customer personal data. Emails are masked (`j***@example.com`), phones show the last two digits (`***90`), names the initial (`J***`); IP addresses, user agents and delivery values (player ids) are never returned. `search_customer_orders` needs one exact email or one exact order number.
- Digital code content, and masked code hints too.
- Arbitrary SQL, generic queries or database access. Every route runs fixed queries in `App\Domains\Mcp\Repositories`.
- Refunds.
- Critical settings (no settings route at all).

A canary test fills every sink with known codes, credentials, secrets, a delivery value and customer details, calls all read tools and a write tool with a token holding every scope, and asserts none of them appears in any response or in `mcp_audit_logs`.

## 4. Tools

List tools accept `page` (default 1) and `per_page` (default 20, max 100) and return `meta` with `current_page`, `per_page`, `total`, `last_page`. Date windows are `from`/`to` as `YYYY-MM-DD`, whole days in UTC, inclusive, at most 366 days. Money is `{ "amount": "12.50", "currency": "USD" }`. Inputs are validated by zod in the server and again by a FormRequest in Laravel. Identifiers in paths accept only letters, digits, `.`, `-` and `_`.

### Products (`products:read`)

| Tool | Input | Laravel route | Returns |
|---|---|---|---|
| `list_products` | `status?` (draft, active, inactive), `platform?` slug, `category?` slug, pagination | `GET products` | Products with platform, category, active variant count |
| `get_product` | `id_or_sku` | `GET products/{id_or_sku}` | Product with variants, denomination, base price |
| `search_products` | `query` (2 to 100), pagination | `GET products/search` | Name, product SKU or variant SKU match |
| `get_product_pricing` | `variant_sku`, `country?` ISO-2, `currency?` ISO-3 | `GET variants/{sku}/pricing` | PricingEngine quote: price, base price, source, cost, margin floor, margin %, rate and staleness, block reason |
| `check_product_availability` | `variant_sku` | `GET variants/{sku}/availability` | Sellable flag, variant availability, supplier mappings (status, health, availability), count of available internal stock codes |

### Orders (`orders:read`)

| Tool | Input | Laravel route | Returns |
|---|---|---|---|
| `list_orders` | `status?`, `from?`, `to?`, pagination | `GET orders` | Order summaries, masked email |
| `get_order_details` | `order_number` | `GET orders/{number}` | Totals, masked customer, items, units and supplier attempt statuses |
| `get_order_status` | `order_number` | `GET orders/{number}/status` | Status, final flag, paid flag |
| `search_customer_orders` | `email` (exact) **or** `order_number`, pagination | `GET orders/search` | Order summaries |

### Analytics (`analytics:read`)

| Tool | Input | Laravel route | Source |
|---|---|---|---|
| `get_sales_summary` | `from`, `to`, `group_by?` (day, week, month, none) | `GET analytics/sales-summary` | `order_item_financials`: orders, units, revenue, refunds, net revenue per period |
| `get_profit_summary` | `from`, `to`, `group_by?` | `GET analytics/profit-summary` | `order_item_financials`: supplier cost, gateway fees (estimated rows counted), FX, other cost, gross and net profit, net margin % |
| `get_top_selling_products` | `from`, `to`, `limit?` (1 to 100) | `GET analytics/top-selling-products` | Products by recognised revenue |
| `get_supplier_performance` | `from`, `to` | `GET analytics/supplier-performance` | `supplier_transactions` by outcome, success rate, average seconds to success, recognised supplier cost |
| `get_failed_orders` | `from`, `to`, pagination | `GET analytics/failed-orders` | Orders `failed` or `partially_fulfilled` |
| `get_conversion_metrics` | `from`, `to` | `GET analytics/conversion-metrics` | **Partial**: checkout-to-payment rate from orders; visitor and cart conversion `unavailable` (see 4a) |

Financials are recognised when an order settles (M10), so unsettled orders are not in sales and profit figures; the responses say so in `basis`.

### Inventory and suppliers (`suppliers:read`)

| Tool | Input | Laravel route | Returns |
|---|---|---|---|
| `get_supplier_balances` | none | `GET suppliers/balances` | Last balance cached by `suppliers:refresh-balances` (no live call), threshold, `is_low` |
| `get_supplier_status` | `supplier?` (id, name or adapter key) | `GET suppliers/status` | Status, mode, health, circuit breaker, last health check |
| `get_supplier_sync_status` | `supplier?` | `GET suppliers/sync-status` | Latest catalog and pricing run: status, counts, `has_error` (error text withheld) |
| `get_low_margin_products` | `threshold_pct?` (default "10"), pagination | `GET inventory/low-margin-products` | Active variants below the threshold, lowest margin first; `meta` counts variants scanned (max 5000) and those with unknown cost |

### Marketing (`marketing:read`)

| Tool | Input | Laravel route | Returns |
|---|---|---|---|
| `get_best_selling_products` | `from`, `to`, `limit?` | `GET marketing/best-selling-products` | Units sold on paid orders |
| `get_underperforming_products` | `from`, `to`, `limit?` | `GET marketing/underperforming-products` | Active products with fewest units, zero included |
| `get_coupon_performance` | `from`, `to`, `code?` | `GET marketing/coupon-performance` | Redeemed uses, discount, order revenue per coupon |
| `get_customer_purchase_trends` | `from`, `to`, `group_by?` | `GET marketing/customer-purchase-trends` | Aggregates only: orders, revenue, AOV, guest, registered and repeat orders, top countries |

### Operations (`operations:read`)

| Tool | Input | Laravel route | Returns |
|---|---|---|---|
| `get_pending_orders` | `older_than_minutes?` (default 30), pagination | `GET operations/pending-orders` | Orders in paid, processing, supplier_pending, pending_verification, manual_review or refund_pending unchanged for that long |
| `get_failed_transactions` | `from`, `to`, pagination | `GET operations/failed-transactions` | Failed, expired or cancelled payment attempts: gateway, amount, failure code (no provider ids or messages) |
| `get_pending_reconciliation` | pagination | `GET operations/pending-reconciliation` | Open reconciliation items without `details`, plus unresolved supplier attempts |
| `get_system_health` | none | `GET operations/system-health` | Database, queue (pending and failed jobs), suppliers, exchange rates (stale, missing), fulfilment backlog; scheduler `unavailable` |

### 4a. Results reported as `unavailable`

| Tool | Item | Reason |
|---|---|---|
| `get_conversion_metrics` | `visitor_conversion_rate`, `cart_to_checkout_rate` | Sessions, product views, add-to-cart and checkout-start events are not collected until GA4 (M18). The checkout-to-payment rate is real and returned. |
| `get_system_health` | `scheduler` | Scheduled commands do not write `scheduled_task_runs` heartbeats yet (M17). |

No figure is estimated or invented.

### Administrative actions

Each call stores a pending action and returns `202` with `{ action_id, tool, status: "pending_approval", risk_tier, summary, arguments, expires_at, approval: { admin_panel, elicitation } }`. Nothing changes until approval (section 5).

| Tool | Input | Scope | Tier | Executes through |
|---|---|---|---|---|
| `update_product_price` | `variant_sku`, `new_price_base` (decimal string), `reason` | `products:write` | 1; 2 if the change exceeds 10%, there is no current price, the cost is unknown, or the price is below the margin floor | `VariantPriceService` (explicit price, country `*`, base currency): the margin guard applies; below the floor needs a staff approver with `pricing.override_margin`, and `reason` is the override reason |
| `activate_product` | `product_sku`, `reason` | `products:write` | 1 | `ProductPublicationService::activate` (refuses a product customers could not buy) |
| `deactivate_product` | `product_sku`, `reason` | `products:write` | 1 | `ProductPublicationService::deactivate` |
| `create_coupon` | `code`, `type` (percent, fixed), `value`, `starts_at`, `ends_at`, `usage_limit`, `reason` | `coupons:write` | 1 up to 20% or 10.00 base (settings `mcp.coupon_tier1_max_*`), else 2 | `CouponService` with `created_by_type = mcp`, one use per buyer, never below the margin floor; fixed amounts in the base currency |
| `trigger_catalog_sync` | `supplier` (id, name or adapter key), `reason` | `catalog:sync` | 1 | `SyncSupplierCatalog` job, `triggered_by = mcp:{account id}`; active suppliers only |
| `retry_verified_failed_order` | `order_number`, `unit_public_id`, `reason` | `orders:retry` | always 2 | `FulfilmentAdminService::retryVerifiedUnit` as the approving staff member (`orders.review`), through the order state machine and the supplier gate |
| `get_action_status` | `action_id` | any write scope | none | Own account's actions only |

`retry_verified_failed_order` is refused (`409 retry_not_allowed`) unless the unit's **latest** supplier attempt is `rejected` or `not_accepted_verified`. A unit whose latest attempt is `submitting`, `accepted`, `pending_verification` or `succeeded`, or that has no attempt, is never retried through MCP. The check runs when the action is requested and again at execution under the order and unit row locks, so a unit that moved to `pending_verification` in between is refused.

## 5. Confirmation mechanism (as built)

A confirm tool the model can call would not be human confirmation (ADR-020). The flow:

```
1. Agent calls a write tool.
2. Laravel checks scope and read-only (before validation), validates arguments with the tool's rules, resolves the
   target, decides the tier, refuses an identical live pending action (409 duplicate_action), and stores
   mcp_pending_actions (status pending, TTL 15 min, arguments and their SHA-256 hash, requesting token id).
   202 { action_id, summary, status: "pending_approval", ... }. Nothing has changed.
3. Approval, out of band:
   a. Admin: a staff member with mcp_actions.approve AND the tool's own permission (pricing.manage,
      catalog.publish, marketing.coupons, suppliers.sync, orders.review) calls
      POST /admin/api/mcp-actions/{id}/approve (or /reject with a note). The action runs as that staff member.
      The only path for tier 2.
   b. Elicitation: only for tier 1 and an account whose token holds actions:confirm. The 202 then also carries a
      single-use confirmation_token (48 random characters; only its SHA-256 is stored). The MCP server removes it
      from the tool result, asks the user through MCP elicitation and, only on an explicit approval, sends
      POST /api/v1/mcp/actions/{id}/confirm { confirmation_token }.
4. Execution: under a row lock the action must still be pending and unexpired; the confirmation hash is cleared
   (single use). Then the arguments hash is recomputed (tampering: failed arguments_tampered), the arguments are
   re-validated and re-prepared (target changed: arguments_changed; tier rose: tier_changed), and the account,
   the requesting token (not revoked or expired) and its scope are re-checked. The action runs through the
   domain service; executed or failed is stored with the result, and audit_logs gets mcp.action_requested,
   mcp.action_approved or mcp.action_rejected, and mcp.action_executed or mcp.action_failed.
5. Agent polls get_action_status.
```

Confirmation refusals: another token of the same account `403 forbidden`; another account `404 not_found`; wrong token `403 confirmation_invalid` (the right one still works); after expiry `410 confirmation_expired` and the action becomes `expired`; a second use `409 action_not_pending`; tier 2 `403 admin_approval_required`. `mcp:expire-pending-actions` runs every five minutes, and expiry is also applied when an action is read or decided.

Limits of 3b: a local process holding the token can impersonate the server and confirm its own tier 1 actions, so `actions:confirm` is an explicit trust decision per account and is off by default. Clients without elicitation support fall back to 3a. If execution crashes between approval and completion the action stays `approved` and is visible in the admin queue; it is never re-run automatically.

Admin JSON (screens in M15): `GET /admin/api/mcp-actions?status=` (list, newest first), `POST /admin/api/mcp-actions/{id}/approve` (`note?`), `POST /admin/api/mcp-actions/{id}/reject` (`note` required).

## 6. Rate limiting, audit, errors

- Server: process-wide token bucket (`MCP_RATE_LIMIT_PER_MINUTE`, default 60). Laravel: limiter `mcp`, keyed by account, `service_accounts.rate_limit_per_minute` (default 60) is authoritative; `429 rate_limited` with `Retry-After`.
- `mcp_audit_logs`: one row for every call, including unauthenticated, denied, invalid and rate-limited ones: account, token id, tool (route name), arguments (secrets and the confirmation token removed by the redactor, emails and phones masked), outcome, error code, HTTP status, duration, IP, request id. Responses carry `Cache-Control: no-store`.
- Laravel errors use the standard envelope `{ success: false, message, code, errors?, request_id }`.
- Tool errors use `isError: true` with `structuredContent: { error: { code, message, request_id, errors? } }`. Codes: `invalid_input`, `forbidden`, `scope_missing`, `read_only`, `not_found`, `rate_limited`, `upstream_error`, `unknown_tool`, and specific store codes passed through (`duplicate_action`, `no_change`, `retry_not_allowed`, `supplier_inactive`, `confirmation_invalid`, `confirmation_expired`, `action_not_pending`, `admin_approval_required`). A failure of the action itself after approval is reported in the action's `result.error` (`product_not_publishable`, `margin_override_forbidden`, `arguments_tampered` and so on). Laravel exceptions, server error bodies and stack traces are never passed through; network failures and 5xx become `upstream_error` with a generic message.

## 7. Setup

```bash
cd mcp-server && npm ci && npm run build
```

| Variable | Default | Purpose |
|---|---|---|
| `EGS_API_BASE_URL` | none (required) | Laravel URL. Must be HTTPS except `localhost` and `127.0.0.1`; no credentials in the URL. |
| `EGS_API_TOKEN` | none (required) | Service account token |
| `MCP_READ_ONLY` | `true` | Only the exact value `false` registers write tools |
| `MCP_TRANSPORT` | `stdio` | Only `stdio` is implemented |
| `MCP_LOG_LEVEL` | `info` | Logs go to stderr so stdout stays protocol-only; secret-looking keys are redacted |
| `MCP_HTTP_TIMEOUT_MS` | `15000` | Per request to Laravel |
| `MCP_RATE_LIMIT_PER_MINUTE` | `60` | Local token bucket |

Laravel: admin settings in Settings > MCP (group `mcp`, `settings.manage`; built-in defaults in `config/mcp.php`): `pending_action_ttl_minutes` (15), `coupon_tier1_max_percent` (20), `coupon_tier1_max_fixed_base` (10), `mcp_token_ttl_days` (90). They were the `MCP_*` keys of `backend/.env` until 2026-11-12; `php artisan settings:import-env` copies them once (`DEPLOYMENT.md` 3.2).

### Claude Desktop (`claude_desktop_config.json`)

```json
{
  "mcpServers": {
    "egs-store": {
      "command": "node",
      "args": ["/absolute/path/mcp-server/dist/index.js"],
      "env": {
        "EGS_API_BASE_URL": "https://example.com",
        "EGS_API_TOKEN": "replace-with-token",
        "MCP_READ_ONLY": "true"
      }
    }
  }
}
```

### Claude Code

```bash
claude mcp add egs-store \
  --env EGS_API_BASE_URL=https://example.com \
  --env EGS_API_TOKEN=replace-with-token \
  --env MCP_READ_ONLY=true \
  -- node /absolute/path/mcp-server/dist/index.js
```

Do not commit a `.mcp.json` containing a real token; reference an environment variable (`"EGS_API_TOKEN": "${EGS_API_TOKEN}"`), as shown in the README.

## 8. Testing

- MCP server (`npm test`, node:test, Laravel HTTP mocked): configuration, HTTP client and error mapping (no stack traces, no token in logs, path escape refused), tool registration (read-only default, account and scope filtering), input validation, structured results and errors, local rate limit, and the confirmation flow over an in-memory MCP transport (token hidden from output, confirm only on explicit approval, never for tier 2). `npm run smoke` starts `dist/index.js` over stdio against a local mock API, lists tools and calls a read tool.
- Laravel (`tests/Feature/Mcp`): no token, invalid, expired, revoked, disabled, deleted, non-MCP and user tokens; wrong scope for every read and write tool; wildcard ability; IP allow-list; read-only account; PII masking; audit rows for every outcome; every read tool on real data, `unavailable` results, N+1 budget; the pending-action lifecycle including expiry, reuse, another token, another account, wrong token, argument tampering, revoked token before approval, admin approval with and without the tool permission, rejection; business rules inherited by each write tool (publishability, margin guard and override, coupon caps, sync trigger); `retry_verified_failed_order` blocked for `pending_verification`, `accepted`, `submitting`, `succeeded` or no attempt, allowed for `rejected` and `not_accepted_verified`, and re-checked at execution; rate limits; token issuance and revocation; the route surface; and the canary.
