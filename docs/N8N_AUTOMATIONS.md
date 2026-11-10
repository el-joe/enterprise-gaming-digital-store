# n8n Automations

Status: **built in M17: the Laravel automation API, outbox and heartbeats are built and tested; all 58 workflows are authored and validated, not executed.** n8n is not installed on the development machine, so no workflow has run. Each workflow JSON (`/n8n/workflows`) and document (`/n8n/documentation`) says so, and `node n8n/scripts/validate-workflows.mjs` (also run by the Laravel suite) checks them statically. Treat the first import on a real host as the first test.

PM scope decisions for M17 (recorded in `IMPLEMENTATION_ROADMAP.md`): M17 ran before M14; credentials are managed by `php artisan automation:credentials` and `/admin/api/automation-credentials` (the screen is M15). The alert channel (BD9) is open, so every alert goes through the Z02 sub-workflow, which picks Slack or Telegram from `EGS_ALERT_CHANNEL`; no workflow names a channel.

## 1. Principles

1. **Laravel is the source of truth.** n8n holds no business state, does not touch MySQL, and reaches data only through `/api/v1/automation`.
2. n8n orchestrates and notifies. It never decides payment, fulfilment or refund outcomes.
3. n8n failure must not affect orders. Laravel sends all transactional notifications itself (ADR-013).
4. Digital codes, credentials and full payment data never pass through n8n.
5. Scheduled sync and rate jobs default to the Laravel Scheduler. n8n drives them only if `settings.automation.task_drivers.{task}=n8n`, with task `catalog_sync`, `exchange_rates` or `payment_reconciliation` (ADR-014); the schedule entry then skips, and otherwise the job endpoint refuses. A job is queued at most once per execution.
6. AI output always lands in `ai_suggestions` as `pending_review`. Approval creates a draft; publication is a further human act.
7. Customer messaging checks consent and preferences server-side; Laravel's segment endpoints return only eligible recipients.

## 2. Security and reliability

| Requirement | Mechanism (built) |
|---|---|
| Dedicated API credentials | `service_accounts` row of kind `n8n` with its own encrypted `hmac_secret`, and a Sanctum token with exact `automation:` scopes: `executions:write`, `reports:read`, `monitor:read`, `marketing:read` by default; `jobs:run`, `suggestions:write`, `customers:message` only when named. Issued by `automation:credentials issue` or `POST /admin/api/automation-credentials`; shown once |
| Request authentication | Bearer token **and** `X-Automation-Signature`: HMAC-SHA256 over timestamp, nonce, method, path with query, workflow id, execution key and raw body (API_DOCUMENTATION section 7). 5-minute window; `X-Automation-Nonce` stored in the cache for 15 minutes after the signature verifies, so a replay is refused |
| Workflow registry | `X-Workflow-Id` must be a seeded, **enabled** `automation_workflows` row (`php artisan automation:workflows enable ...`); disabling a row stops that workflow at the API |
| Inbound webhooks (Laravel to n8n) | Z03 webhook with header auth (`X-EGS-Webhook-Token`) and the raw body; a Code node verifies `X-Signature` = HMAC-SHA256 of `timestamp.raw_body` with `N8N_OUTBOUND_SECRET`, within 5 minutes |
| Execution logs | Every request writes an `automation_logs` row of kind `call`; `POST /executions` writes the row of kind `execution` at start and updates it at finish |
| Duplicate execution protection | Unique (`workflow_id`, execution key) over execution rows. The key for scheduled runs is the time window (`A01:2026-11-10`, `A03:2026-11-10T10:15`). A second start of a running or successful key returns `skipped_duplicate` and the workflow stops; a failed key may be restarted |
| Event deduplication | Z03 starts an execution keyed `Z03:<event_id>` and D01 to D05 one keyed `<ID>:<event_id>`, so a redelivered event is a duplicate |
| Retry policies | Laravel calls: 3 tries, 5 seconds apart (a retry reuses the nonce, so it only helps when the first copy never arrived). LLM calls: 2 tries. SMS sends: no retry (a retry could send twice); a failure is logged as `failed` |
| Error workflows | Every workflow except Z01 sets Z01 (`egsZ01automation`) as its error workflow. Z01 has none (it would be its own) |
| Notifications | Only through sub-workflow Z02; channel from `EGS_ALERT_CHANNEL` (`slack` or `telegram`) |
| Rate limiting | `automation` limiter per account (`service_accounts.rate_limit_per_minute`); SMS batches of 10 per second in D06 to D09 |
| Failure alerts | Z01 calls `POST /alerts` (log entry, `last_failure_at`, failed execution closed) and Z02, except for a Z02 failure (no loop) |

