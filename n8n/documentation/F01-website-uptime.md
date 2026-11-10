# F01 Website uptime

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/system-monitoring/F01-website-uptime.json` (n8n workflow id `egsF01automation`). Laravel endpoints: none.

## Purpose

Check that Laravel's `/up` returns 200 and the homepage answers 2xx or 3xx, and alert when that changes. It does not call the automation API: it probes Laravel itself, and logging every minute would add 1440 rows a day. Response times are not measured. n8n cannot report its own or the host's outage; an external monitor is still required (DEPLOYMENT.md).

## Trigger

Schedule Trigger, every minute.

## Required credentials

- None.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_STOREFRONT_URL` (public homepage URL).

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Check /up | `n8n-nodes-base.httpRequest` v4.2 | continues on error |
| Check homepage | `n8n-nodes-base.httpRequest` v4.2 | continues on error |
| Evaluate | `n8n-nodes-base.code` v2 |  |
| Notify? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 |  |

## Input

HTTP responses of `/up` and the homepage.

## Output

A critical "Website is down" message once, and "Website recovered" when it is back.

## Failure handling

The probes never throw: a timeout or refusal counts as down. A failure of Z02 goes to Z01.

## Schedule

Every minute (cron `* * * * *`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/system-monitoring/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`none`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable F01`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsF01automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsF01automation --active=true`).
6. Check that rows for F01 reach `automation_logs` (F01 writes none; check the n8n execution list).
