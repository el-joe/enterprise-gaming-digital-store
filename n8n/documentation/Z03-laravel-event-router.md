# Z03 Laravel event router

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/infrastructure/Z03-laravel-event-router.json` (n8n workflow id `egsZ03automation`). Laravel endpoints: `POST /executions` under `/api/v1/automation`.

## Purpose

Receives Laravel's outbox events, verifies the HMAC signature and timestamp, deduplicates by `event_id` (execution key `Z03:<event_id>`) and starts the matching customer workflow (D01 to D05). Unknown event types are ignored.

## Trigger

Webhook `POST /webhook/egs-events` with header authentication and the raw body kept for the signature check; answered with 202 before the event is routed.

## Required credentials

- `LaravelOutboundWebhook`: HTTP Header Auth used by the webhook node. Name `X-EGS-Webhook-Token`, value equal to Laravel's `N8N_WEBHOOK_AUTH_TOKEN`.
- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.

Scopes the Laravel token needs: `executions:write`.

Environment variables (n8n process): `EGS_OUTBOUND_SECRET` (equal to Laravel's `N8N_OUTBOUND_SECRET`); `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Webhook from Laravel | `n8n-nodes-base.webhook` v2 | credential `LaravelOutboundWebhook` |
| Verify signature | `n8n-nodes-base.code` v2 |  |
| Signature valid? | `n8n-nodes-base.if` v2.2 |  |
| Respond 202 accepted | `n8n-nodes-base.respondToWebhook` v1.1 |  |
| Respond 401 rejected | `n8n-nodes-base.respondToWebhook` v1.1 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Route event | `n8n-nodes-base.code` v2 |  |
| Event type | `n8n-nodes-base.switch` v3.2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Run D01 (order.created) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Run D02 (payment.succeeded) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Run D03 (order.delivered) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Run D04 (order.failed) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Run D05 (ticket.updated) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Ignore unknown event | `n8n-nodes-base.noOp` v1 |  |

## Input

The raw POST body from `automation:flush-outbox` and headers `X-Signature`, `X-Signature-Timestamp`, `X-Event-Id`, `X-Event-Type`, `X-EGS-Webhook-Token`.

## Output

HTTP 202 to Laravel (401 on a bad signature), then one sub-workflow run.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`.

## Schedule

None. Runs per delivered event.

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/infrastructure/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelOutboundWebhook`, `LaravelAutomationApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable Z03`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsZ03automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsZ03automation --active=true`).
6. Check that rows for Z03 reach `automation_logs`.
