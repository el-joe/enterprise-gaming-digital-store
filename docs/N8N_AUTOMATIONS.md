# n8n Automations

Status: planned. n8n is not installed on the development machine: workflow JSON will be authored and statically validated, and cannot be executed here until a host exists. Each workflow must be labelled "not executed" until it has been run.

## 1. Principles

1. **Laravel is the source of truth.** n8n holds no business state, does not touch MySQL, and reaches data only through `/api/v1/automation`.
2. n8n orchestrates and notifies. It never decides payment, fulfilment or refund outcomes.
3. n8n failure must not affect orders. Laravel sends all transactional notifications itself (ADR-013).
4. Digital codes, credentials and full payment data never pass through n8n.
5. Scheduled sync and rate jobs default to the Laravel Scheduler. n8n drives them only if `settings.automation.task_drivers.{task}=n8n` (ADR-014). The endpoints are idempotent per time window either way.
6. AI output always lands in `ai_suggestions` as `pending_review`. Approval creates a draft; publication is a further human act.
7. Customer messaging checks consent and preferences server-side; Laravel's segment endpoints return only eligible recipients.

## 2. Security and reliability

| Requirement | Mechanism |
|---|---|
| Dedicated API credentials | `service_accounts` row of kind `n8n`, Sanctum token with `automation:*` scopes |
| Request authentication | Bearer token **and** `X-Automation-Signature` HMAC over `timestamp.raw_body`, 5-minute window, replay nonce in Redis |
| Inbound webhooks (Laravel to n8n) | n8n Webhook node with header auth; Code node verifies `X-Signature` HMAC with `N8N_OUTBOUND_SECRET` |
| Execution logs | `POST /executions` at start and finish, stored in `automation_logs` |
| Duplicate execution protection | `automation_logs` unique on (`workflow_id`, `execution_key`). The key for scheduled runs is the time window (`A01:2026-11-10`). A second start returns `skipped_duplicate`, and the workflow stops. |
| Event deduplication | `automation_outbox.event_id` is passed through; D workflows check it via their first node |
| Retry policies | HTTP nodes: 3 tries, 30 s apart. Writes only when the endpoint is idempotent. |
| Error workflows | Every workflow sets `Z01` as its Error Workflow |
| Notifications | Via sub-workflow `Z02` to the configured channel |
| Rate limiting | Per-account limiter in Laravel; n8n batches with Wait nodes |
| Failure alerts | `Z01` alerts and calls `POST /alerts` |

Credentials required in n8n: `LaravelAutomationApi` (HTTP Header Auth), `AutomationHmacSecret` (variable or credential), `AlertChannel` (SMTP, Slack or Telegram; a business choice), and `LlmProvider` for section C (Anthropic or OpenAI node; key not supplied yet).

## 3. Standard workflow shape

```
Trigger -> Set(workflow_id, execution_key) -> Sign -> POST /executions(start)
  -> IF duplicate: stop
  -> HTTP Laravel -> transform -> IF threshold -> Z02 notify
  -> POST /executions(finish)
Error Workflow: Z01
```

Repository layout: `/n8n/workflows/<category>/<ID>-<slug>.json`, `/n8n/documentation/<ID>.md`, `/n8n/credentials.example.md`.

Each `/n8n/documentation/<ID>.md` must have: Purpose, Trigger, Required credentials, Nodes, Input, Output, Failure handling, Schedule, How to import and activate.

Times are UTC. All endpoints are under `/api/v1/automation`. Event triggers are delivered from `automation_outbox` through `Z03`.

## 4. Inventory

### Infrastructure (added, required by the spec's "error workflows" and "notifications")

| ID | Name | Trigger | Laravel endpoint |
|---|---|---|---|
| Z01 | Global error handler | n8n Error Trigger | `POST /alerts`, `POST /executions` |
| Z02 | Send notification (sub-workflow) | Execute Workflow | none |
| Z03 | Laravel event router | Webhook `POST /webhook/egs-events` | none; routes by `event_type` to D workflows |

### A. Daily operations

