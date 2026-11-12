# n8n automation department (M17)

Status: **authored and validated, not executed.** n8n is not installed on the development machine. All 58 workflows here were generated, statically validated with `scripts/validate-workflows.mjs`, and documented, but none has run in n8n. Treat the first import on a real host as the first test: import, run each workflow manually once against a staging Laravel, then activate.

The inventory, endpoints and rules are in `docs/N8N_AUTOMATIONS.md`; the automation API is section 7 of `docs/API_DOCUMENTATION.md`.

## Layout

```
n8n/
  workflows/<category>/<ID>-<slug>.json   58 workflows (inactive on import)
  documentation/<ID>-<slug>.md            one document per workflow
  scripts/validate-workflows.mjs           static validator (Node 18+, no dependencies)
  credentials.example.md                   credentials to create, placeholders only
```

| Folder | Workflows |
|---|---|
| `infrastructure` | Z01 error handler, Z02 notify sub-workflow, Z03 Laravel event router |
| `daily-operations` | A01 to A11 |
| `weekly-business-intelligence` | B01 to B10 |
| `smart-marketing` | C01 to C10 |
| `customer-automation` | D01 to D09 |
| `financial-monitoring` | E01 to E07 |
| `system-monitoring` | F01 to F08 |

## Principles built into every workflow

- Laravel is the source of truth. n8n reads through `/api/v1/automation`, never touches MySQL, and never changes transactional state. The only writes are its own execution log, alerts, review-only AI suggestions, logs of messages it sent, and (only when n8n is the configured driver) a request to queue the same command the Laravel scheduler would run.
- Every request is signed: bearer token (`LaravelAutomationApi`) plus `X-Automation-Signature`, a hex HMAC-SHA256 over timestamp, nonce, method, path with query, workflow id, execution key and raw body. Laravel refuses a timestamp more than 5 minutes away and any nonce it has seen.
- Every scheduled workflow starts with `POST /executions` using its schedule window as the execution key (`A01:2026-11-10`). A second start in the same window returns `skipped_duplicate` and the workflow stops. Event workflows use `<ID>:<event_id>`.
- Every workflow except Z01 names Z01 as its error workflow. Z01 has none, because it cannot be its own error workflow without risking a loop.
- Alerts go through Z02 only. The channel is chosen by `EGS_ALERT_CHANNEL` (`slack` or `telegram`); the business decision BD9 is open, and no workflow names a channel.
- Customer messages (D workflows) are a secondary SMS channel. Laravel sends the transactional email; Laravel decides who may be contacted (consent and preferences) and writes the text. Messages never carry a code, PIN or payment detail.
- AI workflows (C) store drafts as `pending_review`. Nothing is published automatically. The model is reached through the `LlmProvider` credential; no key is in any file.
- No credential value or secret is in any file. Credentials are referenced by name only.

## Install on Ubuntu without Docker (behind Nginx)

These steps follow `docs/DEPLOYMENT.md` (Nginx server block, Supervisor program, backups). They have not been executed here.

1. Install Node.js 20 or later from the distribution or NodeSource, then n8n for a dedicated user:
   ```
   sudo useradd --system --create-home --home-dir /opt/n8n n8n
   sudo npm install -g n8n
   ```
2. Configure the environment (Supervisor `environment=` line or `/opt/n8n/.n8n/.env`). Required values are listed below; never commit them.
3. Add the Nginx server block from `DEPLOYMENT.md` (`n8n.example.com`): the editor is restricted to operator IPs, `/webhook/` stays reachable for Laravel and the C09 manual trigger. Issue the certificate with certbot.
4. Add the Supervisor program `n8n` from `DEPLOYMENT.md`, then `sudo supervisorctl reread && sudo supervisorctl update`.
5. Open the editor, create the owner account, and create the credentials in `credentials.example.md` with exactly those names.
6. In Laravel, issue the automation credential and enable workflows (below).
7. Import, review and publish the workflows in the order below.

### n8n environment variables

