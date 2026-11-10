import type { Config } from './config.js';
import { mapLaravelCode, sanitizeFieldErrors, ToolFailure } from './errors.js';
import type { Logger } from './logger.js';

export type FetchLike = (input: string | URL, init?: RequestInit) => Promise<Response>;

export type QueryValue = string | number | boolean | undefined | null;

export interface ApiSuccess {
  status: number;
  message: string;
  data: unknown;
  meta: Record<string, unknown>;
}

/**
 * HTTP client for the Laravel MCP API (`{EGS_API_BASE_URL}/api/v1/mcp/...`). It sends the bearer
 * token, applies a timeout, and turns every failure into a ToolFailure with a safe message.
 */
export class LaravelClient {
  private readonly root: URL;

  constructor(
    private readonly config: Pick<Config, 'baseUrl' | 'token' | 'timeoutMs'>,
    private readonly logger: Logger,
    private readonly fetchImpl: FetchLike = (input, init) => fetch(input, init),
  ) {
    const base = new URL(config.baseUrl.toString());
    base.pathname = base.pathname.replace(/\/?$/, '/') + 'api/v1/mcp/';
    base.search = '';
    base.hash = '';
    this.root = base;
  }

  get(path: string, query: Record<string, QueryValue> = {}): Promise<ApiSuccess> {
    return this.request('GET', path, query, undefined);
  }

  post(path: string, body: Record<string, unknown>): Promise<ApiSuccess> {
    return this.request('POST', path, {}, body);
  }

  private async request(method: 'GET' | 'POST', path: string, query: Record<string, QueryValue>, body: Record<string, unknown> | undefined): Promise<ApiSuccess> {
    const url = new URL(path.replace(/^\/+/, ''), this.root);

    if (!url.pathname.startsWith(this.root.pathname)) {
      throw new ToolFailure('invalid_input', 'The request path is not allowed.');
    }

    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(key, String(value));
      }
    }

    let response: Response;

    try {
      response = await this.fetchImpl(url, {
        method,
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${this.config.token}`,
          ...(body === undefined ? {} : { 'Content-Type': 'application/json' }),
        },
        body: body === undefined ? undefined : JSON.stringify(body),
        signal: AbortSignal.timeout(this.config.timeoutMs),
        redirect: 'error',
      });
    } catch (error) {
      const timedOut = error instanceof Error && (error.name === 'TimeoutError' || error.name === 'AbortError');
      this.logger.warn('store API request failed', { method, path: url.pathname, reason: error instanceof Error ? error.name : 'unknown' });

      throw new ToolFailure('upstream_error', timedOut ? 'The store API did not answer in time.' : 'The store API could not be reached.');
    }

    const requestId = response.headers.get('x-request-id');
    const payload = await this.readJson(response);

    if (!response.ok) {
      const envelope = isRecord(payload) ? payload : {};
      const code = mapLaravelCode(envelope.code, response.status);
      const message = code === 'upstream_error'
        ? 'The store API returned an error.'
        : typeof envelope.message === 'string' ? envelope.message.slice(0, 500) : 'The request was refused.';

      this.logger.info('store API refused a request', { method, path: url.pathname, status: response.status, code });

      throw new ToolFailure(code, message, typeof envelope.request_id === 'string' ? envelope.request_id : requestId, sanitizeFieldErrors(envelope.errors));
    }

    if (!isRecord(payload) || payload.success !== true) {
      throw new ToolFailure('upstream_error', 'The store API returned an unexpected response.', requestId);
    }

    return {
      status: response.status,
      message: typeof payload.message === 'string' ? payload.message : 'OK',
      data: payload.data ?? null,
      meta: isRecord(payload.meta) ? payload.meta : {},
    };
  }

  private async readJson(response: Response): Promise<unknown> {
    try {
      return JSON.parse(await response.text());
    } catch {
      return null;
    }
  }
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}
