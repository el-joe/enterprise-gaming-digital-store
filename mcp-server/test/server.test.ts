import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import type { CallToolResult } from '@modelcontextprotocol/sdk/types.js';
import { selectTools } from '../src/server.js';
import { TOOLS } from '../src/tools.js';
import { harness, ok, READ_SESSION, refused, WRITE_SESSION } from './helpers.js';

const READ_TOOLS = TOOLS.filter((tool) => !tool.write).map((tool) => tool.name);
const WRITE_TOOLS = TOOLS.filter((tool) => tool.write).map((tool) => tool.name);

const pendingAction = (overrides: Record<string, unknown> = {}) => ({
  action_id: '01JCQ5R7ZB9V0000000000000A',
  tool: 'activate_product',
  status: 'pending_approval',
  risk_tier: 1,
  summary: 'Publish product PSN-US (PlayStation Store Card); it is draft now.',
  approval: { admin_panel: true, elicitation: true },
  confirmation_token: 'c'.repeat(48),
  ...overrides,
});

function structured(result: CallToolResult): Record<string, unknown> {
  assert.ok(result.structuredContent, 'structuredContent is always present');
  assert.deepEqual(JSON.parse((result.content[0] as { text: string }).text), result.structuredContent, 'text mirrors structuredContent');

  return result.structuredContent as Record<string, unknown>;
}

describe('tool registration', () => {
  it('lists all 27 read tools and no write tool by default', async () => {
    const h = await harness({ route: () => ok({}) });
    const { tools } = await h.client.listTools();

    assert.deepEqual(tools.map((tool) => tool.name).sort(), [...READ_TOOLS].sort());
    assert.equal(READ_TOOLS.length, 27);
    assert.ok(tools.every((tool) => tool.annotations?.readOnlyHint === true && tool.inputSchema.type === 'object'));
    await h.close();
  });

  it('keeps write tools hidden while MCP_READ_ONLY is true, even for a writable token', () => {
    assert.deepEqual(selectTools({ readOnly: true }, WRITE_SESSION).map((tool) => tool.name).sort(), [...READ_TOOLS].sort());
  });

  it('keeps write tools hidden for a read-only account even when MCP_READ_ONLY is false', () => {
    assert.deepEqual(selectTools({ readOnly: false }, { ...WRITE_SESSION, is_read_only: true }).map((tool) => tool.name).sort(), [...READ_TOOLS].sort());
    assert.deepEqual(selectTools({ readOnly: false }, null).map((tool) => tool.name).sort(), [...READ_TOOLS].sort());
  });

  it('registers only the write tools whose scope the token holds, plus get_action_status', () => {
    const names = selectTools({ readOnly: false }, { ...WRITE_SESSION, scopes: [...READ_SESSION.scopes, 'coupons:write'] }).map((tool) => tool.name);

    assert.ok(names.includes('create_coupon'));
    assert.ok(names.includes('get_action_status'));
    assert.ok(!names.includes('update_product_price'));
    assert.ok(!names.includes('retry_verified_failed_order'));

    const all = selectTools({ readOnly: false }, WRITE_SESSION).map((tool) => tool.name);
    assert.deepEqual(all.filter((name) => WRITE_TOOLS.includes(name)).sort(), [...WRITE_TOOLS].sort());
  });

  it('offers only the read tools of the scopes the token holds', () => {
    const names = selectTools({ readOnly: true }, { ...READ_SESSION, scopes: ['products:read'] }).map((tool) => tool.name);

    assert.deepEqual(names.sort(), ['check_product_availability', 'get_product', 'get_product_pricing', 'list_products', 'search_products']);
  });

  it('has no tool for refunds, codes, credentials, settings or raw queries', () => {
    for (const tool of TOOLS) {
      assert.doesNotMatch(tool.name, /refund|code|credential|secret|setting|sql|query|reveal/);
    }
  });
});

