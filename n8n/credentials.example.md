# n8n credentials (placeholders only)

Create these in n8n (Credentials > Add credential) with exactly these names. The workflow files reference credentials by name only and contain no values. Real values are never committed; keep them in n8n's encrypted credential store (protected by `N8N_ENCRYPTION_KEY`) and in the operators' secret store.

| Name | n8n type | Fields | Used by |
|---|---|---|---|
| `LaravelAutomationApi` | HTTP Header Auth (`httpHeaderAuth`) | Name: `Authorization`; Value: `Bearer <AUTOMATION_TOKEN>` | every workflow that calls Laravel |
| `LaravelOutboundWebhook` | HTTP Header Auth (`httpHeaderAuth`) | Name: `X-EGS-Webhook-Token`; Value: `<N8N_WEBHOOK_AUTH_TOKEN>` | Z03 webhook |
| `ManualTriggerWebhook` | HTTP Header Auth (`httpHeaderAuth`) | Name: `<HEADER_NAME>`; Value: `<LONG_RANDOM_VALUE>` | C09 manual webhook |
| `AlertSlack` | Slack API (`slackApi`) | Access token: `<SLACK_BOT_TOKEN>` (scope `chat:write`) | Z02 when `EGS_ALERT_CHANNEL=slack` |
| `AlertTelegram` | Telegram API (`telegramApi`) | Access token: `<TELEGRAM_BOT_TOKEN>` | Z02 when `EGS_ALERT_CHANNEL=telegram` |
| `CustomerSmsApi` | HTTP Header Auth (`httpHeaderAuth`) | Name and value as the SMS provider requires, for example `Authorization: Bearer <SMS_API_KEY>` | D01 to D09 |
| `LlmProvider` | Anthropic (`anthropicApi`) | API key: `<ANTHROPIC_API_KEY>` | C01 to C10 |

Only one of `AlertSlack` and `AlertTelegram` is needed, matching `EGS_ALERT_CHANNEL`. Z02 references both; the unused branch never runs. If n8n refuses to publish Z02 because the unused credential is missing, delete that one node.

To use OpenAI for the C workflows instead of Anthropic, replace the "LLM provider (Anthropic)" sub-node in each with "OpenAI Chat Model" and an OpenAI credential, keeping the credential name `LlmProvider`.

## Values that are environment variables, not credentials

Code nodes cannot read n8n credentials, so the two signing secrets are environment variables of the n8n process (see `README.md`):

| Variable | Placeholder | Source |
|---|---|---|
| `EGS_AUTOMATION_HMAC_SECRET` | `<AUTOMATION_HMAC_SECRET>` | printed once by `php artisan automation:credentials issue` |
| `EGS_OUTBOUND_SECRET` | `<N8N_OUTBOUND_SECRET>` | the same value as Laravel's `N8N_OUTBOUND_SECRET` |

## Laravel side

`<AUTOMATION_TOKEN>` and `<AUTOMATION_HMAC_SECRET>` come from:

```
php artisan automation:credentials issue n8n-main --scope=... --ip=<n8n server IP>
```

or `POST /admin/api/automation-credentials` (permission `service_accounts.manage`). Both are shown once. Issuing again for the same account replaces its HMAC secret, so update `EGS_AUTOMATION_HMAC_SECRET` at the same time. Revoke with `php artisan automation:credentials revoke --id=<token id>`.
