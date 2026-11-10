# D01 Order confirmation

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/customer-automation/D01-order-confirmation.json` (n8n workflow id `egsD01automation`). Laravel endpoints: `POST /executions`, `POST /customers/messages/prepare`, `POST /customers/messages/log` under `/api/v1/automation`.

## Purpose

Send the customer an SMS for `order.created` as a secondary channel. The transactional email is sent by Laravel (ADR-013); this workflow only adds SMS, never a code, PIN or payment detail. Laravel decides whether the customer may be contacted: an active account with a phone number and an explicit, enabled SMS preference for order updates. Guests are never contacted this way.

## Trigger

Execute Workflow Trigger, called by Z03 when Laravel's outbox delivers an event of type `order.created`. Execution key `D01:<event_id>`, so a redelivered event is a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.
- `CustomerSmsApi`: HTTP Header Auth for the SMS provider (provider not chosen yet). The request body is `{"to", "text", "reference"}`; adapt the node to the chosen provider's API.

Scopes the Laravel token needs: `executions:write`, `customers:message`. `customers:message` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`); `EGS_SMS_API_URL` (SMS provider endpoint). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| When called by Z03 | `n8n-nodes-base.executeWorkflowTrigger` v1.1 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Prepare message | `n8n-nodes-base.code` v2 |  |
| Prepare message | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Customer may be contacted? | `n8n-nodes-base.if` v2.2 |  |
| Recipient | `n8n-nodes-base.code` v2 |  |
| Send SMS | `n8n-nodes-base.httpRequest` v4.2 | credential `CustomerSmsApi`; continues on error |
| Sign: Log message | `n8n-nodes-base.code` v2 |  |
| Log message | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

The event from Z03: `{event_id, event_type, occurred_at, attempt, payload}`; the payload holds identifiers only (order or ticket public id, order number, status). Then `POST /customers/messages/prepare` returns `eligible`, a `reason` when not, or the recipient (`customer_id`, `phone`) and the message text written by Laravel.

## Output

At most one SMS through the `CustomerSmsApi` provider, then `POST /customers/messages/log` with `sent` or `failed` (no message text). Nothing else changes in Laravel.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`. The SMS node does not retry (a retry could send twice) and continues on error: a provider failure is logged as `failed`, and that customer is offered again on the next run.

## Schedule

None. Runs per event, within seconds of the state change (Laravel flushes the outbox every minute).

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/customer-automation/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`, `CustomerSmsApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable D01`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsD01automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsD01automation --active=true`).
6. Check that rows for D01 reach `automation_logs`.
