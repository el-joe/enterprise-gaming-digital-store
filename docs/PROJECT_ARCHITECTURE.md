# Project Architecture

Status: approved baseline for implementation. Date: 2026-11-10. Companion docs: `DATABASE_DESIGN.md` (schema), `INTEGRATIONS.md` (provider facts), `SECURITY.md`, `API_DOCUMENTATION.md`, `IMPLEMENTATION_ROADMAP.md`.

Wording: **must** is a requirement that a test has to prove. "Planned" means not built yet.

---

## 1. Discovery summary

| Area | Finding | Consequence |
|---|---|---|
| Codebase | `backend/` is a pristine Laravel 13 skeleton (framework constraint `^13.17`, installed 13.40), PHP 8.3, Pest 4, Tailwind 4, Vite 8, Pint, Boost. Only `User` model and three default migrations. | Nothing to preserve beyond `users`, `sessions`, `cache`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`. Extend `users` with a new migration; do not edit the originals. |
| PHP extensions | bcmath gd intl mbstring mysqli mysqlnd PDO pdo_mysql redis zip. No pdo_sqlite. No pcntl reported. | Tests run on MySQL (`gaming-store_testing`). Laravel Horizon is excluded (ADR-012). bcmath is available for Money. |
| Runtime | MySQL 8.0.46, Redis 7.0.15, Node 20.20.2 | Generated columns, `SKIP LOCKED`, functional checks are available. |
| Not installed | nginx, supervisor, n8n | Configs and workflow JSON are authored and statically validated, not executed here. |
| Credentials | No supplier or gateway credentials exist. App DB login pending (user is fixing `.env`). | All provider adapters ship complete with mocked tests and are labelled "awaiting credentials / sandbox verification". |

### Risk register

| ID | Risk | Mitigation |
|---|---|---|
| R1 | Duplicate supplier purchase (irreversible loss) | Exactly-once gate, section 8 |
| R2 | Fulfilling an unpaid order | Webhook + provider re-read, section 9; single state machine |
| R3 | Digital code leak | Vault, redaction, no codes in email or logs, section 10 |
| R4 | Chargebacks on irreversible goods | Risk engine, 3DS, first-order caps, section 15 |
| R5 | Reloadly has no direct game top-up API | Top-ups need a second supplier or manual fulfilment, section 7.5 |
| R6 | Stripe does not list Egypt as a merchant country | Business decision, `INTEGRATIONS.md` section 2 |
| R7 | Stale or bad exchange rates | Staleness cut-off and spike guard, section 11 |
| R8 | Supplier balance exhausted | Pre-flight balance check, alerts, fallback |
| R9 | Reloadly default USD 100 per-request cap | One unit per request; lift cap with 2FA and IP whitelist (needs a static egress IP) |
| R10 | Provider webhook loss | Polling reconciliation for every gateway and supplier, section 9.5 |

---

## 2. System overview

```
Browser (Blade + Vite JS)        n8n (self-hosted)        MCP server (TypeScript, stdio)
        |                              |                            |
   web, /admin, /admin/api       /api/v1/automation            /api/v1/mcp
        |                              |                            |
        +---------- Laravel 13 monolith (modular by domain) --------+
             | MySQL (source of truth)   | Redis (cache, queues, rate limits)
             | Queue workers (Supervisor) | Scheduler
             v
  Stripe / PayPal / Paymob / Reloadly / Open Exchange Rates / GA4 MP / Search Console
```

Laravel is the only system that reads or writes the database. n8n and MCP are API clients with scoped tokens.

---

## 3. Code layout

Binding layout (PM decision). The `.claude` Boost guideline "do not create new base folders without approval" is satisfied by that decision.

```
backend/app/
  Domains/<Domain>/{Models,Enums,DTOs,Contracts,Repositories,Services,Actions,Events,Listeners,Jobs,Notifications,Policies,Exceptions,Adapters}
  Http/{Controllers,Requests,Resources,Middleware}/{Storefront,Account,Admin,Api/V1,Webhooks,Integrations}
  DataTables/<Domain>/
  Console/Commands/<Domain>/
