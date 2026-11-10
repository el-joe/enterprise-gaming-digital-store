import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { ConfigError, loadConfig } from '../src/config.js';

const base = { EGS_API_BASE_URL: 'https://store.example.com', EGS_API_TOKEN: 'placeholder' };

describe('loadConfig', () => {
  it('is read-only by default and reads everything from the environment', () => {
    const config = loadConfig(base);

    assert.equal(config.readOnly, true);
    assert.equal(config.baseUrl.toString(), 'https://store.example.com/');
    assert.equal(config.token, 'placeholder');
    assert.equal(config.timeoutMs, 15000);
    assert.equal(config.rateLimitPerMinute, 60);
  });

  it('enables writes only for MCP_READ_ONLY=false', () => {
    assert.equal(loadConfig({ ...base, MCP_READ_ONLY: 'false' }).readOnly, false);
    assert.equal(loadConfig({ ...base, MCP_READ_ONLY: 'FALSE' }).readOnly, false);
    assert.equal(loadConfig({ ...base, MCP_READ_ONLY: '0' }).readOnly, true);
    assert.equal(loadConfig({ ...base, MCP_READ_ONLY: 'no' }).readOnly, true);
    assert.equal(loadConfig({ ...base, MCP_READ_ONLY: '' }).readOnly, true);
  });

  it('requires the base URL and the token', () => {
    assert.throws(() => loadConfig({ EGS_API_TOKEN: 'x' }), ConfigError);
    assert.throws(() => loadConfig({ EGS_API_BASE_URL: 'https://store.example.com' }), ConfigError);
    assert.throws(() => loadConfig({ ...base, EGS_API_TOKEN: '' }), ConfigError);
  });

  it('requires https except on localhost, and no credentials in the URL', () => {
    assert.throws(() => loadConfig({ ...base, EGS_API_BASE_URL: 'http://store.example.com' }), /https/);
    assert.equal(loadConfig({ ...base, EGS_API_BASE_URL: 'http://localhost:8000' }).baseUrl.port, '8000');
    assert.equal(loadConfig({ ...base, EGS_API_BASE_URL: 'http://127.0.0.1:8000' }).baseUrl.hostname, '127.0.0.1');
    assert.throws(() => loadConfig({ ...base, EGS_API_BASE_URL: 'https://user:pass@store.example.com' }), /credentials/);
    assert.throws(() => loadConfig({ ...base, EGS_API_BASE_URL: 'not a url' }), ConfigError);
  });

  it('accepts only the stdio transport and sane limits', () => {
    assert.throws(() => loadConfig({ ...base, MCP_TRANSPORT: 'http' }), /stdio/);
    assert.throws(() => loadConfig({ ...base, MCP_HTTP_TIMEOUT_MS: '5' }), ConfigError);
    assert.throws(() => loadConfig({ ...base, MCP_RATE_LIMIT_PER_MINUTE: 'lots' }), ConfigError);
    assert.throws(() => loadConfig({ ...base, MCP_LOG_LEVEL: 'verbose' }), ConfigError);
  });
});