Credentials required in n8n: `LaravelAutomationApi`, `LaravelOutboundWebhook`, `ManualTriggerWebhook` (C09), `AlertSlack` or `AlertTelegram`, `CustomerSmsApi` (D workflows; provider not chosen) and `LlmProvider` (Anthropic; key not supplied yet). The two HMAC secrets are n8n environment variables because Code nodes cannot read credentials. See `/n8n/credentials.example.md` and `/n8n/README.md`.

## 3. Standard workflow shape

```
Trigger -> Prepare execution (key, signed start) -> POST /executions(start)
  -> IF duplicate: stop
  -> Sign -> HTTP Laravel -> Build message -> IF notify -> Z02
  -> Sign -> POST /executions(finish)
Error workflow: Z01
```

Repository layout: `/n8n/workflows/<category>/<ID>-<slug>.json`, `/n8n/documentation/<ID>-<slug>.md`, `/n8n/credentials.example.md`, `/n8n/README.md`, `/n8n/scripts/validate-workflows.mjs`. Category folders: `infrastructure`, `daily-operations`, `weekly-business-intelligence`, `smart-marketing`, `customer-automation`, `financial-monitoring`, `system-monitoring`. Workflow ids are fixed (`egs<ID>automation`) so error-workflow and sub-workflow references survive import.

Each document has: Purpose, Trigger, Required credentials, Nodes, Input, Output, Failure handling, Schedule, Import and activation.

Times are UTC. All endpoints are under `/api/v1/automation`. Event triggers are delivered from `automation_outbox` through `Z03`. Monitoring endpoints return `alert`, so thresholds live in Laravel. Where Laravel has no data it returns `unavailable` with a reason (section 6).

## 4. Inventory

### Infrastructure (added, required by the spec's "error workflows" and "notifications")

| ID | Name | Trigger | Laravel endpoint |
|---|---|---|---|
| Z01 | Global error handler | n8n Error Trigger | `POST /alerts`, `POST /executions` |
| Z02 | Send notification (sub-workflow) | Execute Workflow | none (Slack or Telegram by `EGS_ALERT_CHANNEL`) |
| Z03 | Laravel event router | Webhook `POST /webhook/egs-events` | none; routes by `event_type` to D workflows |

### A. Daily operations

| ID | Name | Trigger | Endpoint | Output |
|---|---|---|---|---|
| A01 | Daily Sales Report | Cron 06:00 | `GET /reports/sales?period=yesterday` | Report to channel |
| A02 | Daily Profit Report | Cron 06:05 | `GET /reports/profit?period=yesterday` | Report |
| A03 | Failed Orders Monitoring | Cron every 15 min | `GET /monitor/orders/failed?since=` | Alert if any |
| A04 | Pending Orders Monitoring | Cron every 15 min | `GET /monitor/orders/pending?older_than=60` | Alert on orders stuck in `pending_verification`, `manual_review`, `supplier_pending`, `processing`, `payment_processing` |
| A05 | Supplier Balance Monitoring | Cron hourly | `GET /monitor/suppliers/balances?level=warning` | Alert under each supplier's threshold |
| A06 | Supplier API Health Monitoring | Cron every 5 min | `GET /monitor/suppliers/health` | Alert on `down` |
| A07 | Payment Gateway Failure Monitoring | Cron every 15 min | `GET /monitor/payments/failures?window=60` | Alert on failure-rate spike |
| A08 | Low Margin Product Alerts | Cron 07:00 | `GET /monitor/products/low-margin` | Report |
| A09 | Catalog Synchronization | Cron 03:00 (if driver is n8n) | `POST /jobs/catalog-sync` | Run reference |
| A10 | Exchange Rate Updates | Cron hourly at :05 (if driver is n8n) | `POST /jobs/exchange-rates` | Run reference |
| A11 | Daily Revenue Comparison | Cron 06:10 | `GET /reports/revenue-comparison` | Report vs prior day and week |