```

### Domains

The spec's 22 suggested domains are consolidated as follows. Each domain owns its tables (see `DATABASE_DESIGN.md`).

| Domain | Covers spec domains | Responsibility |
|---|---|---|
| `Shared` | - | `Money`, `Percentage`, `CountryCode` value objects (`ValueObjects/`), base exceptions and the JSON error renderer, log redaction (`Services/`), `Country` reference model, `AppendOnly` model concern, morph map. Reason for this refinement: these are used by every domain and belong to none. |
| `Identity` | - | Users, staff, roles and permissions, 2FA, service accounts |
| `Customers` | Customers | Profiles, consents, risk flags, notes |
| `Catalog` | Catalog, Gaming Platforms, Digital Products | Platforms, categories, products, variants, regions, attributes, delivery field definitions, search |
| `Suppliers` | Suppliers, Supplier Integrations | Adapters, mappings, sync, selection, reconciliation, health |
| `Pricing` | Pricing | Rule engine, margin guard, profitability |
| `Currency` | Currency | Rates, conversion, providers |
| `Fulfillment` | Inventory | Delivery strategies, units, digital code vault, internal code stock, manual tasks |
| `Orders` | Orders | Cart, checkout, state machine, events |
| `Payments` | Payments | Gateway adapters, eligibility, webhooks, refunds, disputes, reconciliation |
| `Wallet` | Wallet | Ledger |
| `Promotions` | Promotions, Coupons | Coupons, promotions, cross and upsell |
| `Affiliates` | Affiliates | Links, referrals, commissions |
| `Reviews` | Reviews | Reviews and moderation |
| `Notifications` | Notifications | Channels, preferences, templates |
| `Support` | Support | Tickets, refund requests |
| `Analytics` | Analytics | Reports, rollups, GA4 server events, Search Console |
| `Seo` | SEO | Metadata, templates, sitemaps, redirects, structured data |
| `Cms` | - | Pages, blog, FAQs, menus, homepage sections, banners, media, newsletter |
| `Settings` | - | Typed settings, encrypted secrets |
| `Automation` | Automation | n8n endpoints, outbox, execution logs, AI suggestions |
| `Mcp` | MCP | MCP API surface, read repositories and readers, write actions, pending actions and confirmation, tokens, audit (built M16) |
| `Audit` | - | Audit log writer |

**Dependency rule:** a domain may call another domain's Services and read its DTOs, Enums and Contracts. It must not use another domain's Repositories. Eloquent relations across domains are permitted for reads and eager loading. A Pest `arch()` test enforces this.

### Layering

`Controller -> FormRequest -> Service/Action -> Repository -> Model`

- Controllers: validate through a FormRequest, authorize, call one service method, return a View or API Resource. No business logic, no direct query building.
- Services hold business rules; single-purpose flows are `Actions` (`PlaceOrderAction`). A service over about 300 lines must be split.
- DTOs are `readonly` classes crossing domain and adapter boundaries. Enums are PHP backed enums stored as `VARCHAR` (ADR-005).
- Events are dispatched after commit (`ShouldDispatchAfterCommit`).

### Where the repository line is drawn

A repository exists only when an aggregate has real query logic worth isolating.

| Has a repository | No repository (Eloquent in the service) |
|---|---|
| `ProductRepository` (listing filters, facets), `OrderRepository`, `PaymentRepository`, `SupplierTransactionRepository`, `SupplierProductRepository`, `PricingRuleRepository`, `CouponRepository`, `CustomerRepository`, `ReportRepository`, `SearchRepository`, `WalletRepository` | `Currency`, `Country`, `Region`, `Setting`, `Faq`, `Banner`, `Menu`, `HomepageSection`, `Page`, `Redirect`, `SavedFilter`, `Wishlist`, `NewsletterSubscriber`, all logging tables |

Yajra DataTable classes own their admin list queries; they do not go through repositories, because their purpose is query composition.

---

## 4. Packages

Installed at M3: Sanctum, Permission, DataTables, Stripe, Image, Google2FA. `geoip2/geoip2` is added when its first consumer lands (M10 eligibility); the npm packages at M14. Boost guidelines require approval for dependency changes; this list is that request and is approved by the PM's stack decision unless marked.

| Package | Purpose |
|---|---|
| `laravel/sanctum` | Service tokens and API auth |
| `spatie/laravel-permission` | RBAC |
| `yajra/laravel-datatables-oracle` | Server-side tables. Export is a queued CSV job, so `-buttons` and Excel packages are not needed. |
| `stripe/stripe-php` | Stripe API and signature verification |
| `intervention/image` | WebP and AVIF derivatives through GD (AVIF depends on the GD build, check at M3) |
| `geoip2/geoip2` | Local GeoLite2 lookup, needs a MaxMind licence key (ADR-016) |
| npm: `datatables.net-dt`, `tom-select`, `sweetalert2`, `chart.js`, `axios` | Admin UI (Tom Select is the "more modern" Select2 alternative; DataTables still requires jQuery internally, confined to the admin bundle) |
| `pragmarx/google2fa` | Staff TOTP |

PayPal, Paymob and Reloadly use the Laravel HTTP client directly, with no third-party SDK.

---

## 5. Request and queue topology

| Queue | Purpose | Worker timeout | `retry_after` | Job tries |
|---|---|---|---|---|
| `webhooks` | Process stored provider webhooks | 60 s | 180 s | 5 |
| `payments` | Payment verification, refunds, reconciliation | 90 s | 180 s | 3 (idempotent reads and provider idempotent writes only) |
| `fulfillment` | Supplier order submission | 90 s | 180 s | **1, no automatic retry** |
| `verification` | Supplier status polling | 60 s | 180 s | 3 |
| `notifications` | Mail and in-app | 60 s | 180 s | 3 |
| `sync` | Catalog and price sync | 600 s | 660 s | 2 |
| `reports` | Exports, rollups | 600 s | 660 s | 2 |
| `default` | Everything else | 60 s | 180 s | 3 |

`retry_after` is per connection: `redis` (180 s) and `redis-long` (660 s).

Rules:

1. `retry_after` must exceed the worker timeout, otherwise a job runs twice while still in progress.
2. HTTP timeout must be smaller than the worker timeout. Supplier default: connect 5 s, total 30 s, configurable per supplier up to 60 s.
3. **No external HTTP call may occur inside a database transaction.**
4. Fulfilment safety comes from the database gate in section 8, never from queue semantics. A redelivered job is expected and is a no-op.

### Scheduled commands

| Command | Frequency |
|---|---|
| `orders:expire-unpaid` | every 5 min |
| `payments:reconcile-open` | every 10 min |
| `payments:reconcile-daily` | daily 02:00 UTC |
| `suppliers:sweep-stale-submissions` | every minute |
| `suppliers:verify-pending` | every minute |
| `suppliers:reconcile-daily` | daily 02:30 UTC |
| `suppliers:refresh-balances` | every 15 min |
| `suppliers:health-check` | every 5 min |
| `suppliers:sync-catalog` | daily 03:00 UTC, per supplier setting |
| `suppliers:sync-pricing` | every 6 hours |
| `currency:fetch-rates` | hourly at minute 5 (provider publishes shortly after the hour) |
| `pricing:recalculate-dirty` | every 5 min (also marks rules, explicit prices and promotions whose window started or ended) |
| `analytics:rollup-daily` | hourly |
| `analytics:dispatch-ga4` | every minute |
| `analytics:sync-search-console` | daily 06:00 UTC |
| `seo:generate-sitemaps` | every 6 hours |
| `carts:detect-abandoned` | every 15 min |
| `automation:flush-outbox` | every minute |
| `auth:prune-mobile-tokens` | hourly (M-A2: expired refresh tokens, two-factor login challenges and customer access tokens) |
| `logs:prune` | daily (retention in `SECURITY.md` section 9) |
| `queue:prune-failed --hours=720`, `queue:monitor` | daily, every minute |

Every scheduled command records a heartbeat to `scheduled_task_runs`; F04 in `N8N_AUTOMATIONS.md` and the MCP `get_system_health` scheduler check read it. Commands that overlap use `withoutOverlapping()->onOneServer()`. Built in M17: the entries live in `App\Console\TaskSchedule` (so the monitor reads the same cron expressions as the scheduler), heartbeats come from the scheduler's own task events (`RecordScheduledTaskHeartbeat`), and `currency:fetch-rates`, `suppliers:sync-catalog` and `payments:reconcile-daily` skip when `settings.automation.task_drivers` hands them to n8n (ADR-014). Of the commands listed above, `analytics:*`, `seo:generate-sitemaps`, `carts:detect-abandoned`, `logs:prune` and the queue commands are not built yet; the built ones also include `orders:prune-idempotency-keys`, `wallet:verify-ledger`, `mcp:expire-pending-actions`, `auth:prune-mobile-tokens` (M-A2) and `orders:prune-guest-order-tokens` (M-A3, hourly).

---

## 6. Order state machine

A single class, `Domains\Orders\Services\OrderStateMachine`, is the only writer of `orders.status`. The column is not mass-assignable and a Pest arch test forbids writes elsewhere. Every transition, in one DB transaction holding `SELECT ... FOR UPDATE` on the order: validates against the table below, updates the status, appends an `order_events` row, and dispatches `OrderStatusChanged` after commit. An attempt at a disallowed transition throws `IllegalOrderTransition` and logs an alert; it never silently succeeds.

### 6.1 Order statuses

`pending_payment`, `payment_processing`, `paid`, `processing`, `supplier_pending`, `pending_verification`, `manual_review`, `completed`, `partially_fulfilled`, `failed`, `cancelled`, `refund_pending`, `partially_refunded`, `refunded`.

Additions to the spec's 11 suggested statuses, with reasons: `pending_verification` (spec Phase 5 requires it by name), `partially_fulfilled` and `partially_refunded` (multi-item carts can end in mixed outcomes, and collapsing those into another status would misreport them).

### 6.2 Allowed transitions

| From | To | Trigger | Actor |
|---|---|---|---|
| `pending_payment` | `payment_processing` | Verified async state (Stripe `processing`, PayPal capture `PENDING`, Paymob `pending=true`) | Webhook or poll job |
| `pending_payment` | `paid` | Verified success, amount and currency match | Webhook or poll job, internal wallet capture |
| `pending_payment` | `failed` | Risk block before payment, or attempts exhausted (default 5) | System |
| `pending_payment` | `cancelled` | Customer cancels, or quote TTL expires (default 30 min) | Customer, `orders:expire-unpaid` |
| `pending_payment`, `payment_processing` | `manual_review` | Verified success but amount or currency mismatch | System |
| `payment_processing` | `paid` | Verified success | Webhook or poll job |
| `payment_processing` | `pending_payment` | Attempt failed, retry permitted | Webhook or poll job |
| `payment_processing` | `failed` | Attempt failed, none remaining | Webhook or poll job |
| `paid` | `processing` | Risk check passed, `FulfillOrder` started | System |
| `paid` | `manual_review` | Risk hold | Risk engine |
| `processing` | `completed` | All units delivered | Fulfilment |
| `processing` | `supplier_pending` | At least one unit awaiting supplier, none ambiguous | Fulfilment |
| `processing` | `pending_verification` | At least one unit ambiguous | Fulfilment, sweeper |
| `processing` | `manual_review` | Manual task required, or selection found no candidate and policy holds | Fulfilment |
| `processing` | `partially_fulfilled` | All units settled; some delivered, some definitively failed | Fulfilment |
| `processing` | `refund_pending` | All units definitively failed | Fulfilment |
| `supplier_pending` | `completed`, `pending_verification`, `manual_review`, `partially_fulfilled`, `refund_pending` | Supplier status update (same meaning as above) | Poll job, supplier webhook then poll |
| `pending_verification` | `supplier_pending` | Verified: supplier is still processing | Poll job |
| `pending_verification` | `completed` | Verified: supplier succeeded and codes stored | Poll job |
| `pending_verification` | `processing` | Verified not accepted and a safe fallback exists | Poll job, or admin |
| `pending_verification` | `partially_fulfilled`, `refund_pending` | Verified failed, no fallback | Poll job |
| `pending_verification` | `manual_review` | Verification deadline passed, or supplier has no lookup capability | Poll job |
| `manual_review` | `processing` | Admin approves, or retries a *verified failed* unit | Admin (`orders.review`) |
| `manual_review` | `completed` | Manual fulfilment finished | Admin (`orders.fulfill_manual`) |
| `manual_review` | `refund_pending` | Admin refunds | Admin (`refunds.create`) |
| `manual_review` | `cancelled` | Admin rejects an unpaid order | Admin |
| `partially_fulfilled` | `refund_pending` | Automatic refund of undelivered units | System |
| `partially_fulfilled` | `processing` | Admin retries verified-failed units | Admin |
| `refund_pending` | `refunded` | All paid value refunded and confirmed | Webhook or poll job |
| `refund_pending` | `partially_refunded` | Undelivered value refunded and confirmed | Webhook or poll job |
| `refund_pending` | `manual_review` | Refund failed | Webhook or poll job |
| `completed`, `partially_refunded` | `refund_pending` | Authorized refund | Admin (`refunds.create`) |
| `cancelled`, `failed` | `manual_review` | **Late verified payment** on a closed order. Never auto-fulfil. Refund or approve by a human. | Webhook or poll job |

Settled at M8 (`OrderProgress`): the order follows the aggregator only through rows the actor may use. Two implied moves are held instead: `supplier_pending` to `processing` (a fallback unit queued while others wait; the order stays `supplier_pending`), and any exit from `manual_review` by the system. A staff actor whose permission does not cover the implied row releases the order to `processing` first when they hold `orders.review`; otherwise a `status_held` event is recorded and the order waits for `POST orders/{id}/review`. Unit status changes are recorded as `unit.*` order events with `unit_from` and `unit_to` in metadata.

Terminal statuses: `refunded`. Effectively terminal unless a row above applies: `completed`, `partially_refunded`, `cancelled`, `failed`. Disputes do not change status; they set `orders.has_open_dispute` and raise an alert.

Enforcement settled at M7 (`OrderTransitionTable`, `OrderStateMachine`):

- The Actor column is enforced. "Webhook or poll job" rows accept `webhook` and `system`; "System", "Fulfilment" and "Risk engine" rows accept `system`; "Admin" rows accept `staff` only, and the staff user must hold the permission named in the row (`orders.review` where the row names none; `orders.reconcile` for `pending_verification` to `processing`). `n8n` and `mcp` actors are never accepted. Callers still authorize their own endpoints.
- Rule 9: entering `paid`, `processing`, `supplier_pending`, `pending_verification`, `completed` or `partially_fulfilled` requires `orders.primary_payment_id`, so M10 sets the verified payment before transitioning. This also covers `manual_review` to `processing` for an order that reached review without payment.
- Entering `paid` stamps `paid_at` once; `completed` stamps `completed_at`; any closed status clears `placement_hash`. Entering `cancelled` or `failed` on an order that was never paid (`paid_at` null, so also `manual_review` to `cancelled`) releases the coupon reservation through `CheckoutCoupons::release`; release touches only `reserved` uses and is idempotent (M11).
- Orders are created only by `OrderStateMachine::open` (status `pending_payment`, event `order.placed`). Saving a changed status through the model throws, and an arch test fails on any status write outside the machine.
- The risk engine's `block` decision cannot refund from `paid` (no such row): `OrderRiskGate` moves both `review` and `block` to `manual_review`, and a human refunds from there.

### 6.3 Unit-level status

Each purchased unit (quantity 3 means 3 rows in `order_item_units`) has its own status: `pending`, `queued`, `submitting`, `supplier_pending`, `pending_verification`, `manual_task`, `manual_review`, `delivered`, `failed`, `refund_pending`, `refunded`, `cancelled`.

Order status is derived from units by `OrderStatusAggregator`, in this precedence after payment:

1. any `pending_verification` gives `pending_verification`
2. else any `manual_review` or `manual_task` gives `manual_review`
3. else any `pending`, `queued`, `submitting`, `supplier_pending`: `supplier_pending` if any supplier-pending, else `processing`
4. else all `delivered` gives `completed`
5. else some `delivered` gives `partially_fulfilled`
6. else `refund_pending`

---

## 7. Supplier engine

### 7.1 Adapter contract

```php
namespace App\Domains\Suppliers\Contracts;

