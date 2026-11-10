import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { CallToolRequestSchema, ListToolsRequestSchema, type CallToolResult, type Tool } from '@modelcontextprotocol/sdk/types.js';
import { z } from 'zod';
import type { Config } from './config.js';
import { ToolFailure } from './errors.js';
import type { LaravelClient } from './laravel-client.js';
import type { Logger } from './logger.js';
import { TokenBucket } from './rate-limiter.js';
import { ACTION_STATUS_TOOL, TOOLS, type ToolDefinition } from './tools.js';

export const SERVER_NAME = 'egs-store';
export const SERVER_VERSION = '1.0.0';

/**
 * What Laravel says about the token at start-up (GET /api/v1/mcp/session).
 */
export interface SessionInfo {
  account: string;
  is_read_only: boolean;
  scopes: string[];
}

export async function loadSession(client: LaravelClient): Promise<SessionInfo> {
  const response = await client.get('session');
  const parsed = z
    .object({ account: z.string(), is_read_only: z.boolean(), scopes: z.array(z.string()) })
    .safeParse(response.data);

  if (!parsed.success) {
    throw new ToolFailure('upstream_error', 'The store API returned an unexpected session description.');
  }

  return parsed.data;
}

/**
 * Read-only by default, in two layers. Write tools are registered only when MCP_READ_ONLY=false,
 * the account is not read-only in Laravel, and the token holds the tool's scope; Laravel enforces
 * all of this again on every call. Without a session description only read tools are offered.
 */
export function selectTools(config: Pick<Config, 'readOnly'>, session: SessionInfo | null): ToolDefinition[] {
  const reads = TOOLS.filter((tool) => !tool.write && (session === null || session.scopes.includes(tool.scope)));

  if (config.readOnly || session === null || session.is_read_only) {
    return reads;
  }

  const writes = TOOLS.filter((tool) => tool.write && session.scopes.includes(tool.scope));

  return writes.length === 0 ? reads : [...reads, ...writes, ACTION_STATUS_TOOL];
}

export interface ServerDependencies {
  config: Pick<Config, 'readOnly' | 'rateLimitPerMinute'>;
  client: LaravelClient;
  logger: Logger;
  session: SessionInfo | null;
  now?: () => number;
}

const INSTRUCTIONS = [
  'Tools for the Enterprise Gaming Digital Store. All data comes from the store API; customer emails and phone numbers are masked,',
  'and digital codes, credentials, refunds, settings and raw queries are not available. Money is a decimal string with a currency.',
  'Write tools never change anything immediately: they create a pending action. Depending on its risk tier and this account,',
  'the user may be asked to approve it here; otherwise a staff member approves it in the store admin. Use get_action_status to follow it.',
].join(' ');

export function createMcpServer({ config, client, logger, session, now = Date.now }: ServerDependencies): Server {
  const tools = selectTools(config, session);
  const byName = new Map(tools.map((tool) => [tool.name, tool]));
  const schemas = new Map(tools.map((tool) => [tool.name, z.object(tool.input).strict()]));
  const bucket = new TokenBucket(config.rateLimitPerMinute, config.rateLimitPerMinute, now);

  const server = new Server({ name: SERVER_NAME, version: SERVER_VERSION }, { capabilities: { tools: {} }, instructions: INSTRUCTIONS });

  server.setRequestHandler(ListToolsRequestSchema, async () => ({
    tools: tools.map((tool): Tool => ({
      name: tool.name,
      title: tool.title,
      description: tool.description,
      inputSchema: toInputSchema(schemas.get(tool.name)!),
      annotations: {
        title: tool.title,
        readOnlyHint: !tool.write,
        destructiveHint: tool.write && tool !== ACTION_STATUS_TOOL,
        idempotentHint: !tool.write,
        openWorldHint: false,
      },
    })),
  }));

  server.setRequestHandler(CallToolRequestSchema, async (request): Promise<CallToolResult> => {
    const tool = byName.get(request.params.name);

    try {
      if (tool === undefined) {
        throw new ToolFailure('unknown_tool', `No tool named ${request.params.name.slice(0, 64)} is available to this account.`);
      }

      const parsed = schemas.get(tool.name)!.safeParse(request.params.arguments ?? {});

      if (!parsed.success) {
        throw new ToolFailure('invalid_input', 'The arguments are not valid for this tool.', null, fieldErrors(parsed.error));
      }

      if (!bucket.tryTake()) {
        throw new ToolFailure('rate_limited', 'Too many tool calls; wait a little and try again.');
      }

      logger.debug('tool call', { tool: tool.name });

      const route = tool.route(parsed.data);
      const response = route.method === 'GET' ? await client.get(route.path, route.params as Record<string, string | number | undefined>) : await client.post(route.path, route.params);

      if (tool.write && tool !== ACTION_STATUS_TOOL) {
        return success(await settlePendingAction(server, client, logger, response.data));
      }

      return success({ data: response.data, ...(Object.keys(response.meta).length === 0 ? {} : { meta: response.meta }) });
    } catch (error) {
      if (error instanceof ToolFailure) {
        return failure(error);
      }

      logger.error('unexpected tool failure', { tool: request.params.name, reason: error instanceof Error ? error.name : 'unknown' });

      return failure(new ToolFailure('upstream_error', 'The tool failed unexpectedly.'));
    }
  });

  return server;
}