### B. Weekly business intelligence

Cron Monday, every 5 minutes from 07:00 (B01 07:00 to B10 07:45). Window `period=last_week` (previous Monday to Sunday).

| ID | Name | Endpoint |
|---|---|---|
| B01 | Weekly Business Report | `GET /reports/weekly-business` |
| B02 | Best-Selling Games | `GET /reports/best-selling?by=game` |
| B03 | Best-Selling Platforms | `GET /reports/best-selling?by=platform` |
| B04 | Most Profitable Products | `GET /reports/profitable-products` |
| B05 | Underperforming Products | `GET /reports/underperforming-products` |
| B06 | Supplier Performance Comparison | `GET /reports/supplier-performance` |
| B07 | Payment Gateway Performance | `GET /reports/gateway-performance` |
| B08 | Customer Retention Analysis | `GET /reports/customer-retention` |
| B09 | Coupon Performance | `GET /reports/coupon-performance` |
| B10 | Pricing Optimization Recommendations | `GET /reports/pricing-recommendations`, `POST /suggestions` (type `pricing`, `pending_review`; no price changes) |

### C. Smart marketing

All use `LlmProvider` (Basic LLM Chain with the Anthropic Chat Model sub-node; the model id is `EGS_LLM_MODEL`). Every output is `POST /suggestions` as `pending_review`; approval creates a draft and publication is a further human act. Input is `GET /data/trending`, `GET /data/product-context/{id}` and (C08) `GET /data/products/missing-descriptions`, which are aggregated internal sales data and public catalog facts. External trend sources are not assumed (the data says `unavailable`); add one only with a confirmed API and licence.

| ID | Name | Trigger | Suggestion type |
|---|---|---|---|
| C01 | Discover Trending Gaming Products | Cron Mon 08:00 | `product` |
| C02 | Identify Popular Gaming Platforms | Cron Mon 08:10 | `product` |
| C03 | Suggest New Products | Cron Mon 08:20 | `product` |
| C04 | Generate Promotional Campaigns | Cron Tue 08:00 | `campaign` |
| C05 | Recommend Product Bundles | Cron Tue 08:10 | `bundle` |
| C06 | Suggest Coupon Campaigns | Cron Tue 08:20 | `coupon` |
| C07 | Generate SEO Blog Ideas | Cron Wed 08:00 | `blog_idea` |
| C08 | Generate SEO Descriptions | Cron Wed 08:10, products lacking descriptions | `seo_description` |
| C09 | Generate Product Marketing Content | Manual webhook `POST /webhook/egs-c09-marketing-content` `{product_id}`, header auth | `marketing_copy` |
| C10 | Prepare Social Media Content | Cron Thu 08:00 | `social_post` |

### D. Customer automation

D01 to D09 send SMS only, the one secondary channel (provider not chosen; credential `CustomerSmsApi`). The authoritative customer email is sent by Laravel. Laravel decides who may be contacted and writes the text: an active customer account with a phone number; for order and support updates (D01 to D05) an enabled SMS `notification_preferences` row for that category (opt-in); for marketing (D06 to D09) the latest `sms_marketing` consent must be a grant and no disabled marketing SMS preference. Guests are never contacted by n8n. Messages carry an order number and a link at most, never codes. Each send is logged with `POST /customers/messages/log`, whose dedupe key stops a repeat.

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| D01 | Order confirmation | Event `order.created` | `POST /customers/messages/prepare`, `POST /customers/messages/log` |
| D02 | Payment confirmation | Event `payment.succeeded` | same |
| D03 | Digital delivery notification | Event `order.delivered` | same |
| D04 | Failed order notification | Event `order.failed` | same |
| D05 | Customer support notification | Event `ticket.updated` | same |
| D06 | Abandoned cart reminders | Cron hourly at :20 | `GET /customers/segments/abandoned`, then log (sets `carts.reminder_sent_at`) |
| D07 | Review requests | Cron daily 10:00 | `GET /customers/segments/review-eligible` |
| D08 | Customer reactivation | Cron Wed 10:00 | `GET /customers/segments/reactivation` |
| D09 | Personalized recommendations | Cron Fri 10:00 | `GET /customers/segments/recommendations` |

