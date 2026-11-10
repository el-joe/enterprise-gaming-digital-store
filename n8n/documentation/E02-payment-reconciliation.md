# E02 Payment reconciliation

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/financial-monitoring/E02-payment-reconciliation.json` (n8n workflow id `egsE02automation`). Laravel endpoints: `POST /executions`, `POST /jobs/reconcile-payments`, `GET /payments/reconciliation` under `/api/v1/automation`.

## Purpose

Report what the daily payment reconciliation found. When `EGS_N8N_DRIVES_PAYMENT_RECONCILIATION=true` (and the Laravel setting `task_drivers.payment_reconciliation` is `n8n`), first ask Laravel to queue `payments:reconcile-daily`.

## Trigger

Schedule Trigger, daily at 02:45 UTC (cron `45 2 * * *`, workflow timezone UTC). The execution key is the schedule window (`E02:2026-11-10`), so a second run in the same window is refused as a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.

Scopes the Laravel token needs: `executions:write`, `jobs:run`, `reports:read`. `jobs:run` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`); `EGS_N8N_DRIVES_PAYMENT_RECONCILIATION` (`true` only when Laravel's `task_drivers.payment_reconciliation` is `n8n`). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| n8n drives reconciliation? | `n8n-nodes-base.if` v2.2 |  |
| Sign: Queue reconciliation in Laravel | `n8n-nodes-base.code` v2 |  |
| Queue reconciliation in Laravel | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Sign: Fetch reconciliation | `n8n-nodes-base.code` v2 |  |
| Fetch reconciliation | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Build message | `n8n-nodes-base.code` v2 |  |
| Notify? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

Laravel response of `POST /jobs/reconcile-payments`, `GET /payments/reconciliation` (standard envelope `{success, message, data, meta}`).

## Output

A warning when reconciliation items are open; otherwise only the execution log.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`.

## Schedule

Daily at 02:45 UTC (cron `45 2 * * *`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/financial-monitoring/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable E02`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsE02automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsE02automation --active=true`).
6. Check that rows for E02 reach `automation_logs`.