interface SupplierAdapter
{
    public function key(): string;                       // 'reloadly_giftcards'
    public function capabilities(): SupplierCapabilities;
    public function testConnection(): ConnectionResult;

    public function getProducts(ProductQuery $query): SupplierProductPage;
    public function getProductDetails(string $supplierProductId): SupplierProductData;
    public function getCountries(): array;               // list<SupplierCountryData>
    public function getPricing(string $supplierProductId): SupplierPricingData;
    public function checkAvailability(AvailabilityQuery $query): AvailabilityResult;
    public function getBalance(): SupplierBalanceData;

    public function createOrder(SupplierOrderRequest $request): SupplierOrderOutcome;
    public function getOrderStatus(string $supplierTransactionId): SupplierOrderOutcome;
    public function findOrderByReference(string $clientReference, CarbonInterface $since): SupplierOrderOutcome;
    public function fetchDeliverables(string $supplierTransactionId): DeliverablePayload;

    public function cancelOrder(string $supplierTransactionId): SupplierCancelResult;
    public function refundOrder(string $supplierTransactionId, ?Money $amount): SupplierRefundResult;
}
```

`AbstractSupplierAdapter` implements every optional method to throw `CapabilityNotSupported`. Callers must check `capabilities()` first; hitting the exception is a bug, logged at error level.

```php
enum SupplierCapability: string {
    case ListProducts = 'list_products';       case ProductDetails = 'product_details';
    case Countries = 'countries';              case Pricing = 'pricing';
    case Availability = 'availability';        case Balance = 'balance';
    case CreateOrder = 'create_order';         case OrderStatus = 'order_status';
    case LookupByReference = 'lookup_by_reference';
    case FetchDeliverables = 'fetch_deliverables';
    case CancelOrder = 'cancel_order';         case RefundOrder = 'refund_order';
    case TransactionHistory = 'transaction_history';
    case Webhooks = 'webhooks';                case ClientIdempotency = 'client_idempotency';
    case PerRequestAmountCap = 'per_request_amount_cap';
}
enum CapabilityLevel: string { case Full = 'full'; case Advisory = 'advisory'; case None = 'none'; case Unverified = 'unverified'; }
```

`SupplierCapabilities` maps each capability to a level and carries `supportedDeliveryTypes` and `sensitiveResponsePaths` (fields redacted before logging). The admin supplier screen renders this matrix.

Reloadly's declared matrix is in `INTEGRATIONS.md` section 1.6. Adapters are registered in `SupplierAdapterRegistry` by `suppliers.adapter_key`, so a new supplier is a new class plus a database row; checkout and order code do not change (rule 7).

Built-in adapters: `ReloadlyGiftCardAdapter`, `InternalStockAdapter` (admin-loaded codes in `digital_codes`), `ManualFulfillmentAdapter`. The latter two are real fulfilment paths, not mocks. Test doubles live under `tests/` only.

Settled at M8: manual fulfilment is a delivery strategy (`ManualTaskStrategy`, used by `DirectTopupStrategy` per BD2), not a supplier order. `ManualFulfillmentAdapter` declares every supplier operation `none` and only lists its delivery types, so the capability matrix shows it but selection never picks it. A staff task has no supplier order to poll and `supplier_product_mappings` requires a supplier product, so routing it through the gate would invent both. `SupplierCapabilities` also carries `defaultPerRequestCap` (Reloadly: USD 100), used when `suppliers.per_request_cap_base` is null. The contract gained `getTransactions()` for daily reconciliation.

### 7.2 Outcome classification

Every `createOrder`, `getOrderStatus` and `findOrderByReference` returns a `SupplierOrderOutcome` with one of:

| Kind | Meaning | Next step |
|---|---|---|
| `Fulfilled` | Supplier says done | Fetch deliverables |
| `InProgress` | Accepted, not finished | Status polling |
| `Rejected` | Supplier **definitively did not accept**, or reversed the purchase | Fallback allowed |
| `Ambiguous` | Anything else | `pending_verification`, **never retried** |
| `NotFound` | Lookup found nothing (lookup methods only) | Stay in verification until deadline |

**Default-deny:** a response is `Rejected` only if it matches an adapter's explicit allow-list. Unknown error codes, 5xx, malformed bodies, read timeouts and connection resets are `Ambiguous`.

`Rejected` is permitted only for: (a) failure before the request was sent (DNS failure, connection refused, TLS handshake failure, connect timeout), (b) authentication refused (401/403), (c) documented validation or stock refusals on the allow-list, (d) a supplier-reported reversal or failure status.

### 7.3 Selection

`SupplierSelector::rank(OrderItemUnit, context): list<SupplierCandidate>`. Filter, then sort. The result is stored on the unit as `selection_snapshot` for audit.

Filters (all must pass): mapping active; supplier `active` and circuit not open; supplier supports the product's delivery type; region and country allowed for this mapping; availability not `out_of_stock`; cost known and younger than `max_price_age_minutes`; cached balance covers the cost; amount within per-request cap if declared; margin at the mapping's cost is at or above the minimum, or the product allows it.

Settled at M8: an unknown cached balance excludes a supplier that declares `Balance` (`balance_unknown`); cost is the mapping override or the latest effective cost in `supplier_product_prices` for the mapped denomination, younger than `suppliers.max_price_age_minutes`; the margin is checked on the unit's net base price after its coupon share, including the payment currency's conversion cost. Availability calls the adapter only when it declares `availability` as `full` (internal stock); Reloadly's is advisory and uses the synced status.

Sort by the strategy in `settings.suppliers.selection_strategy`:

- `priority` (default): mapping `priority` ascending, then lowest cost.
- `lowest_cost`: lowest normalized cost, then priority.

No candidates: the unit goes to `manual_review` with reason `no_supplier_available`.

### 7.4 Safe fallback rule

Fallback to the next candidate occurs **only** when all of these hold:

1. The current attempt's outcome is `Rejected`.
2. `suppliers.fallback_enabled` is true, and the mapping allows it.
3. Candidates remain, and the attempt count is below `max_fallback_attempts` (default 2).
4. The next candidate still passes the margin guard. Otherwise the unit goes to `manual_review`.

Ambiguous or timeout outcomes always go to `pending_verification`. There is no code path from `Ambiguous` to a new `createOrder`. If verification later proves "not accepted", section 8.3 applies.

`retry_policy` on a supplier applies only to `Rejected` failures of type (a) above, and only on the same supplier.

Settled at M8: `retry_policy` is `{ "max_attempts": n }` (0 to 3). The schema has no per-mapping fallback flag, so condition 2 is `suppliers.fallback_enabled` of the refusing supplier only. Condition 3 counts attempts on the unit: fallback is allowed while attempts so far are at most `max_fallback_attempts`. A unit whose remaining candidates fail only on margin goes to `manual_review` (`fallback_below_margin`); with no candidate at all it is `failed`. A supplier transaction id already held by another attempt of the same supplier is treated as ambiguous and alerted.

### 7.5 Delivery types

| Type | Strategy | Realised through |
|---|---|---|
| `instant_code` | `CodeDeliveryStrategy` | Reloadly, internal stock |
| `api_delivery` | `CodeDeliveryStrategy` or adapter-specific | Any adapter declaring it |
| `direct_topup` | `DirectTopupStrategy` | **No integrated supplier supports player-ID top-up.** Manual fulfilment until a suitable supplier is added |
| `account_recharge` | `DirectTopupStrategy` | As above |
| `subscription_activation` | `CodeDeliveryStrategy` or manual | Depends on product |
| `manual` | `ManualTaskStrategy` | Staff task |

Required fields per product (`product_delivery_fields`) are validated at cart and checkout and stored encrypted per order item.

---

## 8. Idempotency and locking: supplier orders

### 8.1 Unit of work

Fulfilment operates per single unit. One supplier request buys one card. Reasons: partial failure is expressible, refunds are per unit, and Reloadly's per-request cap is respected.

### 8.2 The exactly-once gate

`SubmitUnitToSupplier` job, `tries = 1`:

```
1. BEGIN
     SELECT unit FOR UPDATE
     if unit.status not in (queued) -> COMMIT, return (redelivery no-op)
     choose candidate (already ranked)
     INSERT supplier_transactions(status='submitting', client_reference, attempt_no, ...)
       -- the generated unique column active_unit_key rejects a second active row
     unit.status = 'submitting'
   COMMIT
