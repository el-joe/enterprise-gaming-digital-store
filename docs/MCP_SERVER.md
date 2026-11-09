# MCP Server

Status: planned. Independent TypeScript project at `/var/www/enterprise-gaming-digital-store/mcp-server/`. Exposes the store to authorized AI agents such as Claude Desktop and Claude Code.

## 1. Architecture

```
MCP client (Claude Desktop / Claude Code)
   | stdio (default) or Streamable HTTP
mcp-server (Node 20+, TypeScript, zod validation, per-token rate limit)
   | HTTPS, Bearer token
Laravel /api/v1/mcp (scopes, Policies, audit, rate limit)
   | Eloquent
MySQL
```

- SDK: `@modelcontextprotocol/sdk` 1.x (1.32.2 at research date). The v2 scoped packages are newly released; the import paths and elicitation APIs differ between them. Revisit at M16 (ADR-024).
- The MCP server has **no database access and no provider credentials**. Enforcement is in Laravel, so a compromised or modified server gains nothing beyond its token.
- Transports: stdio for local clients, Streamable HTTP for remote use. HTTP mode must sit behind TLS and bind to localhost by default.
- Structured responses: every tool returns `structuredContent` with a JSON `text` fallback.

## 2. Authentication and scopes

Token: Sanctum token issued to a `service_accounts` row of kind `mcp`, shown once in the admin panel. Provided as `EGS_API_TOKEN`. The default token has only read scopes and `is_read_only=true`.

| Scope (Sanctum ability prefix `mcp:`) | Tools |
|---|---|
| `products:read` | Products group |
| `orders:read` | Orders group (masked PII) |
| `analytics:read` | Analytics group |
| `suppliers:read` | Inventory and Suppliers group |
| `marketing:read` | Marketing group |
| `operations:read` | Operations group |
| `products:write` | `update_product_price`, `activate_product`, `deactivate_product` |
| `coupons:write` | `create_coupon` |
| `catalog:sync` | `trigger_catalog_sync` |
| `orders:retry` | `retry_verified_failed_order` |
| `actions:confirm` | Lets the server report a human elicitation approval (off by default) |

**Read-only by default, in two layers:** `MCP_READ_ONLY=true` is the server default and hides write tools from `tools/list`; independently, Laravel rejects writes for any account with `is_read_only=true`. Both must be changed to enable writes.

## 3. Prohibited capabilities

There is no tool, and no Laravel MCP route, for any of the following:

- Raw payment or supplier credentials.
- Unrestricted customer personal data. Identifiers are masked (`j***@example.com`), and `search_customer_orders` needs an exact order number or an exact email.
- Digital code content. Masked hints are not returned either.
- Arbitrary SQL or database access.
- Refunds.
- Critical settings.

## 4. Tools

Pagination params `page` (int, default 1), `per_page` (int, max 100) apply to list tools. Dates are ISO 8601. Inputs are validated by zod in the server and again by a FormRequest in Laravel.

### Products

| Tool | Input | Scope |
|---|---|---|
| `list_products` | `status?`, `platform?`, `category?`, pagination | `products:read` |
| `get_product` | `id_or_sku` | `products:read` |
| `search_products` | `query` (string, 2 to 100), pagination | `products:read` |
| `get_product_pricing` | `variant_sku`, `country?`, `currency?` | `products:read` |
| `check_product_availability` | `variant_sku` | `products:read` |

### Orders

| Tool | Input | Scope |
|---|---|---|
| `list_orders` | `status?`, `from?`, `to?`, pagination | `orders:read` |
| `get_order_details` | `order_number` | `orders:read` |
| `get_order_status` | `order_number` | `orders:read` |
| `search_customer_orders` | `email` (exact) or `order_number`, pagination | `orders:read` |

### Analytics

| Tool | Input | Scope |
|---|---|---|
| `get_sales_summary` | `from`, `to`, `group_by?` | `analytics:read` |
| `get_profit_summary` | `from`, `to`, `group_by?` | `analytics:read` |
| `get_top_selling_products` | `from`, `to`, `limit?` | `analytics:read` |
| `get_supplier_performance` | `from`, `to` | `analytics:read` |
| `get_failed_orders` | `from`, `to`, pagination | `analytics:read` |
| `get_conversion_metrics` | `from`, `to` | `analytics:read` |

### Inventory and suppliers

| Tool | Input | Scope |
|---|---|---|
| `get_supplier_balances` | none | `suppliers:read` |
| `get_supplier_status` | `supplier?` | `suppliers:read` |
| `get_supplier_sync_status` | `supplier?` | `suppliers:read` |
| `get_low_margin_products` | `threshold_pct?`, pagination | `suppliers:read` |

