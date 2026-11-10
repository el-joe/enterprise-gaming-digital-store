# Z02 Send notification (sub-workflow)

Status: **authored and validated, not executed.** n8n is not installed on the development machine; this workflow has never run. The JSON is checked by `n8n/scripts/validate-workflows.mjs`.

File: `n8n/workflows/infrastructure/Z02-send-notification-sub-workflow.json` (n8n workflow id `egsZ02automation`). Laravel endpoints: none.

## Purpose

The one reusable "notify" sub-workflow. The channel is chosen by `EGS_ALERT_CHANNEL` (`slack` or `telegram`) because the alert channel (BD9) is not decided; no other workflow names a channel.

## Trigger

Execute Workflow Trigger ("Accept all data"), called by any workflow with items `{severity, title, text, source}`.

## Required credentials

- `AlertSlack`: Slack API (bot token with `chat:write`, bot invited to the channel). Used only when `EGS_ALERT_CHANNEL=slack`.
- `AlertTelegram`: Telegram API (bot token). Used only when `EGS_ALERT_CHANNEL=telegram`.

Environment variables (n8n process): `EGS_ALERT_CHANNEL` (`slack` or `telegram`); `EGS_SLACK_CHANNEL_ID` (Slack channel id when the channel is Slack); `EGS_TELEGRAM_CHAT_ID` (Telegram chat id when the channel is Telegram).

## Nodes

| Node | Type | Notes |
|---|---|---|
| When called by another workflow | `n8n-nodes-base.executeWorkflowTrigger` v1.1 |  |
| Format message | `n8n-nodes-base.code` v2 |  |
| Channel | `n8n-nodes-base.switch` v3.2 |  |
| Send to Slack | `n8n-nodes-base.slack` v2.2 | credential `AlertSlack`; retry 3 tries, 5000 ms apart |
| Send to Telegram | `n8n-nodes-base.telegram` v1.2 | credential `AlertTelegram`; retry 3 tries, 5000 ms apart |

## Input

Items `{severity: info|warning|critical, title, text, source}`.

## Output

One message on Slack or Telegram, prefixed `[SEVERITY]`, at most 3500 characters.

## Failure handling

Slack and Telegram nodes retry 3 times, 5 seconds apart. An unknown `EGS_ALERT_CHANNEL` throws. Errors go to Z01, which then records the alert in Laravel only (it never calls Z02 for a Z02 failure).

## Schedule

None. Runs when called.

## Import and activation

1. Import the category folder: `n8n import:workflow --separate --input=n8n/workflows/infrastructure/` (Z01 and Z02 first, see `n8n/README.md`). Imported workflows are inactive.
2. Open the workflow and check each credential selection (`AlertSlack`, `AlertTelegram`). The file names credentials only; n8n matches them by name when they exist.
3. Confirm Settings > Error workflow is `Z01 Global error handler` (id `egsZ01automation`).
4. In Laravel, enable it: `php artisan automation:workflows enable Z02`. Disabled workflows are refused (`workflow_disabled`).
5. Publish: `n8n publish:workflow --id=egsZ02automation` (n8n 2.x; on 1.x, `n8n update:workflow --id=egsZ02automation --active=true`).
6. Check that rows for Z02 reach `automation_logs`.