/**
 * Handles the 202 answer of a write tool. The confirmation token stays in this process and is
 * never shown to the model. If Laravel offered one (tier 1 and `actions:confirm`) and the client
 * supports elicitation, the user is asked; only an explicit approval sends the token back.
 */
async function settlePendingAction(server: Server, client: LaravelClient, logger: Logger, data: unknown): Promise<Record<string, unknown>> {
  const action = { ...(isRecord(data) ? data : {}) };
  const confirmationToken = typeof action.confirmation_token === 'string' ? action.confirmation_token : null;
  delete action.confirmation_token;

  const actionId = typeof action.action_id === 'string' ? action.action_id : null;
  const summary = typeof action.summary === 'string' ? action.summary : 'Apply this change to the store?';

  if (confirmationToken === null || actionId === null) {
    return { data: action, next_step: 'Waiting for a staff member to approve it in the store admin. Use get_action_status to follow it.' };
  }

  if (server.getClientCapabilities()?.elicitation === undefined) {
    return { data: action, next_step: 'This client cannot ask for approval; a staff member must approve it in the store admin. Use get_action_status to follow it.' };
  }

  let approved = false;

  try {
    const answer = await server.elicitInput({
      message: `Approve this store change?\n\n${summary}`,
      requestedSchema: {
        type: 'object',
        properties: { approve: { type: 'boolean', title: 'Approve this change', description: summary.slice(0, 500) } },
        required: ['approve'],
      },
    });

    approved = answer.action === 'accept' && answer.content?.approve === true;
  } catch (error) {
    logger.warn('elicitation failed', { reason: error instanceof Error ? error.name : 'unknown' });

    return { data: action, next_step: 'The approval prompt failed; a staff member can approve it in the store admin. Use get_action_status to follow it.' };
  }

  if (!approved) {
    return { data: action, next_step: 'Not approved by the user. Nothing changed; the action expires unless a staff member approves it in the store admin.' };
  }

  const confirmed = await client.post(`actions/${encodeURIComponent(actionId)}/confirm`, { confirmation_token: confirmationToken });

  return { data: confirmed.data, next_step: 'Approved by the user and processed by the store; see status and result.' };
}

function success(structured: Record<string, unknown>): CallToolResult {
  return { structuredContent: structured, content: [{ type: 'text', text: JSON.stringify(structured) }] };
}

function failure(error: ToolFailure): CallToolResult {
  const structured = { error: error.toBody() };

  return { isError: true, structuredContent: structured, content: [{ type: 'text', text: JSON.stringify(structured) }] };
}

function fieldErrors(error: z.ZodError): Record<string, string[]> {
  const result: Record<string, string[]> = {};

  for (const issue of error.issues) {
    const key = issue.path.length === 0 ? '_' : issue.path.map(String).join('.');
    (result[key] ??= []).push(issue.message);
  }

  return result;
}

function toInputSchema(schema: z.ZodObject): Tool['inputSchema'] {
  const json = z.toJSONSchema(schema, { io: 'input' }) as Record<string, unknown>;
  delete json.$schema;

  return { ...json, type: 'object' } as Tool['inputSchema'];
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}
