# C09 Generate Product Marketing Content

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/smart-marketing/C09-generate-product-marketing-content.json` (n8n workflow id `egsC09automation`). Laravel endpoints: `POST /executions`, `GET /data/product-context/{product_id}`, `POST /suggestions` under `/api/v1/automation`.

## Purpose

On demand, draft marketing copy for one product (POST `{"product_id": "<ULID>"}` to the webhook). Output is stored with `POST /suggestions` as `pending_review` and is never published automatically: approval creates a draft, and publication is a further human act. The LLM provider is the `LlmProvider` credential; no key is in the file.

## Trigger

Webhook `POST /webhook/egs-c09-marketing-content` with header authentication; body `{"product_id": "<ULID>"}`. Execution key `C09:<product_id>:<date>`, so one draft request per product per day.

## Required credentials

- `ManualTriggerWebhook`: HTTP Header Auth protecting the manual webhook. Choose a header name and a long random value; give them only to staff who may request drafts.
- `LaravelAutomationApi`: HTTP Header Auth. Name `Authorization`, value `Bearer <token>` from `php artisan automation:credentials issue`.
- `LlmProvider`: Anthropic API credential (`anthropicApi`) for the "LLM provider (Anthropic)" sub-node. To use OpenAI instead, replace that sub-node with "OpenAI Chat Model" and an OpenAI credential. The key is never in the workflow file.

Scopes the Laravel token needs: `executions:write`, `marketing:read`, `suggestions:write`. `suggestions:write` is not granted by default: issue with `--scope`.

Environment variables (n8n process): `EGS_API_BASE_URL` (Laravel base URL, for example `https://shop.example.com`); `EGS_AUTOMATION_HMAC_SECRET` (HMAC secret printed by `automation:credentials issue`); `EGS_LLM_MODEL` (model id for the LLM provider). Code nodes use the built-in `crypto` module: `NODE_FUNCTION_ALLOW_BUILTIN=crypto`.

## Nodes

| Node | Type | Notes |
|---|---|---|
| Webhook (manual request) | `n8n-nodes-base.webhook` v2 | credential `ManualTriggerWebhook` |
| Prepare execution | `n8n-nodes-base.code` v2 |  |
| Start execution | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Already ran in this window? | `n8n-nodes-base.if` v2.2 |  |
| Stop: duplicate execution | `n8n-nodes-base.noOp` v1 |  |
| Sign: Fetch product context | `n8n-nodes-base.code` v2 |  |
| Fetch product context | `n8n-nodes-base.httpRequest` v4.2 | credential `LaravelAutomationApi`; retry 3 tries, 5000 ms apart |
| Build prompts | `n8n-nodes-base.code` v2 |  |
| Anything to draft? | `n8n-nodes-base.if` v2.2 |  |
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

Aggregated internal data only: `data/product-context/`{product_id}. No customer data, costs or supplier details; external trend sources are not connected and the data says so.

## Output

One `ai_suggestions` row per draft, type `marketing_copy`, status `pending_review` (a retried request returns the existing row), and an information message with the count.

## Failure handling

Calls to Laravel retry up to 3 times, 5 seconds apart. A retry resends the same nonce: if the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails, which is intended for non-idempotent calls. Any error stops the run and Z01 records it in Laravel (closing the execution as `failed`) and alerts the channel. A failed window can be run again; a window that succeeded or is still running is refused as `skipped_duplicate`. The LLM call retries twice. A reply without a usable JSON array of drafts fails the run, so nothing half-parsed is stored.

## Schedule

None. Runs on request.

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/smart-marketing/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`ManualTriggerWebhook`, `LaravelAutomationApi`, `LlmProvider`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable C09`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsC09automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsC09automation --active=true`).
6. Check that rows for C09 reach `automation_logs`.