describe('read tools', () => {
  it('returns Laravel data as structured content with pagination meta', async () => {
    const h = await harness({ route: () => ok([{ sku: 'PSN-US' }], { current_page: 1, total: 1 }) });

    const result = await h.call('list_products', { status: 'active', per_page: 5 });

    assert.equal(result.isError, undefined);
    assert.deepEqual(structured(result), { data: [{ sku: 'PSN-US' }], meta: { current_page: 1, total: 1 } });
    assert.equal(h.requests[0]?.url.pathname, '/api/v1/mcp/products');
    assert.equal(h.requests[0]?.url.search, '?status=active&per_page=5');
    await h.close();
  });

  it('builds path parameters safely and passes the date window', async () => {
    const h = await harness({ route: () => ok({}) });

    await h.call('get_order_details', { order_number: 'ABCD1234' });
    await h.call('get_sales_summary', { from: '2026-11-01', to: '2026-11-07', group_by: 'week' });

    assert.equal(h.requests[0]?.url.pathname, '/api/v1/mcp/orders/ABCD1234');
    assert.equal(h.requests[1]?.url.search, '?from=2026-11-01&to=2026-11-07&group_by=week');
    await h.close();
  });

  it('rejects invalid input with a structured error and does not call Laravel', async () => {
    const h = await harness({ route: () => ok({}) });

    for (const [name, args] of [
      ['get_product', { id_or_sku: '../admin' }],
      ['get_product', { id_or_sku: 'a/b' }],
      ['get_sales_summary', { from: 'yesterday', to: '2026-11-07' }],
      ['search_products', { query: 'x' }],
      ['list_products', { per_page: 500 }],
      ['list_products', { unexpected: true }],
      ['search_customer_orders', { email: 'not-an-email' }],
    ] as const) {
      const body = structured(await h.call(name, args));
      assert.equal((body.error as { code: string }).code, 'invalid_input', `${name} ${JSON.stringify(args)}`);
      assert.ok((body.error as { errors?: object }).errors);
    }

    assert.equal(h.requests.length, 0);
    await h.close();
  });

  it('passes Laravel refusals through as structured errors', async () => {
    const h = await harness({ route: () => refused(403, 'scope_missing', 'This token lacks the orders:read scope.') });

    const result = await h.call('list_orders');

    assert.equal(result.isError, true);
    assert.deepEqual(structured(result), { error: { code: 'scope_missing', message: 'This token lacks the orders:read scope.', request_id: '01JCREQUESTID0000000000000' } });
    await h.close();
  });

  it('answers an unknown or unregistered tool with a structured error', async () => {
    const h = await harness({ route: () => ok({}) });

    const result = await h.call('activate_product', { product_sku: 'X', reason: 'Because' });

    assert.equal(result.isError, true);
    assert.equal((structured(result).error as { code: string }).code, 'unknown_tool');
    assert.equal(h.requests.length, 0);
    await h.close();
  });

  it('turns an unexpected failure into a generic error without a stack trace', async () => {
    const h = await harness({
      route: () => ok({}),
      fetchOverride: async () => {
        throw new RangeError('boom at /srv/app/secret.ts:42');
      },
    });

    const result = await h.call('list_products');

    assert.equal(result.isError, true);
    assert.doesNotMatch(JSON.stringify(result), /boom|secret\.ts|RangeError|at \//);
    await h.close();
  });

  it('applies the local rate limit before calling Laravel', async () => {
    const h = await harness({ route: () => ok([]), rateLimitPerMinute: 2 });

    await h.call('list_products');
    await h.call('list_products');
    const third = await h.call('list_products');

    assert.equal((structured(third).error as { code: string }).code, 'rate_limited');
    assert.equal(h.requests.length, 2);
    await h.close();
  });
});

describe('write tools and confirmation', () => {
  it('sends money as a decimal string and refuses a JSON number', async () => {
    const h = await harness({ route: () => ok(pendingAction({ confirmation_token: undefined }), {}, 202), readOnly: false, session: WRITE_SESSION });

    const rejected = await h.call('update_product_price', { variant_sku: 'PSN-25', new_price_base: 27.5, reason: 'Match the market' });
    assert.equal((structured(rejected).error as { code: string }).code, 'invalid_input');

    await h.call('update_product_price', { variant_sku: 'PSN-25', new_price_base: '27.50', reason: 'Match the market' });
    assert.deepEqual(h.requests[0]?.body, { variant_sku: 'PSN-25', new_price_base: '27.50', reason: 'Match the market' });
    await h.close();
  });

  it('returns a pending action without the confirmation token when the client cannot elicit', async () => {
    const h = await harness({ route: () => ok(pendingAction(), {}, 202), readOnly: false, session: WRITE_SESSION });

    const result = await h.call('activate_product', { product_sku: 'PSN-US', reason: 'Launch it now' });
    const body = structured(result);

    assert.equal((body.data as { status: string }).status, 'pending_approval');
    assert.match(String(body.next_step), /store admin/);
    assert.doesNotMatch(JSON.stringify(result), /confirmation_token|cccccccc/);
    assert.equal(h.requests.length, 1);
    await h.close();
  });

  it('asks the user and confirms with the token only after an explicit approval', async () => {
    const prompts: string[] = [];
    const h = await harness({
      route: (request) => request.url.pathname.endsWith('/confirm') ? ok({ ...pendingAction({ status: 'executed', confirmation_token: undefined }), result: { status: 'active' } }) : ok(pendingAction(), {}, 202),
      readOnly: false,
      session: WRITE_SESSION,
      elicitation: (message) => {
        prompts.push(message);

        return { action: 'accept', content: { approve: true } };
      },
    });

    const result = await h.call('activate_product', { product_sku: 'PSN-US', reason: 'Launch it now' });
    const body = structured(result);

    assert.equal(prompts.length, 1);
    assert.match(prompts[0] ?? '', /Publish product PSN-US/);
    assert.equal(h.requests[1]?.url.pathname, '/api/v1/mcp/actions/01JCQ5R7ZB9V0000000000000A/confirm');
    assert.deepEqual(h.requests[1]?.body, { confirmation_token: 'c'.repeat(48) });
    assert.equal((body.data as { status: string }).status, 'executed');
    assert.doesNotMatch(JSON.stringify(result), /cccccccc/);
    await h.close();
  });

  it('does not confirm when the user declines, cancels or leaves approve unchecked', async () => {
    for (const answer of [{ action: 'decline' as const }, { action: 'cancel' as const }, { action: 'accept' as const, content: { approve: false } }]) {
      const h = await harness({ route: () => ok(pendingAction(), {}, 202), readOnly: false, session: WRITE_SESSION, elicitation: () => answer });

      const body = structured(await h.call('activate_product', { product_sku: 'PSN-US', reason: 'Launch it now' }));

      assert.equal(h.requests.length, 1, JSON.stringify(answer));
      assert.match(String(body.next_step), /Not approved/);
      await h.close();
    }
  });

  it('never prompts for a tier 2 action, which Laravel gives no token for', async () => {
    let prompted = false;
    const h = await harness({
      route: () => ok(pendingAction({ risk_tier: 2, confirmation_token: undefined, approval: { admin_panel: true, elicitation: false } }), {}, 202),
      readOnly: false,
      session: WRITE_SESSION,
      elicitation: () => {
        prompted = true;

        return { action: 'accept', content: { approve: true } };
      },
    });

    await h.call('retry_verified_failed_order', { order_number: 'ABCD1234', unit_public_id: '01JCQ5R7ZB9V0000000000000B', reason: 'Supplier rejected it' });

    assert.equal(prompted, false);
    assert.equal(h.requests.length, 1);
    await h.close();
  });

  it('reports an expired or reused confirmation as a structured error', async () => {
    const h = await harness({
      route: (request) => request.url.pathname.endsWith('/confirm') ? refused(410, 'confirmation_expired', 'This action expired before it was confirmed.') : ok(pendingAction(), {}, 202),
      readOnly: false,
      session: WRITE_SESSION,
      elicitation: () => ({ action: 'accept', content: { approve: true } }),
    });

    const result = await h.call('activate_product', { product_sku: 'PSN-US', reason: 'Launch it now' });

    assert.equal(result.isError, true);
    assert.equal((structured(result).error as { code: string }).code, 'confirmation_expired');
    await h.close();
  });

  it('follows a pending action with get_action_status', async () => {
    const h = await harness({ route: () => ok({ action_id: '01JCQ5R7ZB9V0000000000000A', status: 'executed' }), readOnly: false, session: WRITE_SESSION });

    const body = structured(await h.call('get_action_status', { action_id: '01JCQ5R7ZB9V0000000000000A' }));

    assert.equal((body.data as { status: string }).status, 'executed');
    assert.equal(h.requests[0]?.url.pathname, '/api/v1/mcp/actions/01JCQ5R7ZB9V0000000000000A');
    await h.close();
  });
});