### E. Financial monitoring

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| E01 | Daily revenue and expenses | Cron 06:15 | `GET /reports/expenses` |
| E02 | Payment reconciliation | Cron 02:45 | `GET /payments/reconciliation`; first `POST /jobs/reconcile-payments` when `EGS_N8N_DRIVES_PAYMENT_RECONCILIATION=true` and the Laravel driver is n8n |
| E03 | Supplier balance alerts | Cron every 30 min | `GET /monitor/suppliers/balances` (critical threshold; A05 is the warning threshold) |
| E04 | Gateway fee monitoring | Cron 06:20 | `GET /reports/gateway-fees` |
| E05 | Profit margin alerts | Cron hourly | `GET /monitor/margin-alerts?period=last_hour` (net margin below 0 %) |
| E06 | Unusual transaction monitoring | Cron every 15 min | `GET /reports/unusual-transactions?period=last_15_minutes` (risk `review`/`block`, disputes) |
| E07 | Refund monitoring | Cron hourly | `GET /reports/refunds?period=last_hour` |

### F. System monitoring

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| F01 | Website uptime | Cron every minute | `GET /up` (public) and homepage; no automation API calls (it probes Laravel itself); alerts on state change only |
| F02 | Queue failure monitoring | Cron every 5 min | `GET /monitor/queues` |
| F03 | Laravel exception monitoring | Cron every 5 min | `GET /monitor/exceptions` (returns `unavailable`: no exception store) |
| F04 | Scheduled task monitoring | Cron every 5 min | `GET /monitor/scheduler` (`scheduled_task_runs` heartbeats against each task's cron) |
| F05 | Database health | Cron every 5 min | `GET /monitor/database` (Laravel-computed summary: connections, slow count, replication; no query access) |
| F06 | API latency | Cron every 5 min | `GET /monitor/latency` (recorded supplier, automation and MCP durations; web latency `unavailable`) |
| F07 | Supplier API availability | Cron every 5 min | `GET /monitor/suppliers/health?detail=1` |
| F08 | Webhook delivery failures | Cron every 15 min | `GET /monitor/webhooks` |

Total: 55 workflows from the spec, plus 3 infrastructure workflows.

**Monitoring limitation:** F01 and Z01 run on n8n, so they cannot report n8n's or the host's own outage. An external uptime monitor is needed (see `DEPLOYMENT.md`).

## 5. Import and activate

Full steps, environment variables and the import order are in `/n8n/README.md`; per-workflow steps are in each document.

1. Install n8n on the VPS and configure the reverse proxy (`DEPLOYMENT.md`), with `NODE_FUNCTION_ALLOW_BUILTIN=crypto` and the `EGS_*` variables.
2. Create the credentials listed in `/n8n/credentials.example.md`.
3. Issue the Laravel credential: `php artisan automation:credentials issue n8n-main --scope=...`.
4. Import with `n8n import:workflow --separate --input=n8n/workflows/<category>/`, infrastructure first. Imported workflows are inactive; the error workflow (Z01) is already set by id.
5. Enable each workflow in Laravel: `php artisan automation:workflows enable <ID> ...`.
6. Run once manually against staging, then publish (`n8n publish:workflow --id=egs<ID>automation` on n8n 2.x).
7. Verify rows reach `automation_logs` (`call` per request, `execution` per run).

## 6. What Laravel returns as unavailable

| Endpoint | Unavailable part | Reason |
|---|---|---|
| `GET /monitor/exceptions` (F03) | everything | Exceptions go to log files only; no exception store or error tracker |
| `GET /monitor/latency` (F06) | storefront and admin request latency | Web request durations are not recorded |
| `GET /monitor/database` (F05) | replication | Single MySQL server |
| `GET /reports/expenses` (E01) | operating expenses | Not recorded in the store |
| `GET /reports/gateway-performance` (B07) | provider latency and decline reasons | Not stored per attempt |
| `GET /reports/customer-retention` (B08) | guest retention | Guest orders are not linked to accounts |
| `GET /reports/pricing-recommendations` (B10) | competitor prices, price elasticity | No licensed source; no price-change history against volume |
| `GET /data/trending` (C) | external market trends | No external source connected |
| `GET /reports/weekly-business` (B01) | visitor and cart conversion | No GA4 data until M18 |
