# D08 Customer reactivation

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/customer-automation/D08-customer-reactivation.json` (n8n workflow id `egsD08automation`). Laravel endpoints: `POST /executions`, `GET /customers/segments/reactivation`, `POST /customers/messages/log` under `/api/v1/automation`.

## Purpose

Invite customers whose last order was 60 to 365 days ago back to the store, once per month. Laravel returns only customers whose latest SMS marketing consent is a grant, who have not disabled marketing SMS, have an active account and a phone number, and have not already received this message.

## Trigger

Schedule Trigger, every Wednesday at 10:00 UTC (cron `0 10 * * 3`, workflow timezone UTC). The execution key is the schedule window (`D08:2026-W46`), so a second run in the same window is refused as a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.
- `CustomerSmsApi`: HTTP Header Auth for the SMS provider (provider not chosen yet). The request body is `{"to", "text", "reference"}`; adapt the node to the chosen provider's API.

Scopes the Laravel token needs: `executions:write`, `customers:message`. `customers:message` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`); `EGS_SMS_API_URL` (SMS provider endpoint). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Fetch recipients | `n8n-nodes-base.code` v2 |  |
| Fetch recipients | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Split recipients | `n8n-nodes-base.code` v2 |  |
| Any recipients? | `n8n-nodes-base.if` v2.2 |  |
| Send SMS | `n8n-nodes-base.httpRequest` v4.2 | credential `CustomerSmsApi`; continues on error |
| Sign: Log message | `n8n-nodes-base.code` v2 |  |
| Log message | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Count results | `n8n-nodes-base.code` v2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

`GET /customers/segments/reactivation`: up to 100 recipients, each with `dedupe_key`, `customer_id`, `phone`, `locale` and `message.text` (written by Laravel).

## Output

One SMS per recipient (batches of 10 per second), each logged with `POST /customers/messages/log`. Logging `sent` excludes the customer from the next run.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`. The SMS node does not retry (a retry could send twice) and continues on error: a provider failure is logged as `failed`, and that customer is offered again on the next run.

## Schedule

Every Wednesday at 10:00 UTC (cron `0 10 * * 3`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/customer-automation/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`, `CustomerSmsApi`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable D08`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsD08automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsD08automation --active=true`).
6. Check that rows for D08 reach `automation_logs`.
