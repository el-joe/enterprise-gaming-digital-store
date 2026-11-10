# Z01 Global error handler

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/infrastructure/Z01-global-error-handler.json` (n8n workflow id `egsZ01automation`). Laravel endpoints: `POST /alerts` under `/api/v1/automation`.

## Purpose

Global error handler. Records each failed execution in Laravel (`POST /alerts`, which also closes the failed execution as `failed`) and alerts the channel through Z02. When Z02 itself failed, it records the alert in Laravel only, so a broken channel cannot loop.

## Trigger

n8n Error Trigger. Every other workflow names this one as its error workflow (`settings.errorWorkflow`).

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.

Scopes the Laravel token needs: `executions:write`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Error Trigger | `n8n-nodes-base.errorTrigger` v1 |  |
| Build alert | `n8n-nodes-base.code` v2 |  |
| Record alert in Laravel | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart; continues on error |
| Restore alert | `n8n-nodes-base.code` v2 |  |
| Notify channel? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 | continues on error |
| Z02 failed: Laravel log only | `n8n-nodes-base.noOp` v1 |  |

## Input

The Error Trigger item: `execution` (id, url, lastNodeExecuted, error.message) and `workflow` (id, name). The failed workflow id is taken from the name prefix (`A05 ...`).

## Output

An `automation.alert` entry in the Laravel log, `automation_workflows.last_failure_at`, the failed execution closed as `failed`, and a critical message on the channel.

## Failure handling

Z01 has no error workflow of its own: pointing it at itself could loop. Its Laravel call and its Z02 call continue on error, so an unreachable Laravel or channel does not stop the other. A failure inside Z01 is visible only in the n8n execution list.

## Schedule

None. Runs whenever another workflow fails.

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/infrastructure/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`). The file names credentials only; n8n matches them by name when they exist.
3. Z01 needs no error workflow.
4. In Laravel, enable it: `php artisan automation:workflows enable Z01`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsZ01automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsZ01automation --active=true`).
6. Check that rows for Z01 reach `automation_logs`.