2. adapter->createOrder(...)      -- no DB transaction held
3. BEGIN
     SELECT unit, supplier_transaction FOR UPDATE
     apply outcome; write order_events; update aggregate
   COMMIT
```

Because the row is committed before the HTTP call, a crash at any point after step 1 leaves durable evidence. `suppliers:sweep-stale-submissions` moves any `submitting` row older than `http_timeout + 60s` to `pending_verification`.

Layers of protection against duplicates:

| Layer | Mechanism |
|---|---|
| Database | Unique generated column `supplier_transactions.active_unit_key` allows one active attempt per unit. Active statuses: `submitting`, `accepted`, `pending_verification`, `succeeded`. |
| Row lock | `FOR UPDATE` on the unit |
| Reference | Stable `client_reference` per unit per supplier, sent as Reloadly `customIdentifier` |
| Duplicate codes | `digital_codes.code_hash` unique |
| Job | `tries=1`, unique job key |
| Order-level | `(user or guest email, cart hash)` idempotency at checkout, and the `Idempotency-Key` header on place-order |

### 8.3 Verification and reconciliation

`suppliers:verify-pending` picks `pending_verification` and `accepted` rows that are due. Backoff schedule: 1, 2, 5, 10, 15, 30 min, then every 30 min until `verification_deadline` (default 2 hours after submission; per supplier).

| Lookup result | Action |
|---|---|
| Found, finished | Fetch deliverables, deliver |
| Found, in progress | `accepted`, continue polling |
| Found, failed or reversed | `rejected`; fallback rules apply |
| Not found | Keep polling until the deadline |
| Not found at deadline | Order goes to `manual_review`. The transaction stays `pending_verification` and keeps the active lock. |
| No lookup capability | `manual_review` immediately |

Absence from a supplier report is not proof of non-acceptance, so automatic retry after "not found" is forbidden. A human with `orders.reconcile` may confirm, with a mandatory written reason and evidence, "verified not accepted". This sets `not_accepted_verified`, which releases the active lock. A retry may then run through the normal gate, **reusing the same `client_reference` on the same supplier**, so a supplier that enforces uniqueness provides a second barrier.

`suppliers:reconcile-daily` compares each supplier's transaction report against our rows for the previous 48 hours and raises `reconciliation_items` for: supplier transactions unknown to us (orphans), status mismatches, and cost differences.

After a successful purchase, a failed deliverable fetch never triggers a new `createOrder`. The fetch is a safe read and retries independently (`code_retrieval_status`).

---

## 9. Idempotency and locking: payments

### 9.1 Principles

Never trust the browser. A redirect only renders a "checking" page. An order becomes `paid` only after a verified webhook **and** a provider-side re-read, or a server-initiated capture response.

### 9.2 Adapter contract

```php
interface PaymentGateway
{
    public function key(): string;
    public function capabilities(): GatewayCapabilities;       // refunds, partial, void, webhooks, supported methods
    public function supportsCurrency(string $currency): bool;  // from payment_gateway_currencies
    public function createPayment(CreatePaymentRequest $r): CreatePaymentResult;
    public function capture(Payment $p): VerifiedPaymentState;  // PayPal; unsupported elsewhere
    public function fetchState(Payment $p): VerifiedPaymentState;
    public function verifyWebhook(WebhookRequestData $r): VerifiedWebhook; // throws InvalidWebhookSignature
    public function parseWebhook(VerifiedWebhook $w): GatewayEvent;
    public function refund(Refund $r): RefundResult;
    public function toGatewayAmount(Money $m): string|int;      // per-gateway minor-unit rule
}
```

### 9.3 Keys

| Operation | Stripe `Idempotency-Key` / PayPal `PayPal-Request-Id` | Paymob |
|---|---|---|
| Create | `pay_create:{payment.public_id}` | No idempotency documented. `special_reference = {order_number}-{attempt}`. A timed-out create opens a new attempt; an abandoned intention costs nothing because no customer was redirected to it. |
| Capture (PayPal) | `pay_capture:{payment.public_id}` | n/a |
| Refund | `refund:{refund.public_id}` | Single in-flight refund enforced locally. A timed-out refund is set to `refunds.status = unknown` and is resolved by re-reading the transaction's `refunded_amount_cents` before any retry. |

Stripe keys are retained for at least 24 hours (documented), so the retry window for create and refund is capped at 12 hours. After this, the operation is re-read from the provider and never blindly resent. PayPal retention varies by endpoint (45 days is documented for refunds); treat all others as unknown and apply the same cap.

### 9.4 Webhook processing

```
POST /webhooks/{gateway}
  1. Read raw body. Verify signature (timestamp tolerance where applicable). Reject 400 on failure.
  2. INSERT payment_webhooks (gateway_id, provider_event_id UNIQUE, raw payload encrypted, status='received').
     Duplicate key -> return 200 immediately.
  3. Return 200. Dispatch ProcessPaymentWebhook.

ProcessPaymentWebhook (queue: webhooks):
  a. Locate payment by provider reference (not by metadata alone).
  b. adapter->fetchState()  -- no transaction held (provider re-read)
  c. BEGIN; SELECT order FOR UPDATE; SELECT payment FOR UPDATE
     idempotent state apply:
       compare verified amount + currency with payments snapshot
       set orders.primary_payment_id (UNIQUE) on first success
       transition order
     COMMIT
