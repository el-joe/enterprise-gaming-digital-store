# A10 Exchange Rate Updates

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/daily-operations/A10-exchange-rate-updates.json` (n8n workflow id `egsA10automation`). Laravel endpoints: `POST /executions`, `POST /jobs/exchange-rates` under `/api/v1/automation`.

## Purpose

Ask Laravel to queue the hourly exchange-rate fetch, only when `settings.automation.task_drivers.exchange_rates` is `n8n` (ADR-014).

## Trigger

Schedule Trigger, hourly at minute 5 (cron `5 * * * *`, workflow timezone UTC). The execution key is the schedule window (`A10:2026-11-10T10`), so a second run in the same window is refused as a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.

Scopes the Laravel token needs: `executions:write`, `jobs:run`. `jobs:run` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Queue task in Laravel | `n8n-nodes-base.code` v2 |  |
| Queue task in Laravel | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Build message | `n8n-nodes-base.code` v2 |  |
| Notify? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

Laravel response of `POST /jobs/exchange-rates` (standard envelope `{success, message, data, meta}`).

## Output

The queued job reference in the execution summary. Laravel runs `currency:fetch-rates`.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`. When Laravel's scheduler is the driver, the job endpoint answers 409 `task_driver_mismatch`; keep this workflow inactive unless the setting says `n8n`.

## Schedule

Hourly at minute 5 (cron `5 * * * *`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/daily-operations/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable A10`. Disabled workflows are refused (`workflow_disabled`).
5. Set `settings.automation.task_drivers` for this task to `n8n` first, or the scheduler and n8n would both drive it.
6. Publish: `n8n publish:workflow --id=egsA10automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsA10automation --active=true`).
7. Check that a `call` row and an `execution` row for A10 reach `automation_logs`.
