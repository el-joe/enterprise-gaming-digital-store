# EGS MCP server

A Model Context Protocol server that lets authorised AI clients (Claude Desktop, Claude Code) read and, with approval, change the Enterprise Gaming Digital Store. It talks only to the protected Laravel API at `/api/v1/mcp`; it has no database access and holds no provider credentials. Laravel enforces every rule again, so a modified copy of this server gains nothing beyond its token.

The full specification, tool schemas and security notes are in [`../docs/MCP_SERVER.md`](../docs/MCP_SERVER.md).

## Requirements

- Node.js 20 or later.
- A store token issued by an administrator (see below). Tokens are read-only unless writes were allowed explicitly.

## Install and build

```bash
cd mcp-server
npm ci
npm run build      # compiles src/ to dist/
npm test           # unit and in-process protocol tests (Laravel HTTP is mocked)
npm run smoke      # builds, starts dist/index.js over stdio, lists tools and calls one against a local mock API
```

## Configuration

All configuration comes from environment variables set by the MCP client. Nothing is hardcoded.

| Variable | Default | Purpose |
|---|---|---|
| `EGS_API_BASE_URL` | required | Store URL, for example `https://store.example.com`. Must be HTTPS; plain HTTP is accepted only for `localhost` and `127.0.0.1`. |
| `EGS_API_TOKEN` | required | The token issued for this client. |
| `MCP_READ_ONLY` | `true` | Write tools are not even registered unless this is exactly `false` **and** the account is writable **and** the token holds the write scope. |
| `MCP_TRANSPORT` | `stdio` | Only `stdio` is implemented in this build. |
| `MCP_LOG_LEVEL` | `info` | `debug`, `info`, `warn`, `error` or `silent`. Logs go to stderr as JSON lines; stdout carries only the protocol. |
| `MCP_HTTP_TIMEOUT_MS` | `15000` | Timeout of each call to the store API. |
| `MCP_RATE_LIMIT_PER_MINUTE` | `60` | Local token bucket. The store's per-account limit is authoritative. |

## Getting a token

An administrator issues tokens on the server that runs Laravel:

```bash
php artisan mcp:token issue "claude-desktop-alice"                      # all read scopes, read-only account
php artisan mcp:token issue "claude-code-ops" --scope=products:read --scope=products:write --scope=actions:confirm --allow-writes
php artisan mcp:token list
php artisan mcp:token revoke --id=12
```

or through `POST /admin/api/mcp-tokens` (permission `service_accounts.manage`). The token is printed once. Treat it like a password: keep it in the client configuration or an environment variable, never in a committed file.

## Claude Desktop

Add to `claude_desktop_config.json` (macOS: `~/Library/Application Support/Claude/`, Windows: `%APPDATA%\Claude\`), then restart Claude Desktop:

```json
{
  "mcpServers": {
    "egs-store": {
      "command": "node",
      "args": ["/absolute/path/to/mcp-server/dist/index.js"],
      "env": {
        "EGS_API_BASE_URL": "https://store.example.com",
        "EGS_API_TOKEN": "replace-with-your-token",
        "MCP_READ_ONLY": "true"
      }
    }
  }
}
```

## Claude Code

With the CLI:

```bash
claude mcp add egs-store \
  --env EGS_API_BASE_URL=https://store.example.com \
  --env EGS_API_TOKEN=replace-with-your-token \
  --env MCP_READ_ONLY=true \
  -- node /absolute/path/to/mcp-server/dist/index.js
```

Or a project `.mcp.json` that reads the token from your shell environment, so the file can be shared without the secret:

```json
{
  "mcpServers": {
    "egs-store": {
      "command": "node",
      "args": ["/absolute/path/to/mcp-server/dist/index.js"],
      "env": {
        "EGS_API_BASE_URL": "https://store.example.com",
        "EGS_API_TOKEN": "${EGS_API_TOKEN}",
        "MCP_READ_ONLY": "true"
      }
    }
  }
}
```

## Enabling write tools

Writes need all three: `MCP_READ_ONLY=false` here, an account issued with `--allow-writes` (Laravel `is_read_only = false`), and the tool's scope on the token. Even then a write tool only creates a **pending action**; nothing changes until it is approved:

- Tier 1 actions on a token holding `actions:confirm`: this server asks the user through MCP elicitation (if the client supports it). Only an explicit approval sends the single-use confirmation token back to the store. The token is never shown to the model.
- Everything else, and every tier 2 action: a staff member approves it in the store admin (`/admin/api/mcp-actions`, the screen arrives in M15).

Use `get_action_status` to follow an action. Pending actions expire after 15 minutes.

## Errors

Every failure is returned as `isError: true` with structured content `{ "error": { "code", "message", "request_id", "errors"? } }`. Codes include `invalid_input`, `forbidden`, `scope_missing`, `read_only`, `not_found`, `rate_limited`, `upstream_error`, `unknown_tool` and specific store codes such as `retry_not_allowed` or `confirmation_expired`. Stack traces and store exception details are never returned.

## Layout

```
src/config.ts          environment parsing and validation
src/laravel-client.ts  HTTP client for /api/v1/mcp, error mapping
src/tools.ts           tool catalogue: zod input schemas and the Laravel route of each tool
src/server.ts          MCP server: tool registration, validation, rate limit, confirmation flow
src/index.ts           stdio entry point
test/                  node:test suites with a mocked Laravel HTTP layer
scripts/smoke.mjs      end-to-end stdio smoke test against a local mock
```