| ID | Name | Trigger | Endpoint | Output |
|---|---|---|---|---|
| A01 | Daily Sales Report | Cron 06:00 | `GET /reports/sales?period=yesterday` | Report to channel |
| A02 | Daily Profit Report | Cron 06:05 | `GET /reports/profit?period=yesterday` | Report |
| A03 | Failed Orders Monitoring | Cron every 15 min | `GET /monitor/orders/failed?since=` | Alert if any |
| A04 | Pending Orders Monitoring | Cron every 15 min | `GET /monitor/orders/pending?older_than=` | Alert on stuck `pending_verification`, `manual_review` |
| A05 | Supplier Balance Monitoring | Cron hourly | `GET /monitor/suppliers/balances` | Alert under threshold |
| A06 | Supplier API Health Monitoring | Cron every 5 min | `GET /monitor/suppliers/health` | Alert on `down` |
| A07 | Payment Gateway Failure Monitoring | Cron every 15 min | `GET /monitor/payments/failures?window=60` | Alert on failure-rate spike |
| A08 | Low Margin Product Alerts | Cron 07:00 | `GET /monitor/products/low-margin` | Report |
| A09 | Catalog Synchronization | Cron 03:00 (if driver is n8n) | `POST /jobs/catalog-sync` | Run reference |
| A10 | Exchange Rate Updates | Cron hourly at :05 (if driver is n8n) | `POST /jobs/exchange-rates` | Run reference |
| A11 | Daily Revenue Comparison | Cron 06:10 | `GET /reports/revenue-comparison` | Report vs prior day and week |

### B. Weekly business intelligence

Cron Monday, staggered from 07:00.

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
| B10 | Pricing Optimization Recommendations | `GET /reports/pricing-recommendations`, `POST /suggestions` (type `pricing`) |

### C. Smart marketing

All use `LlmProvider`. Every output is `POST /suggestions` as `pending_review`. Input is `GET /data/trending` and `GET /data/product-context/{id}`, which are aggregated internal sales data. External trend sources are not assumed; add one only with a confirmed API and licence.

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
| C09 | Generate Product Marketing Content | Manual webhook, per product | `marketing_copy` |
| C10 | Prepare Social Media Content | Cron Thu 08:00 | `social_post` |

### D. Customer automation

D01 to D05 are secondary-channel fan-out. The authoritative customer email is sent by Laravel. Payloads have no codes.

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| D01 | Order confirmation | Event `order.created` | `POST /customers/messages/log` |
| D02 | Payment confirmation | Event `payment.succeeded` | same |
| D03 | Digital delivery notification | Event `order.delivered` | same |
| D04 | Failed order notification | Event `order.failed` | same |
| D05 | Customer support notification | Event `ticket.updated` | same |
| D06 | Abandoned cart reminders | Cron hourly | `GET /customers/segments/abandoned`, then log |
| D07 | Review requests | Cron daily 10:00 | `GET /customers/segments/review-eligible` |
| D08 | Customer reactivation | Cron Wed 10:00 | `GET /customers/segments/reactivation` |
| D09 | Personalized recommendations | Cron Fri 10:00 | `GET /customers/segments/recommendations` |

### E. Financial monitoring

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| E01 | Daily revenue and expenses | Cron 06:15 | `GET /reports/expenses` |
| E02 | Payment reconciliation | Cron 02:45 | `GET /payments/reconciliation` (or `POST /jobs/reconcile-payments` if driver is n8n) |
| E03 | Supplier balance alerts | Cron every 30 min | `GET /monitor/suppliers/balances` (critical threshold; A05 is the warning threshold) |
| E04 | Gateway fee monitoring | Cron 06:20 | `GET /reports/gateway-fees` |
| E05 | Profit margin alerts | Cron hourly | `GET /monitor/margin-alerts` |
| E06 | Unusual transaction monitoring | Cron every 15 min | `GET /reports/unusual-transactions` |
| E07 | Refund monitoring | Cron hourly | `GET /reports/refunds` |

### F. System monitoring

| ID | Name | Trigger | Endpoint |
|---|---|---|---|
| F01 | Website uptime | Cron every minute | `GET /up` (public) and homepage |
| F02 | Queue failure monitoring | Cron every 5 min | `GET /monitor/queues` |
| F03 | Laravel exception monitoring | Cron every 5 min | `GET /monitor/exceptions` |
| F04 | Scheduled task monitoring | Cron every 5 min | `GET /monitor/scheduler` |
| F05 | Database health | Cron every 5 min | `GET /monitor/database` (Laravel-computed summary: connections, slow count, replication; no query access) |
| F06 | API latency | Cron every 5 min | `GET /monitor/latency` |
| F07 | Supplier API availability | Cron every 5 min | `GET /monitor/suppliers/health?detail=1` |
| F08 | Webhook delivery failures | Cron every 15 min | `GET /monitor/webhooks` |

Total: 55 workflows from the spec, plus 3 infrastructure workflows.

**Monitoring limitation:** F01 and Z01 run on n8n, so they cannot report n8n's or the host's own outage. An external uptime monitor is needed (see `DEPLOYMENT.md`).

## 5. Import and activate

1. Install n8n on the VPS and configure the reverse proxy (`DEPLOYMENT.md`).
2. Create credentials listed in `/n8n/credentials.example.md`.
3. Import with `n8n import:workflow --separate --input=/n8n/workflows/<category>/`.
4. Set `Z01` as the error workflow on each.
5. Register each in `automation_workflows` (seeded).
6. Activate; verify a row reaches `automation_logs`.
