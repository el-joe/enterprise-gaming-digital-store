// End-to-end smoke test: starts the built server (dist/index.js) as a child process over stdio,
// lists its tools and calls a read tool against a local mock of the Laravel MCP API. It also
// starts it a second time with writes enabled for a writable mock token.
// Run with `npm run smoke`. Uses only Node built-ins and the MCP SDK client.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { StdioClientTransport } from '@modelcontextprotocol/sdk/client/stdio.js';

const TOKEN = '1|smoke-placeholder-token';
const READ_SCOPES = ['products:read', 'orders:read', 'analytics:read', 'suppliers:read', 'marketing:read', 'operations:read'];

function startMockLaravel(session) {
  const seen = [];
  const server = createServer((request, response) => {
    seen.push(`${request.method} ${request.url}`);
    const send = (status, body) => {
      response.writeHead(status, { 'content-type': 'application/json', 'x-request-id': '01JCSMQKE0000000000000000A' });
      response.end(JSON.stringify(body));
    };

    if (request.headers.authorization !== `Bearer ${TOKEN}`) {
      return send(401, { success: false, message: 'Unauthenticated.', code: 'unauthenticated', request_id: '01JCSMQKE0000000000000000A' });
    }

    const url = new URL(request.url, 'http://localhost');

    if (url.pathname === '/api/v1/mcp/session') {
      return send(200, { success: true, message: 'OK', data: session, meta: {} });
    }

    if (url.pathname === '/api/v1/mcp/products') {
      return send(200, {
        success: true,
        message: 'OK',
        data: [{ id: '01JCSMQKE0000000000000000B', sku: 'PSN-US', name: 'PlayStation Store Card', status: url.searchParams.get('status') ?? 'active' }],
        meta: { current_page: 1, per_page: 20, total: 1, last_page: 1 },
      });
    }

    return send(404, { success: false, message: 'The requested resource was not found.', code: 'not_found', request_id: '01JCSMQKE0000000000000000A' });
  });

  return new Promise((resolve) => server.listen(0, '127.0.0.1', () => resolve({ server, seen, port: server.address().port })));
}

async function connect(port, readOnly) {
  const transport = new StdioClientTransport({
    command: process.execPath,
    args: ['dist/index.js'],
    env: { PATH: process.env.PATH ?? '', EGS_API_BASE_URL: `http://127.0.0.1:${port}`, EGS_API_TOKEN: TOKEN, MCP_READ_ONLY: readOnly, MCP_LOG_LEVEL: 'warn' },
    stderr: 'pipe',
  });
  const client = new Client({ name: 'smoke', version: '1.0.0' });
  await client.connect(transport);

  return client;
}

const results = {};

// 1. Default (read-only) process.
const readOnly = await startMockLaravel({ account: 'smoke-agent', is_read_only: true, scopes: READ_SCOPES });
const client = await connect(readOnly.port, 'true');

const { tools } = await client.listTools();
assert.equal(tools.length, 27, 'read-only server lists the 27 read tools');
assert.ok(!tools.some((tool) => tool.name === 'update_product_price'), 'no write tool in read-only mode');
results.read_only_tools = tools.length;

const listed = await client.callTool({ name: 'list_products', arguments: { status: 'active' } });
assert.equal(listed.isError, undefined);
assert.equal(listed.structuredContent.data[0].sku, 'PSN-US');
assert.equal(listed.structuredContent.meta.total, 1);
results.list_products = listed.structuredContent.data[0].sku;

const invalid = await client.callTool({ name: 'get_product', arguments: { id_or_sku: '../../etc' } });
assert.equal(invalid.isError, true);
assert.equal(invalid.structuredContent.error.code, 'invalid_input');
results.invalid_input = invalid.structuredContent.error.code;

const missing = await client.callTool({ name: 'get_product', arguments: { id_or_sku: 'NOPE' } });
assert.equal(missing.structuredContent.error.code, 'not_found');
results.not_found = missing.structuredContent.error.code;

await client.close();
readOnly.server.close();
assert.ok(readOnly.seen.includes('GET /api/v1/mcp/products?status=active'));

// 2. Writes enabled in both layers: write tools are listed (nothing is called).
const writable = await startMockLaravel({ account: 'smoke-writer', is_read_only: false, scopes: [...READ_SCOPES, 'products:write'] });
const writer = await connect(writable.port, 'false');
const writeTools = (await writer.listTools()).tools.map((tool) => tool.name);
assert.ok(writeTools.includes('update_product_price') && writeTools.includes('activate_product') && writeTools.includes('get_action_status'));
assert.ok(!writeTools.includes('create_coupon'), 'tools for scopes the token lacks stay hidden');
results.writable_tools = writeTools.length;
await writer.close();
writable.server.close();

process.stdout.write(`smoke ok ${JSON.stringify(results)}\n`);