| Variable | Value |
|---|---|
| `GENERIC_TIMEZONE` | `UTC` (workflows also set `timezone: UTC`) |
| `N8N_HOST`, `N8N_PROTOCOL`, `WEBHOOK_URL` | the public host, `https`, `https://n8n.example.com/` |
| `N8N_ENCRYPTION_KEY` | long random value; back it up (credentials cannot be decrypted without it) |
| `NODE_FUNCTION_ALLOW_BUILTIN` | `crypto` (Code nodes sign requests and verify Laravel's signature) |
| `N8N_BLOCK_ENV_ACCESS_IN_NODE` | `false` (the documented default); workflows read the variables below through `$env` |
| `EGS_API_BASE_URL` | Laravel base URL, for example `https://example.com` |
| `EGS_AUTOMATION_HMAC_SECRET` | the HMAC secret printed by `php artisan automation:credentials issue` |
| `EGS_OUTBOUND_SECRET` | equal to Laravel's outbound signing secret (Admin > Settings > Automation) |
| `EGS_ALERT_CHANNEL` | `slack` or `telegram` |
| `EGS_SLACK_CHANNEL_ID` | Slack channel id (when the channel is Slack) |
| `EGS_TELEGRAM_CHAT_ID` | Telegram chat id (when the channel is Telegram) |
| `EGS_SMS_API_URL` | SMS provider endpoint for D workflows (provider not chosen) |
| `EGS_LLM_MODEL` | model id for the LLM provider used by C workflows |
| `EGS_STOREFRONT_URL` | public homepage URL (F01) |
| `EGS_N8N_DRIVES_PAYMENT_RECONCILIATION` | `true` only when Laravel's `task_drivers.payment_reconciliation` is `n8n` (E02) |

Custom n8n Variables (`$vars`) are not used: n8n documents them for the Business and Enterprise editions only, and the community edition is assumed.

### Laravel side

In Admin > Settings > Automation (`settings.manage_critical`; these were `.env` keys until 2026-11-12, see `docs/DEPLOYMENT.md` 3.2, and are no longer read from `.env`):

| Setting | Value |
|---|---|
| Send events to n8n (outbox) | on |
| n8n webhook URL | `https://n8n.example.com/webhook/egs-events` (https; http only on a local install) |
| Outbound signing secret (HMAC), write-only | long random value, also `EGS_OUTBOUND_SECRET` in n8n |
| Webhook header token, write-only | long random value, also the LaravelOutboundWebhook credential |

The two secrets are stored encrypted and never shown again; keep the values in n8n only. An installation that still has `AUTOMATION_OUTBOX_ENABLED`, `N8N_WEBHOOK_URL`, `N8N_OUTBOUND_SECRET` and `N8N_WEBHOOK_AUTH_TOKEN` in `backend/.env` copies them once with `php artisan config:clear && php artisan settings:import-env`, then removes the lines.

Issue the credential (token and HMAC secret are printed once; issuing again replaces the account's secret):

```
php artisan automation:credentials issue n8n-main \
  --scope=executions:write --scope=reports:read --scope=monitor:read --scope=marketing:read \
  --scope=suggestions:write --scope=customers:message --scope=jobs:run --ip=<n8n server IP>
```

Without `--scope` the account gets `executions:write`, `reports:read`, `monitor:read`, `marketing:read` only. Add `jobs:run` only if n8n drives a task (A09, A10, E02), `suggestions:write` for B10 and C workflows, and `customers:message` for D workflows. The same can be done with `POST /admin/api/automation-credentials` (`service_accounts.manage`).

Every workflow is seeded disabled in `automation_workflows`, and Laravel refuses calls from a disabled workflow. Enable the ones you activate:

```
php artisan automation:workflows enable Z01 Z02 Z03 A01 A02
php artisan automation:workflows list
```

Tasks driven by n8n instead of the scheduler (ADR-014) are switched in `settings.automation.task_drivers`, for example `{"catalog_sync": "n8n"}`. The scheduler then skips that task and the job endpoint accepts it. Leave the default (`scheduler`) unless you mean to move a task.

## Local development (Apache, `boxcards.loc` and `n8n.boxcards.loc`)

This runs a local n8n of its own on `127.0.0.1:5710`, away from the other local n8n instances (5678-5679 and 5690-5691); its task broker is on 5711. It lives in `n8n/runtime/`, where `node_modules/`, `data/` and `.env` are git-ignored, and uses n8n 1.123.92, pinned.

1. Install n8n: `cd n8n/runtime && npm ci`.
2. Start it as a user service. This creates `.env` with a random `N8N_ENCRYPTION_KEY`; back that key up. Run `./install-user-service.sh`, then check it with `systemctl --user status n8n-boxcards`.
3. Set up Apache and the hosts file once, as root: `sudo deploy/setup-local-sudo.sh`. Add `--linger` to keep n8n running when you are logged out. This installs `deploy/apache/boxcards.loc.conf` (pointing at `backend/public`; `admin.boxcards.loc` is an alias) and `deploy/apache/000-n8n.boxcards.loc.conf` (a reverse proxy with WebSocket support), and replaces the old `admin.boxcards.loc` vhost.
4. In `backend/.env`, set `APP_URL=http://boxcards.loc` (with `APP_ENV=local`, so the http webhook URL is accepted). In Admin > Settings > Automation, set:
   - n8n webhook URL `http://n8n.boxcards.loc/webhook/egs-events`;
   - the outbound signing secret and the webhook header token.

   In `n8n/runtime/.env`, set:
   - `EGS_OUTBOUND_SECRET` (equal to the outbound signing secret);
   - `EGS_AUTOMATION_HMAC_SECRET` (from `php artisan automation:credentials issue`).

   Then restart n8n with `systemctl --user restart n8n-boxcards`.
5. Open `http://n8n.boxcards.loc`, create the owner account, and import the workflows in the order below.

## Import order

Workflow ids are fixed in the files (`egs<ID>automation`), so references survive import: every workflow's error workflow is `egsZ01automation`, every notification calls `egsZ02automation`, and Z03 calls `egsD01automation` to `egsD05automation`. Importing a file whose id already exists overwrites that workflow.

```
n8n import:workflow --separate --input=n8n/workflows/infrastructure/
n8n import:workflow --separate --input=n8n/workflows/customer-automation/
n8n import:workflow --separate --input=n8n/workflows/daily-operations/
n8n import:workflow --separate --input=n8n/workflows/financial-monitoring/
n8n import:workflow --separate --input=n8n/workflows/system-monitoring/
n8n import:workflow --separate --input=n8n/workflows/weekly-business-intelligence/
n8n import:workflow --separate --input=n8n/workflows/smart-marketing/
```

`import:workflow` deactivates what it imports. Then, for each workflow:

1. Open it and confirm each credential selection; the files carry credential names only.
2. Run it once manually against staging and read the execution.
3. Publish: `n8n publish:workflow --id=<id>` (n8n 2.x; on 1.x use `n8n update:workflow --id=<id> --active=true`). Publish Z01 and Z02 first, then Z03 and D01 to D05 together.

Check that `automation_logs` receives rows (`kind = call` for every request, `kind = execution` for each run).

## Validation

```
node n8n/scripts/validate-workflows.mjs
```

It also runs inside the Laravel suite (`tests/Feature/Automation/N8nWorkflowValidationTest.php`). It checks parsing, structure, node types and versions against the versions n8n's source declares, JavaScript syntax of every Code node and expression, connections, the error workflow, credentials by name only, a secret scan, environment variable names, retry on Laravel calls, and the matching document with all headings. It cannot prove that a workflow behaves correctly in n8n; only running it can.

## Known limits

- F01 and Z01 run on n8n and cannot report n8n's or the host's own outage; keep an external uptime monitor.
- Retries of a signed request resend the same nonce. If the first copy reached Laravel, the retry is refused as `replay_detected` and the run fails into Z01; the next window runs normally.
- If n8n dies mid-run without reaching Z01, the execution stays `started` and that window is refused as a duplicate; the next window runs normally.
- The Error Trigger does not give the failed run's execution key, so Z01 asks Laravel to close the newest running execution of that workflow.
- The SMS provider, the alert channel and the LLM key are not chosen (BD9). The SMS node sends `{to, text, reference}` and will need adapting to the provider.
- Exceptions (F03) and web request latency (F06) are `unavailable` in Laravel; those workflows do not alert until a source exists.
