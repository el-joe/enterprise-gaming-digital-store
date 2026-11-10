# C06 Suggest Coupon Campaigns

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/smart-marketing/C06-suggest-coupon-campaigns.json` (n8n workflow id `egsC06automation`). Laravel endpoints: `POST /executions`, `GET /data/trending`, `POST /suggestions` under `/api/v1/automation`.

## Purpose

Suggest coupon campaign ideas for staff to evaluate (no coupon is created). Output is stored with `POST /suggestions` as `pending_review` and is never published automatically: approval creates a draft, and publication is a further human act. The LLM provider is the `LlmProvider` credential; no key is in the file.

## Trigger

Schedule Trigger, every Tuesday at 08:20 UTC (cron `20 8 * * 2`, workflow timezone UTC). The execution key is the schedule window (`C06:2026-W46`), so a second run in the same window is refused as a duplicate.

## Required credentials

- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.
- `LlmProvider`: Anthropic API credential (`anthropicApi`) for the "LLM provider (Anthropic)" sub-node. To use OpenAI instead, replace that sub-node with "OpenAI Chat Model" and an OpenAI credential. The key is never in the workflow file.

Scopes the Laravel token needs: `executions:write`, `marketing:read`, `suggestions:write`. `suggestions:write` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`); `EGS_LLM_MODEL` (model id for the LLM provider). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Schedule | `n8n-nodes-base.scheduleTrigger` v1.2 |  |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Fetch sales data | `n8n-nodes-base.code` v2 |  |
| Fetch sales data | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Build prompts | `n8n-nodes-base.code` v2 |  |
| Draft with LLM | `@n8n/n8n-nodes-langchain.chainLlm` v1.7 | retry 2 tries, 5000 ms apart |
| LLM provider (Anthropic) | `@n8n/n8n-nodes-langchain.lmChatAnthropic` v1.3 | credential `LlmProvider` |
| Parse drafts | `n8n-nodes-base.code` v2 |  |
| Sign: Store suggestion | `n8n-nodes-base.code` v2 |  |
| Store suggestion | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Summarise | `n8n-nodes-base.code` v2 |  |
| Build message | `n8n-nodes-base.code` v2 |  |
| Notify? | `n8n-nodes-base.if` v2.2 |  |
| Notify (Z02) | `n8n-nodes-base.executeWorkflow` v1.2 |  |
| Sign: finish execution | `n8n-nodes-base.code` v2 |  |
| Finish execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |

## Input

Aggregated internal data only: `data/trending?days=30&limit=20`. No customer data, costs or supplier details; external trend sources are not connected and the data says so.

## Output

One `ai_suggestions` row per draft, type `coupon`, status `pending_review` (a retried request returns the existing row), and an information message with the count.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`. The LLM call retries twice. A reply without a usable JSON array of drafts fails the run, so nothing half-parsed is stored.

## Schedule

Every Tuesday at 08:20 UTC (cron `20 8 * * 2`, workflow timezone UTC)

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/smart-marketing/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`LaravelAutomationApi`, `LlmProvider`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable C06`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsC06automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsC06automation --active=true`).
6. Check that rows for C06 reach `automation_logs`.