### Marketing

| Tool | Input | Scope |
|---|---|---|
| `get_best_selling_products` | `from`, `to`, `limit?` | `marketing:read` |
| `get_underperforming_products` | `from`, `to`, `limit?` | `marketing:read` |
| `get_coupon_performance` | `from`, `to`, `code?` | `marketing:read` |
| `get_customer_purchase_trends` | `from`, `to` (aggregates only) | `marketing:read` |

### Operations

| Tool | Input | Scope |
|---|---|---|
| `get_pending_orders` | `older_than_minutes?`, pagination | `operations:read` |
| `get_failed_transactions` | `from`, `to`, pagination | `operations:read` |
| `get_pending_reconciliation` | pagination | `operations:read` |
| `get_system_health` | none | `operations:read` |

### Administrative actions

| Tool | Input | Scope | Tier |
|---|---|---|---|
| `update_product_price` | `variant_sku`, `new_price_base`, `reason` | `products:write` | 1, or 2 if the change exceeds 10% or goes below the margin floor |
| `activate_product` | `product_sku`, `reason` | `products:write` | 1 |
| `deactivate_product` | `product_sku`, `reason` | `products:write` | 1 |
| `create_coupon` | `code`, `type`, `value`, `starts_at`, `ends_at`, `usage_limit`, `reason` | `coupons:write` | 1 within the configured value cap, else 2 |
| `trigger_catalog_sync` | `supplier`, `reason` | `catalog:sync` | 1 |
| `retry_verified_failed_order` | `order_number`, `unit_public_id`, `reason` | `orders:retry` | 2 |
| `get_action_status` | `action_id` | any write scope | none |

`retry_verified_failed_order` is rejected unless the unit's latest supplier transaction is `rejected` or `not_accepted_verified`. A unit in `pending_verification` can never be retried through MCP, and execution goes through the same exactly-once gate as any other retry.

## 5. Confirmation mechanism

A confirm tool would not provide human confirmation, because the model can call it itself. Writes therefore need out-of-band approval (ADR-020).

```
1. Agent calls a write tool.
2. Laravel validates scope and arguments, writes mcp_pending_actions (status=pending, TTL 15 min),
   returns 202 { action_id, summary, status: "pending_approval" }. Nothing has changed yet.
3. Approval:
   a. Default: a staff member with mcp_actions.approve opens Admin > AI Actions and approves or rejects,
      seeing tool, arguments, before/after values, requester.
   b. Tier 1, only if the service account has actions:confirm: the MCP server sends an elicitation
      request to the client and reports the user's answer.
4. Laravel re-validates arguments (hash must match), re-checks authorization and business rules,
   executes in a transaction, writes audit_logs and mcp_audit_logs.
5. Agent polls get_action_status.
```

Tier 2 actions accept only the admin panel path. Limits of 3b: a local process holding the token can impersonate the server, so `actions:confirm` is an explicit trust decision per account and is off by default.

Elicitation support differs by client and is checked through client capabilities at runtime. If unsupported, flow 3a applies.

## 6. Rate limiting, audit, errors

- Server: token bucket per process. Laravel: `service_accounts.rate_limit_per_minute` is authoritative.
- `mcp_audit_logs`: every call, including denials and validation failures. Arguments are redacted.
- Tool errors use `isError: true` with `{ code, message, request_id }`. Codes: `forbidden`, `scope_missing`, `read_only`, `invalid_input`, `not_found`, `rate_limited`, `confirmation_required`, `upstream_error`. Never expose Laravel exceptions or stack traces.

## 7. Setup

```bash
cd mcp-server && npm ci && npm run build
```

| Variable | Default | Purpose |
|---|---|---|
| `EGS_API_BASE_URL` | none (required) | Laravel URL. Must be HTTPS except localhost. |
| `EGS_API_TOKEN` | none (required) | Service account token |
| `MCP_READ_ONLY` | `true` | Hide write tools |
| `MCP_TRANSPORT` | `stdio` | `stdio` or `http` |
| `MCP_HTTP_PORT` | `3333` | HTTP mode only |
| `MCP_LOG_LEVEL` | `info` | Logs go to stderr so stdout stays protocol-only |

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

Do not commit a `.mcp.json` containing a real token; reference an environment variable.

## 8. Testing

MCP server: unit tests for schemas and error mapping (Laravel mocked). Laravel: feature tests per tool for no token, wrong scope, read-only account, PII masking, audit written, and the pending-action lifecycle including expiry, argument-tampering rejection, and a blocked retry for a unit in `pending_verification`.
