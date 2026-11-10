import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { InMemoryTransport } from '@modelcontextprotocol/sdk/inMemory.js';
import { ElicitRequestSchema, type CallToolResult, type ElicitResult } from '@modelcontextprotocol/sdk/types.js';
import { LaravelClient, type FetchLike } from '../src/laravel-client.js';
import { createLogger } from '../src/logger.js';
import { createMcpServer, type SessionInfo } from '../src/server.js';

export interface RecordedRequest {
  method: string;
  url: URL;
  headers: Record<string, string>;
  body: unknown;
}

export type Route = (request: RecordedRequest) => { status: number; body: unknown; headers?: Record<string, string> } | Promise<{ status: number; body: unknown; headers?: Record<string, string> }>;

/**
 * A mocked Laravel HTTP layer: every request is recorded and answered by `route`.
 */
export function mockLaravel(route: Route): { fetch: FetchLike; requests: RecordedRequest[] } {
  const requests: RecordedRequest[] = [];

  const fetchImpl: FetchLike = async (input, init) => {
    const headers = Object.fromEntries(Object.entries((init?.headers ?? {}) as Record<string, string>).map(([key, value]) => [key.toLowerCase(), value]));
    const recorded: RecordedRequest = {
      method: init?.method ?? 'GET',
      url: new URL(input.toString()),
      headers,
      body: typeof init?.body === 'string' ? JSON.parse(init.body) : undefined,
    };
    requests.push(recorded);

    const answer = await route(recorded);
    const text = typeof answer.body === 'string' ? answer.body : JSON.stringify(answer.body);

    return new Response(text, { status: answer.status, headers: { 'content-type': 'application/json', ...(answer.headers ?? {}) } });
  };

  return { fetch: fetchImpl, requests };
}

export const ok = (data: unknown, meta: Record<string, unknown> = {}, status = 200) => ({ status, body: { success: true, message: 'OK', data, meta } });

export const refused = (status: number, code: string, message = 'Refused.', errors?: Record<string, string[]>) => ({
  status,
  body: { success: false, message, code, ...(errors === undefined ? {} : { errors }), request_id: '01JCREQUESTID0000000000000' },
});

export const TEST_CONFIG = {
  baseUrl: new URL('http://localhost:8000'),
  token: '7|test-token-value-not-real',
  timeoutMs: 2000,
  readOnly: true,
  rateLimitPerMinute: 60,
};

export const READ_SESSION: SessionInfo = {
  account: 'test-agent',
  is_read_only: true,
  scopes: ['products:read', 'orders:read', 'analytics:read', 'suppliers:read', 'marketing:read', 'operations:read'],
};

export const WRITE_SESSION: SessionInfo = {
  account: 'test-writer',
  is_read_only: false,
  scopes: [...READ_SESSION.scopes, 'products:write', 'coupons:write', 'catalog:sync', 'orders:retry', 'actions:confirm'],
};

export interface Harness {
  client: Client;
  requests: RecordedRequest[];
  call(name: string, args?: Record<string, unknown>): Promise<CallToolResult>;
  close(): Promise<void>;
}

/**
 * Start the MCP server against the mock and connect an SDK client over an in-memory transport.
 */
export async function harness(options: {
  route: Route;
  session?: SessionInfo | null;
  readOnly?: boolean;
  rateLimitPerMinute?: number;
  elicitation?: (message: string) => ElicitResult;
  fetchOverride?: FetchLike;
}): Promise<Harness> {
  const laravel = mockLaravel(options.route);
  const logger = createLogger('silent');
  const apiClient = new LaravelClient(TEST_CONFIG, logger, options.fetchOverride ?? laravel.fetch);
  const server = createMcpServer({
    config: { readOnly: options.readOnly ?? true, rateLimitPerMinute: options.rateLimitPerMinute ?? 60 },
    client: apiClient,
    logger,
    session: options.session === undefined ? READ_SESSION : options.session,
  });

  const client = new Client({ name: 'test-client', version: '1.0.0' }, { capabilities: options.elicitation === undefined ? {} : { elicitation: { form: {} } } });

  if (options.elicitation !== undefined) {
    const answer = options.elicitation;
    client.setRequestHandler(ElicitRequestSchema, async (request) => answer(request.params.message));
  }

  const [clientTransport, serverTransport] = InMemoryTransport.createLinkedPair();
  await server.connect(serverTransport);
  await client.connect(clientTransport);

  return {
    client,
    requests: laravel.requests,
    call: async (name, args = {}) => (await client.callTool({ name, arguments: args })) as CallToolResult,
    close: async () => {
      await client.close();
      await server.close();
    },
  };
}
