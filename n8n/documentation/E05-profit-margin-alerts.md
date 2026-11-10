# E05 Profit margin alerts

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/financial-monitoring/E05-profit-margin-alerts.json` (n8n workflow id `egsE05automation`). Laravel endpoints: `POST /executions`, `GET /monitor/margin-alerts` under `/api/v1/automation`.

## Purpose

Alert when items recognised in the last hour have a net margin below zero (negative profit).

## Trigger

Schedule Trigger, hourly at minute 0 (cron `0 * * * *`, workflow timezone UTC). The execution key is the schedule window (`E05:2026-11-10T10`), so a second run in the same window is refused as a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.

Scopes the Laravel token needs: `executions:write`, `monitor:read`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Fetch status | `n8n-nodes-base.code` v2 |  |
| Fetch status | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Build message | `n8n-nodes-base.code` v2 |  |
| Notify? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

Laravel response of `GET /monitor/margin-alerts` (standard envelope `{success, message, data, meta}`).

## Output

A warning listing the loss-making items.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`.

## Schedule

Hourly at minute 0 (cron `0 * * * *`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/financial-monitoring/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable E05`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsE05automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsE05automation --active=true`).
6. Check that rows for E05 reach `automation_logs`.