```

Paymob has no event ID, so `provider_event_id = sha256(obj.id|success|pending|is_refunded|is_voided|is_capture|refunded_amount_cents|captured_amount|updated_at)`. Handlers are state-based, so duplicate or out-of-order events are harmless; Stripe explicitly does not guarantee ordering and can emit two separate Event objects for one occurrence.

**Double payment:** if a second payment on the same order succeeds, the unique `primary_payment_id` slot is already held. The second becomes `duplicate_capture`, an auto-refund is created, and finance is alerted.

**Lock order, always:** `carts` (placement only, before any order row exists), `orders`, `payments`, `order_item_units`, `supplier_transactions`, `customer_wallets`, `coupons` (M11: reservation and release; a refund request locks its order before the request row). Deadlocks (MySQL 1213) are retried with `DB::transaction($fn, attempts: 3)`, only because those closures contain no external calls.

Redis locks are never a safety mechanism. They are used only to reduce contention (ADR-008).

Built at M10 (`Payments\Services`): `WebhookIntake` (steps 1 to 3), `WebhookProcessor` (a to c), `PaymentVerifier` (re-read, plus server capture of an approved PayPal order) and `PaymentStateApplier`, the only writer of payment success and of the paid transition. The same verifier serves webhooks, the return page (`verify-payment`), the PayPal capture route and reconciliation, so arrival order does not matter. A provider failure during a read changes nothing and schedules `next_check_at`. Paid needs amount **and** currency equal to the attempt's snapshot; a mismatch takes the `primary_payment_id` slot (the money is ours to refund or approve) and goes to `manual_review`. Refunds: `RefundService` (BD4: failed units automatically, delivered units by staff with `refunds.create`), `RefundExecutor` (committed `requested`, claimed to `pending`, sent outside any transaction), `RefundStateApplier`. A failed refund releases its units and sends the order to `manual_review`. Paymob locates attempts by `provider_order_id` (`idx_pay_provider_order`, M10).

### 9.5 Reconciliation

| Job | Scope | Action |
|---|---|---|
| `payments:reconcile-open` | `payments` in non-final state older than 5 min | `fetchState`, apply through the normal path. Covers lost webhooks. |
| `payments:reconcile-daily` | Prior 48 h of succeeded and refunded rows | Compare with provider; write `reconciliation_items` |
| Refund poll | `refunds.status` in (`pending`, `unknown`) | `fetchState` / refund read |

---

## 10. Digital code encryption and access control

| Aspect | Decision |
|---|---|
| Algorithm | AES-256-GCM through a dedicated `Illuminate\Encryption\Encrypter` instance |
| Key | `DIGITAL_CODE_KEYS` keyring (separate from `APP_KEY`), `kid`-indexed, with `DIGITAL_CODE_ACTIVE_KID`. `key_version` is stored per row. Rotation re-encrypts in a queued job. |
| Encrypted fields | code, PIN, redemption URL (separately) |
| Duplicate detection | `code_hash = HMAC-SHA256(blind_index_key, normalized code)`, unique. Never a plain hash, since gift codes have low entropy. |
| Display | `masked_code` (last 4 only) is the only value visible without reveal |
| API surface | Only `DigitalCodeVault::reveal(DigitalCode, Actor, RevealPurpose)` decrypts. The Eloquent `$hidden` list includes ciphertext; no accessor decrypts implicitly. |
| Who may reveal | The owning customer on a delivered unit; a verified guest; staff with `digital_codes.reveal` and a written reason. Super Admin gets this only as an explicit grant, not via a blanket `Gate::before` (see `SECURITY.md`). MCP and n8n: never. |
| Customer auth | Re-confirm password or OTP within the last 15 min (`password.confirm` style), rate limit 10 per hour |
| Guest | Signed expiring link (72 h) plus email OTP, then a session scoped to that order |
| Audit | Every reveal, success or denial, is an append-only `digital_code_accesses` row |
| Email | Contains a secure link, **never the code** (ADR-009) |
| Supplier email | `recipientEmail` is omitted on Reloadly orders so the supplier sends nothing outside our control |
| Parsing | JSON decoded with `JSON_BIGINT_AS_STRING`; Reloadly types `cardNumber` as integer, and a numeric cast can lose digits or leading zeros. Treat as string. |
| Logs | `supplier_api_logs` stores the redacted body. Deliverable endpoint bodies are never stored at all. A Monolog processor strips known key names. HTTP exceptions from deliverable endpoints are caught and rethrown without the response body. |
| Queues | Jobs carry IDs only, never code material. `DeliverableItem` and `RevealedCode` refuse serialization and hide their values from dumps |
| Built (M9) | `DigitalCodeVault` (store, decrypt, re-encrypt), `DigitalCodeKeyring` (`DIGITAL_CODE_KEYS` as `kid:base64` pairs), `CodeRevealService`, `digital-codes:rotate-keys`. Customer recent auth is `POST /api/v1/account/confirm-password`. Stock allocation forces `idx_dc_stock`, because an index-merge plan made `SKIP LOCKED` wait |

---

## 11. Money and currency

### 11.1 Representation

**All money amounts are `DECIMAL(20,6)`, paired with an explicit `CHAR(3)` currency column.** This applies to every money column in the system. In PHP, money is the `Money` value object, string-backed with bcmath. Floats are forbidden, enforced by an arch test against `float` properties on Money-bearing DTOs and by never casting these columns to `float`.

Why decimal rather than integer minor units: Reloadly returns costs and balances to 5 decimal places (official sample: `"cost": 60553.3575`, `"oldBalance": 60582.23641`). Integer minor units would either lose real cost precision, which is the input to every profit report, or force a second representation for supplier amounts. `DECIMAL(20,6)` gives one type everywhere, exact `SUM()` in MySQL, and no per-currency exponent handling in reports.

The rounding boundary is explicit. `Money::roundToCurrency()` is applied when a customer-facing amount is produced. Gateway adapters convert with `toGatewayAmount()`, which asserts there is no fractional residue.

Other numeric types: exchange rates `DECIMAL(24,12)`; percentages `DECIMAL(7,4)`. Rounding is `HALF_UP` unless a currency's rounding rule says otherwise.

Gateway minor units are per gateway because the docs differ: `payment_gateway_currencies.minor_exponent` (e.g. HUF and TWD have no decimals on PayPal; ISK and UGX must be sent to Stripe as two-decimal values ending in `00`).

### 11.2 Base, display and payment currency

| Concept | Purpose | Set by |
|---|---|---|
| Base currency | All pricing computation. `settings.currency.base`, seeded USD. | Admin; changing it after go-live is unsupported |
| Display currency | What the customer browses in | Customer choice, cookie, geo default |
| Payment currency | What the gateway actually charges | `PaymentCurrencyResolver` |

`PaymentCurrencyResolver`: if the chosen gateway supports the display currency for this merchant account, use it; otherwise the gateway's configured `default_currency`. The payment amount is computed from the base amount using the rate for the payment currency. Where display and payment currency match, the charged amount equals the displayed amount exactly, because it is computed once.

Checkout must show display price, actual payment amount and actual payment currency whenever the two differ.

### 11.3 Snapshot

Every order stores: `base_currency`, `subtotal_base`, `discount_base`, `tax_base`, `total_base`, `display_currency`, `display_rate`, `display_total`, `payment_currency`, `payment_rate`, `payment_total`, `payment_gateway_id`, `rate_source`, `rate_fetched_at`, `exchange_rate_history_id`. Each order item stores unit base price and the pricing breakdown. Each payment attempt stores its own currency, rate and amount.

Historical values are never recomputed. A retry with a different gateway updates the order's payment fields until `paid`; they are frozen from then on.

How M7 takes the snapshot, once, inside the placement transaction:

- The pricing engine is called with `forCheckout: true`. `PaymentCurrencyResolver` (contract in `Payments/Contracts`, implemented by M10) returns only the gateway and currency.
- If the payment currency equals the display currency, `payment_rate` and `payment_total` are the display rate and the display total exactly. Otherwise `payment_total` is the base total converted once with a rate that passes the checkout freshness check.
- `rate_source`, `rate_fetched_at` and `exchange_rate_history_id` describe the payment rate, or the display rate when the payment currency is the base currency. Both are base-identity (`base`, null, null) when everything is in the base currency. Each item's `pricing_breakdown` also keeps the display rate and its history id.
- `total_base` is unrounded (six places); the display and payment totals are sums of per-unit amounts rounded by the currency's rule, so they are what the customer saw. The customer must confirm them: `place-order` takes `currency` and `expected_total` and refuses with `price_changed` if either no longer matches.

### 11.4 Rates

Effective rate = `provider_rate * (1 + markup_pct)`, unless a manual override is active, which replaces it.

| Control | Rule |
|---|---|
| Source | `ExchangeRateProvider` contract; Open Exchange Rates adapter first |
| History | Each fetch appends to `exchange_rate_history` |
| Cache | Redis `fx:current`, 1 hour, cleared on update or override |
| Spike guard | A change above `max_change_pct` (default 10%) from the previous rate is held pending approval; the old rate stays |
| Staleness | Older than `max_age_minutes` (default 1440): display continues with a "may vary" note, **checkout in that currency is blocked**, falling back to the base currency |
| No hardcoding | No rates in code or seeders (rule 6). Seeders create currencies without rates; first fetch or an admin override supplies them. |
| Held rates | A held rate does not refresh `fetched_at`, so a rate left unapproved goes stale and blocks checkout. Held rates are recorded in `pending_rate` and the audit log, not in `exchange_rate_history`, because orders snapshot history rows. A repeat of the same provider timestamp is ignored. |
| Manual overrides | An active override is never stale: it is a deliberate staff decision and the fallback when the provider is down. `manual_expires_at` passing ends it on next read or fetch, restoring the provider rate. |
| Fallback at checkout (M7) | Checkout never switches currency silently. `POST /checkout/validate` and `place-order` refuse with `rate_stale` (or `rate_missing`); the cart shows `price_may_vary` and `checkout_ready: false`. The customer falls back by choosing the base currency, whose rate cannot be stale. A stale rate for a payment currency that differs from the display currency refuses placement the same way. |

### 11.5 Pricing rule precedence

Computed in the base currency by `PricingEngine`. Markup rules do not stack: the most specific rule wins.

1. **Cost**: selected primary mapping's cost converted to base (or `product_variants.manual_cost_base`).
2. **Explicit fixed price**: a `variant_prices` row for (variant, country, currency) beats (variant, currency), beats (variant, country). Explicit prices skip steps 3, 7 and any currency markup.
3. **Markup rule**, by scope: Variant > Product > Platform > Category > Global. Within a scope: rule with country and currency, then country, then currency, then neither. Then lower `priority` number. Then newest. Exactly one rule applies; it may be fixed, percentage, or both.
4. **Dynamic rules** (type `dynamic`, conditions as JSON such as stock level or supplier cost band) are evaluated at their own scope position in step 3.
5. **Promotion**: best single eligible promotion. Stacks only when `promotions.stackable` is true.
6. **Coupon**: cart level, after promotions. At most one, unless `stackable`. Allocation over units: ADR-025.
7. **Margin guard**: see below.
8. **Currency conversion** and per-currency rounding (`currencies.rounding_rule`).

**Margin guard**: net margin is estimated after supplier cost, default gateway fee and conversion cost. If below `min_margin` (most specific of variant, product, category, global), behaviour follows `below_margin_policy`: `floor` (raise the price to the floor), `block` (not purchasable), or `allow`. `allow`, and any admin price below the floor, need `pricing.override_margin` and create an audit entry with a reason. A coupon may not take an item below the floor unless the coupon has `allow_below_margin`.

Settled at M8 (inherited from M7): `PricingContext.paymentCurrency` adds the conversion cost to the floor when the gateway charges a non-base currency, even when the display currency is the base. `place-order` prices the cart again with the resolved payment currency and refuses with `payment_currency_below_margin` when any unit would then fall below its floor or change price; it never changes the confirmed price. Showing the per-gateway price before placement is M10 (`GET /checkout/payment-options` must pass the payment currency).

Clarifications settled at M6:

- Explicit prices skip the margin guard's floor, block and allow outcomes, but promotions and coupons on them still may not cross the floor. The price itself is never raised, only the discount limited.
- On the markup path the guard runs after promotions and the coupon is clamped to the floor afterwards (unless `allow_below_margin`), so a large coupon limits itself rather than making the item unpurchasable under `block`.
- Costs and explicit-price conversions use the rate without currency markup.
- Minimum margin is not resolved at platform scope, so setting one on a platform rule is rejected rather than ignored. Variants with no minimum configured fall back to `settings.pricing.default_*`, whose default is break-even with policy `block`.
- Where several gateway fee rules match, the most expensive is assumed; with none, `settings.pricing.default_gateway_fee_*` applies.
- With a price whose cost is unknown, staff need `pricing.override_margin` because the margin cannot be verified.

Listing price is stored on `product_variants.price_base` by `pricing:recalculate-dirty`, so listings and filters do not run the engine per row. The product page and checkout always re-evaluate.

Actual profit is computed at order completion into `order_item_financials`: revenue minus actual supplier cost, gateway fee, conversion cost, other configured costs. Gateway fee uses the provider figure where available and `payment_gateway_fee_rules` as the estimate otherwise.

---

## 12. Payment eligibility

`PaymentEligibilityService::eligibleGateways(EligibilityContext)`. Context: declared country, IP country, currency, amount, customer status, guest flag, risk level, cart products. A gateway is offered only if **all** of these pass.

| Check | Source |
|---|---|
| Gateway active and credentials present for the current mode | `payment_gateways`, `payment_gateway_credentials` |
| Account capability: currency enabled for this merchant | `payment_gateway_currencies` (admin-maintained, not assumed) |
| A payment currency is resolvable, and its rate is fresh | `PaymentCurrencyResolver`, section 11 |
| Amount within per-currency min and max | `payment_gateway_currencies` |
| Country rules | `payment_eligibility_rules` |
| Risk ceiling and guest allowance | rules |
| No cart product excludes this gateway | `payment_gateway_product_exclusions` |

`payment_eligibility_rules` are evaluated by `priority`; each is `allow` or `deny` with conditions on declared country, IP country, currency, amount, guest and risk. The default with no matching rule is **deny**.

Seeded rules:

| Gateway | Rule |
|---|---|
| Paymob | Allow when declared country = EG **and** IP country = EG **and** payment currency = EGP. An admin may relax the IP check. |
| Stripe | Allow when declared country is not EG (configurable), subject to account currencies |
| PayPal | Allow when declared country is not EG (configurable); currency limited to PayPal's list (excludes EGP, SAR, AED; see `INTEGRATIONS.md`) |
| Wallet | Allow for logged-in customers with sufficient balance |

Country alone never grants eligibility. Eligibility is re-evaluated server-side at place-order; the client-side list is advisory only.

Settled at M10 (`PaymentEligibilityService`, bound through `EligiblePaymentCurrencyResolver`): rules are evaluated in `priority` order and the first matching rule decides; `currencies` match the payment currency; an unknown IP country never matches `ip_countries` or `require_ip_match`; `allow_guest = false` makes a rule apply to signed-in customers only; a `blocked` customer is refused everywhere. The adapter's own currency list is intersected with `payment_gateway_currencies`, so PayPal can never be given EGP, SAR or AED even if a merchant row enables them. `payment_gateways.settings.charge_display_currency_only` (seeded for Paymob) refuses a gateway unless it charges the display currency itself. IP country comes from the `GeoIpResolver` contract (ADR-016): `NullGeoIpResolver` by default, `HeaderGeoIpResolver` for a trusted edge header; GeoLite2 is not built because it needs `geoip2/geoip2` and a MaxMind database.

---

## 13. Checkout flow

1. `POST /checkout/validate`: reprice, revalidate stock, delivery fields, coupon.
2. `GET /checkout/payment-options?country=`: eligible gateways, with display and payment amounts.
3. `POST /checkout/place-order` with `Idempotency-Key`: re-run eligibility and pricing; create order, items, units, snapshots in one transaction; status `pending_payment`.
4. Create the payment attempt (HTTP outside the transaction). Stripe returns `client_secret`; PayPal `id` and approve link; Paymob `client_secret` and Unified Checkout URL.
5. Customer pays on the provider's UI. The return page polls `GET /checkout/orders/{public_id}/status`.
6. Webhook or capture verifies payment; state machine moves to `paid`; `FulfillOrder` runs.

Cart prices are re-computed at each step; the customer sees a notice if a price changed. Guests must verify email by OTP before revealing codes; email is captured before payment.

Built in M7: steps 1, 3 (order creation only; the payment attempt of step 4 is M10) and the status read of step 5, plus guest email verification by OTP. Step 2 (`payment-options`) needs eligibility and is M10. Place-order runs: idempotency claim; unlocked read of the cart for the placement hash; then one transaction that locks the cart, rejects a converted cart or an open order with the same placement hash, reprices with `forCheckout: true`, revalidates lines, delivery values and the coupon, checks the confirmed total, resolves the payment currency, opens the order, writes items, units and delivery values, reserves the coupon, converts the cart and stores the idempotent response.

---

## 14. Wallet

Ledger model. `customer_wallets` is a single row per user in the base currency, holding the running balance; `wallet_transactions` is append-only with a unique `idempotency_key` and `balance_after`. Mutations lock the wallet row. Balance updates and the ledger insert occur in one transaction. Scope: credits and refunds only, no customer top-up (BD3, confirmed).

Built at M11 (`Wallet\Services\WalletLedger`): credits (`credit_refund`, `credit_admin`) add, debits (`debit_purchase`, `debit_admin`) and `reversal` subtract; a repeated key returns the first entry, a key reused for another movement is refused; the balance never goes below zero (`chk_cw_balance_nonneg` as a backstop); a frozen wallet refuses debits but accepts refunds; `wallet:verify-ledger` (daily) alerts on any wallet whose balance differs from the sum of its entries. The wallet is a payment gateway of its own kind (ADR-030): `WalletPaymentGateway` implements `InternalLedgerGateway`; eligibility requires a signed-in, verified, active customer whose balance covers the payment amount, and no credentials; it charges the base currency only. Refunds of wallet payments, and staff refunds with destination `wallet`, are keyed ledger credits.

## 15. Risk and fraud

`RiskEngine` scores each order after payment and before fulfilment, from weighted signals: account age, first-order amount, declared/IP/card-country mismatch, velocity per user, IP, email and card fingerprint, failed attempt count, disposable email domain, order value versus history. Score thresholds (`settings.security.risk`): below `review` proceed; between `review` and `block` hold for `manual_review`; above `block` refund and flag. Results are stored in `risk_assessments` with the signals that fired.

## 16. Notifications and integration events

Laravel sends all customer transactional notifications. Domain events are additionally written to `automation_outbox` in the same transaction as the state change, and `automation:flush-outbox` delivers them to n8n with HMAC and retry. n8n failure cannot affect an order. Event payloads contain IDs and non-sensitive fields only.

Built in M17: `AutomationOutboxWriter` is called by `OrderStateMachine` (`order.created` on open; `payment.succeeded`, `order.delivered`, `order.failed` on those transitions) and `SupportTicketService` (`ticket.updated` on a public staff reply or a status change), inside their transactions, when `AUTOMATION_OUTBOX_ENABLED=true`. `AutomationOutboxDispatcher` leases due events with `SKIP LOCKED`, sends them outside any transaction, and retries after 1, 5, 15, 60, 180 and 360 minutes before marking them failed. The n8n customer workflows add SMS only; who may be contacted is decided by `CustomerContactPolicy`.

---

## 17. Testing approach

Details per milestone are in `IMPLEMENTATION_ROADMAP.md`. Tests run on MySQL database `gaming-store_testing`, because pdo_sqlite is unavailable and because locking and generated columns must be exercised for real. Provider calls use `Http::fake()` with fixtures taken from official spec examples. Every provider test file states "mocked; awaiting sandbox verification". `Http::preventStrayRequests()` is global.

Mandatory scenarios for R1 and R2: read timeout then no retry; crash after submit; redelivered job; concurrent submit (two connections); duplicate and out-of-order webhooks; webhook with bad signature; amount mismatch; late payment on a cancelled order; deliverable fetch failure after purchase; refund timeout.

---

## 18. Architecture Decision Records

| ADR | Decision | Reason | Consequence |
|---|---|---|---|
| ADR-001 | Modular monolith under `app/Domains` | One team, transactional boundaries matter; microservices add distributed-transaction risk with no benefit | Enforced by arch tests |
| ADR-002 | Blade + vanilla JS, no Livewire or Inertia | Spec rule 2; SSR is required for SEO | Admin is server-rendered pages calling JSON endpoints |
| ADR-003 | Repositories only where query logic exists | Spec: avoid unnecessary abstraction | Section 3 lists the line |
| ADR-004 | `DECIMAL(20,6)` for all money | Reloadly's 5-dp cost data; one representation; exact SQL sums | bcmath `Money` required; gateway adapters convert |
| ADR-005 | Status columns are `VARCHAR` backed by PHP enums, not MySQL `ENUM` | Adding a status needs no `ALTER` on large tables | Validity enforced in code and casts |
| ADR-006 | One supplier request per unit | Partial failure, per-unit refund, per-request cap | More API calls; acceptable at expected quantities (max quantity per line 10) |
| ADR-007 | Default-deny outcome classification; ambiguous never retried | Spec Phase 5; a duplicate purchase is unrecoverable | Some extra manual review |
| ADR-008 | MySQL row locks and unique keys are the safety mechanism; Redis locks only reduce contention | Redis locks can be lost on failover or expiry | Lock order documented |
| ADR-009 | Emails carry a secure link, not the code | Mailboxes and mail logs are outside our control; spec requires codes only reach authorized customers | One extra click for the customer |
| ADR-010 | Separate keyring and blind index key for codes | Compromise or rotation of `APP_KEY` must not expose codes | Extra secrets to back up |
| ADR-011 | Webhook payloads are triggers; state is re-read from the provider | Spec Phase 13; handles spoofing, reorder, duplicates | One more API call per event |
| ADR-012 | Supervisor + `queue:work`, not Horizon | pcntl not available; spec names Supervisor | Monitoring via `queue:monitor`, `failed_jobs`, F02 workflow |
| ADR-013 | Laravel owns transactional notifications; n8n handles secondary channels | Spec: n8n must not replace transactional logic | D01-D05 workflows do not deliver codes |
| ADR-014 | Scheduler is the default driver for sync and rates; n8n can take over per task | Prevents double runs; system still works if n8n is down | `settings.automation.task_drivers` |
| ADR-015 | Translations via a polymorphic `content_translations` table, English base columns | Avoids a table per entity; JSON columns cannot be FULLTEXT-indexed | Search uses a denormalized `search_documents` |
| ADR-016 | IP country via a `GeoIpResolver` contract (GeoLite2 default, Cloudflare header if behind it) | Eligibility needs location not only declared country | MaxMind licence key needed |
| ADR-017 | Staff and customers share `users`, split by `type` and roles | Spatie permissions and auth stay uniform | Admin routes gate on `type=staff` and permissions |
| ADR-018 | Orders use ULID `public_id` in URLs | Prevents enumeration | Second identifier column |
| ADR-019 | Admin JSON uses session + CSRF under `/admin/api`; Sanctum tokens only for service clients (and, since ADR-035, for customers of the mobile app) | Same-origin Blade pages need no tokens; avoids tokens in browser storage | Two auth paths, both tested |
| ADR-020 | MCP writes need out-of-band human approval, not a model-callable confirm tool | A confirm tool can be called by the agent itself | `MCP_SERVER.md` |
| ADR-021 | FAQ markup off by default | Google removed FAQ rich results (May to June 2026) | `SEO_STRATEGY.md` |
| ADR-022 | Orders carry `tax_base` columns but there is no tax engine yet | Spec is silent on tax; adding columns later touches financial tables | Business decision required before EU or Egypt VAT obligations apply |
| ADR-023 | Stripe API version pinned in config (`2026-10-28.endive` at time of writing) | Webhook and response shapes vary by version | Upgrades are deliberate |
| ADR-024 | Base `@modelcontextprotocol/sdk` 1.x pinned for the MCP server | v2 scoped packages are new and the docs are split between eras | Revisited at M16: kept 1.x, pinned exactly at 1.32.2 (latest 1.x on 2026-11-10) with zod 4.6.7 |
| ADR-025 | Cart-level coupons are computed once on the cart and allocated over units | The engine prices one unit; a coupon is a cart promise. Per-unit coupon maths cannot honour a fixed amount off the cart, and rounding per unit makes totals drift | See the allocation rule below |
| ADR-026 | Coupon validity (M11) and payment currency (M10) are consumed through narrow contracts, bound to fail-closed implementations until those milestones | Building a throwaway coupon system or currency resolver would be a fake integration; a contract lets M7 be complete and tested with doubles under `tests/` | Until M10 binds `PaymentCurrencyResolver`, every `place-order` returns `gateway_not_eligible`; until M11 binds `CheckoutCoupons`, every code returns `coupon_invalid` |
| ADR-027 | Idempotency claims are committed before the work and fenced by their lock time; the response is stored inside the work's transaction | A claim inside the work's transaction is invisible to a concurrent request, which would then wait or duplicate; a response stored outside it could outlive a rolled-back order | A crashed request leaves a claim that blocks its key for 120 s, then any retry takes it over |
| ADR-028 | PayPal webhooks are verified with the `verify-webhook-signature` API, not offline certificate checks | No certificate fetching, caching or host allow-listing to get wrong; the PM chose it for M10 | One PayPal call per event; mock events from the dashboard cannot be verified; an unreachable API returns 503 so PayPal retries |
| ADR-029 | Stripe calls go through the official SDK on a Laravel HTTP transport (`LaravelStripeHttpClient`) | SDK request building and webhook verification, with the same timeouts, `preventStrayRequests` and fakes as every other provider | SDK network retries are disabled; a lost response is re-read |
| ADR-030 | The wallet is an internal-ledger payment gateway: the attempt is settled by debiting the ledger and applying the result through `PaymentStateApplier` in the same transaction, under `orders`, `payments`, `customer_wallets` | Keeps one path to `paid` (rule 9 and M10) without inventing a provider; the ledger debit is the server-side verification | No webhooks; nothing can mark a wallet payment paid except a debit that committed with it; `fetchState` re-reads the ledger for reconciliation |
| ADR-031 | The MCP server uses the SDK's low-level `Server` with its own `tools/list` and `tools/call` handlers, not `McpServer` | `McpServer` validates input before the handler and returns schema failures as plain text; the spec requires structured `invalid_input` errors. JSON Schemas are generated from the same zod schemas | `Server` is marked "advanced use" (deprecated for general use) in SDK 1.x; revisit if a later SDK lets `McpServer` return custom validation errors |
| ADR-032 | MCP elicitation approval (MCP_SERVER.md 5, path 3b) uses a single-use confirmation token returned only to the MCP server, stored as a SHA-256 hash, bound to the requesting Sanctum token, cleared on use and expiring with the action; tier 2 and accounts without `actions:confirm` use admin approval only | Lets a human approve tier 1 changes in the client without giving the model a callable confirm; the server keeps the token out of tool results | A local process holding the API token can still confirm its own tier 1 actions, hence `actions:confirm` is off by default |
| ADR-033 | n8n requests are signed over timestamp, nonce, method, path with query, workflow id, execution key and raw body, with a per-account secret; the nonce is required | A signature over `timestamp.body` alone is the same for every bodiless GET in a second and does not bind the endpoint or workflow; an explicit nonce makes replay refusal exact | n8n retries resend the same nonce, so a retry after the first copy arrived is refused; the workflow fails into Z01 and the next window runs (M17) |
| ADR-034 | The only secondary customer channel is SMS, opt-in per category for order and support updates, consent-based for marketing | `notification_preferences` and `customer_consents` already model this; email stays with Laravel (ADR-013) | No customer gets an n8n message until they opt in; guests never do (M17) |
| ADR-035 | Mobile app sign-in (M-A2) uses a short-lived Sanctum personal access token with the exact ability `customer` (per-token `expires_at`, default 15 min) plus an opaque, single-use refresh token stored as a SHA-256 hash in `mobile_refresh_tokens`, grouped in a family per sign-in with an absolute expiry (default 30 days) that rotation never extends; presenting a used refresh token revokes the family and its access tokens and is audited (`auth.mobile_refresh_token_reused`) with a security event. The two-factor pending state is a hashed, single-use, attempt-limited row in `mobile_login_challenges` (default 300 s). Credentials, status checks and the refused-login error are `AuthenticationService::verifyCredentials`, shared with the web; staff are refused like a wrong password. The `sanctum` guard is bound to the `users` provider, so a service-account token can never authenticate as a customer, and the MCP and automation middlewares accept only `ServiceAccount` tokens, so a customer token can never reach them; mobile routes also require a bearer token (not a session) with the exact `customer` ability (`EnsureCustomerToken`) | Tokens in secure device storage need a revocable, rotating credential; a long-lived access token cannot be revoked quickly enough when stolen, and reuse detection turns refresh-token theft into a visible, contained event. A session has no place in a native app (ADR-019 keeps sessions for same-origin pages). Sanctum is already installed, so no new dependency | Strict single use: two concurrent refreshes with one token sign the device out (the app refreshes single-flight); "new device" is judged by `device_name` within the refresh-token retention, so a renamed or long-unused device is audited as new; the account, cart and checkout routes accept bearer tokens only from M-A3 |
| ADR-036 | Stateless clients (M-A3) reach the cart, checkout and account through a parallel `/api/v1/mobile` group that **re-registers the same route files** (`routes/checkout.php`, `routes/account.php`) in the stateless `api` group, with the same controllers, services, Form Requests, Resources and route limiters: checkout and cart behind `AuthenticateOptionalCustomerToken` (no `Authorization` header = guest; any header must be a valid customer token, else 401), account behind `mobile.account` (`auth:sanctum`, `EnsureCustomerToken`, `account.active`). Controllers branch on `$request->hasSession()`: the browser keeps the cookie, session lists and session proofs; a stateless client uses headers: `X-Cart-Token` (the existing 64-character cart token, stored only as its SHA-256 in `carts.session_token`, returned once in the `X-Cart-Token` header and `meta.cart_token`), `X-Currency` (overrides the cookie, 422 unless an active currency), `X-Guest-Order-Token` (a per-order opaque token in `guest_order_tokens`, SHA-256 at rest, `orders.guest_order_token_ttl_hours`, default 24; issued by place-order including replays (`placement`, opens the order), by OTP verify (`otp`, always a new token that carries the link proof of the presented one, which is deleted) and by the signed delivery link (`link`, proof only, opens nothing); status, verify, capture and retry need a token that opens that guest order; a guest reveal needs both the link and the OTP proof on one live token, exactly the two session proofs of the web) and recent authentication stored per access token (`personal_access_tokens.password_confirmed_at`, same window as the session, carried to the successor token on refresh without extending it). The signed delivery link is verified against the canonical web URL on both routes (`ValidateGuestAccessSignature`). The `checkout` limiter keys a stateless request by user, bearer token, cart token or guest order token. Locale: `SetLocaleFromRequest` on the `api` group, the web checkout routes and the `account` group; session requests use the customer's saved `users.locale`, then `Accept-Language`, then `en`; stateless requests use `Accept-Language`, then `en`; storefront resources read `content_translations` through a request-scoped `ContentTranslator` (one query per page, none in English) with the English column as fallback; envelope messages are English source strings translated by `lang/ar.json`, validation by `lang/ar/validation.php`; codes never change | One set of routes and controllers serves both clients, so behaviour cannot drift. Adding bearer support to the web routes would need a CSRF exemption for requests with an `Authorization` header, and because the `sanctum` guard checks the web session first, a forged request carrying any `Authorization` header plus the victim's cookie would pass as the victim: the parallel stateless group keeps CSRF and session protection intact for the web. A hashed, order-scoped, expiring token is the stateless twin of the session lists without weakening the OTP and signed-link rules | Two URL prefixes per route (names `api.v1.*` and `mobile.*`); the affiliate click route moved to `routes/affiliates.php` because it only makes sense with a browser cookie; a stateless client keeps one guest order token per order and must replace it with the token returned by OTP verify; recent auth is per device, so a second device confirms on its own |
| ADR-037 | Catalog discovery and app config (M-A4). (1) The public storefront reads (catalog, product detail, search, currencies, home, platforms, categories, countries) live in `routes/storefront.php`, registered twice in the stateless `api` group (`/api/v1`, names `api.v1.*`, and `/api/v1/mobile`, names `mobile.*`), as ADR-036 does for checkout; the cookie-setting `POST /storefront/currency` stays web-only in `routes/api.php`. (2) A product's delivery fields are one list of `DeliveryFieldSpec` DTOs built by `DeliveryFieldValidator::specsFor`; `validate()` (cart and checkout) iterates that list and the product endpoint serializes the same list, so the published definition is the enforced rule. (3) Storefront images are absolute: `MediaUrls` uses `catalog.media.base_url` (`MEDIA_BASE_URL`, a CDN) or the media row's disk URL made absolute with `app.url`; `image.path` stays for existing clients and `image.url` is added. (4) Publishable payment identifiers (Stripe `publishable_key`, PayPal `client_id`, Paymob `public_key`, per `payments.public_credentials`) are kept in the gateway's encrypted credentials for each mode, next to the secrets, and `PaymentPublicCredentials` is the only reader for publication: it returns only the allow-listed field of active gateways, refuses values shaped like secrets (`sk_`, `rk_`, `whsec_`) and values whose Stripe prefix does not match the gateway mode (published as null). They are set with `php artisan payments:public-key` until the M15 credential screens exist. (5) App settings (versions, maintenance, feature flags, legal URLs) are the settings group `app` with `config('mobile.app')` defaults, edited with `php artisan mobile:app-config`; versions are SemVer (`SemanticVersion`). (6) Home links are structured `{type, value, url}` (`LinkTargetResolver` maps stored web paths per `SEO_STRATEGY.md` section 2). (7) These GETs carry `Cache-Control: public, max-age=N`, an ETag and `Vary` on `Accept-Language` (plus `X-Currency, Cookie` when priced) through `PublicCacheHeaders` | One route file and one controller set cannot drift between web and app; a single delivery-field spec makes client validation provably equal to the server's; keeping the publishable key beside the secrets of the same mode means a test key can never pair with live secrets and needs no new storage, while the narrow reader keeps decrypted secrets out of the response path; settings with config defaults let operators change versions and maintenance without a deploy | Two URL prefixes for the storefront reads; a publishable key can be set only for a mode that already has credentials (a key alone would make the gateway look configured to eligibility); no admin screen yet for app settings or public keys (commands only); the cache is short (60 s for priced content, 300 s for taxonomy, 3600 s for countries), so admin changes appear within that window |
| ADR-038 | Account self-service (M-A5). (1) Orders: `GET /account/orders` and `/orders/{public_id}` in `routes/account.php` (web and mobile), ownership by `OrderPolicy` (registered with `Gate::policy`; deny as not found), reads in `CustomerOrderRepository`, the `revealable` hint from `CodeRevealService::isRevealable`, which shares its paid and delivered checks with the reveal gate. (2) Guest order detail: `GET /checkout/guest/orders/{public_id}` in `routes/checkout.php` with the **status proof**, not the reveal proof: the placing or OTP-verified browser session, or a placement or OTP guest order token; `meta.guest_access` reports the reveal proofs. (3) Devices are mobile refresh-token families plus, only with `SESSION_DRIVER=database`, browser sessions identified by an HMAC of the session id. (4) Password change keeps only the caller (its family and access token, or its session), revokes every other family (`password_changed`), deletes other database sessions and rotates the remember-me token; password reset signs out everything (`password_reset`). (5) Inbox: the customer notifications choose `mail` and `database` per `NotificationPreferenceService`; `database` rows store a kind, the number and a structured `{type, value}` link, and text is rendered in the reader's language at read time. `orders.mail` and `security.mail` are mandatory; without a row mail and inbox are on for orders, support and security, SMS is off and marketing follows consent. (6) Email links: verification relies on the signed URL alone (signature, id and hash of the current address) and redirects a browser to `auth.links.email_verified_url`; the reset link redirects to `auth.links.password_reset_url` when set, otherwise keeps its JSON | One route file and controller set for web and app, as ADR-036. A policy keeps object-level authorization declarative and testable. Viewing a guest order discloses no secret (codes stay behind the two-proof reveal), so it uses the same proof as status and payment, which the app already holds after placement. Revoking other devices on a password change is the expected outcome of changing a password; keeping the caller avoids signing the user out of the device they are using. Rendering inbox text at read time makes it follow the reader's language and keeps payloads free of anything but identifiers. A configured redirect avoids building UI before the storefront design is approved (M12) and lets the same email link open the app | Browser sessions cannot be listed with a non-database session driver (then `meta.web_sessions_listed` is false and a password change cannot sign other browsers out except through the rotated remember-me token); a stateless client still chooses its language with `Accept-Language`; until `PASSWORD_RESET_URL` is set, the reset link returns JSON; a support email switched off by the customer is not sent (order and security emails always are) |

**ADR-025 allocation rule.** (1) Quote each variant once without a coupon (`PricingContext.coupon` is null at checkout). (2) The coupon contract returns `CouponTerms` and, optionally, the eligible variants. (3) The discount D is computed once on the eligible subtotal by `DiscountCalculator::couponDiscount` (percent, or fixed in base currency, capped by `max_discount_base` and the subtotal) and rounded HALF_UP to the base currency's minor unit. (4) Each purchased unit is a slot: weight = its pre-coupon base price (zero if ineligible); room = `MarginGuard::discountRoom` = price minus floor, or the whole price when the coupon has `allow_below_margin` or the floor is unknown; never negative, and rounded down to the minor unit. (5) `CartDiscountAllocator` splits D proportionally to weight in whole minor units. A slot whose share reaches its room takes exactly its room and the rest is shared again over slots with room left; whatever no slot can take is forfeited and reported as `limited_by_margin`. (6) Remainders: each slot first takes the whole minor units of its share, then the leftover minor units go one at a time to the largest fractional parts, ties to the earliest slot. Slot order is cart item id, then unit index, so the same cart always allocates the same way and the allocations sum exactly to the discount placed. (7) Each unit's net base price is converted to the display currency on its own (explicit prices without currency markup, as in the engine), so the margin guard has evaluated the post-allocation unit price and no unit is below its floor unless the coupon allows it. Each unit's allocation is stored in `order_items.pricing_breakdown.units`.
