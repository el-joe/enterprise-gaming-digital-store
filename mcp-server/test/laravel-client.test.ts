import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { mapLaravelCode, ToolFailure } from '../src/errors.js';
import { LaravelClient, type FetchLike } from '../src/laravel-client.js';
import { createLogger } from '../src/logger.js';
import { mockLaravel, ok, refused, TEST_CONFIG } from './helpers.js';

const silent = createLogger('silent');

async function failureOf(promise: Promise<unknown>): Promise<ToolFailure> {
  try {
    await promise;
  } catch (error) {
    assert.ok(error instanceof ToolFailure);

    return error;
  }

  assert.fail('expected a ToolFailure');
}

describe('LaravelClient', () => {
  it('calls /api/v1/mcp with the bearer token, JSON headers and the query', async () => {
    const laravel = mockLaravel(() => ok([{ sku: 'A' }], { total: 1 }));
    const client = new LaravelClient({ ...TEST_CONFIG, baseUrl: new URL('https://store.example.com/shop') }, silent, laravel.fetch);

    const result = await client.get('products', { status: 'active', page: 2, platform: undefined });

    assert.deepEqual(result.data, [{ sku: 'A' }]);
    assert.deepEqual(result.meta, { total: 1 });
    const [request] = laravel.requests;
    assert.equal(request?.url.toString(), 'https://store.example.com/shop/api/v1/mcp/products?status=active&page=2');
    assert.equal(request?.headers.authorization, `Bearer ${TEST_CONFIG.token}`);
    assert.equal(request?.headers.accept, 'application/json');
  });

  it('maps the Laravel error envelope to structured errors with field errors', async () => {
    const laravel = mockLaravel(() => refused(422, 'validation_failed', 'The given data was invalid.', { query: ['Too short.'] }));
    const failure = await failureOf(new LaravelClient(TEST_CONFIG, silent, laravel.fetch).get('products/search', { query: 'x' }));

    assert.deepEqual(failure.toBody(), { code: 'invalid_input', message: 'The given data was invalid.', request_id: '01JCREQUESTID0000000000000', errors: { query: ['Too short.'] } });
  });

  it('keeps specific domain codes and maps generic ones', () => {
    assert.equal(mapLaravelCode('scope_missing', 403), 'scope_missing');
    assert.equal(mapLaravelCode('read_only', 403), 'read_only');
    assert.equal(mapLaravelCode('retry_not_allowed', 409), 'retry_not_allowed');
    assert.equal(mapLaravelCode('unauthenticated', 401), 'forbidden');
    assert.equal(mapLaravelCode('rate_limited', 429), 'rate_limited');
    assert.equal(mapLaravelCode(undefined, 404), 'not_found');
    assert.equal(mapLaravelCode('server_error', 500), 'upstream_error');
    assert.equal(mapLaravelCode('Weird Code!', 400), 'upstream_error');
  });

  it('never passes through a server error body or stack trace', async () => {
    const laravel = mockLaravel(() => ({ status: 500, body: '<html>Stack trace: /var/www/app/Foo.php:12 SQLSTATE secret</html>' }));
    const failure = await failureOf(new LaravelClient(TEST_CONFIG, silent, laravel.fetch).get('products'));

    assert.equal(failure.code, 'upstream_error');
    assert.equal(failure.message, 'The store API returned an error.');
    assert.doesNotMatch(JSON.stringify(failure.toBody()), /Stack|SQLSTATE|Foo\.php/);
  });

  it('reports network failures and timeouts as upstream errors', async () => {
    const down: FetchLike = async () => {
      throw new TypeError('fetch failed: connect ECONNREFUSED 127.0.0.1:8000');
    };
    const slow: FetchLike = async () => {
      const error = new Error('The operation timed out.');
      error.name = 'TimeoutError';
      throw error;
    };

    const unreachable = await failureOf(new LaravelClient(TEST_CONFIG, silent, down).get('products'));
    assert.deepEqual([unreachable.code, unreachable.message], ['upstream_error', 'The store API could not be reached.']);
    assert.doesNotMatch(unreachable.message, /ECONNREFUSED/);

    const timedOut = await failureOf(new LaravelClient(TEST_CONFIG, silent, slow).get('products'));
    assert.equal(timedOut.message, 'The store API did not answer in time.');
  });

  it('refuses a path that would leave the MCP API', async () => {
    const laravel = mockLaravel(() => ok({}));
    const failure = await failureOf(new LaravelClient(TEST_CONFIG, silent, laravel.fetch).get('../../admin/api/me'));

    assert.equal(failure.code, 'invalid_input');
    assert.equal(laravel.requests.length, 0);
  });

  it('rejects a success status without the success envelope', async () => {
    const laravel = mockLaravel(() => ({ status: 200, body: 'plain text' }));

    assert.equal((await failureOf(new LaravelClient(TEST_CONFIG, silent, laravel.fetch).get('products'))).code, 'upstream_error');
  });

  it('does not log the token', async () => {
    const lines: string[] = [];
    const logger = createLogger('debug', (line) => lines.push(line));
    const laravel = mockLaravel(() => refused(403, 'scope_missing'));

    await failureOf(new LaravelClient(TEST_CONFIG, logger, laravel.fetch).get('products'));
    logger.info('probe', { token: TEST_CONFIG.token, nested: { api_token: TEST_CONFIG.token } });

    assert.ok(lines.length > 0);
    assert.ok(lines.every((line) => !line.includes('test-token-value-not-real')));
  });
});
