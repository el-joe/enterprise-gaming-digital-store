import { z } from 'zod';

/**
 * Runtime configuration, read only from the environment. Nothing is hardcoded: the base URL and
 * token must be supplied by the MCP client configuration.
 */
export interface Config {
  baseUrl: URL;
  token: string;
  readOnly: boolean;
  logLevel: LogLevel;
  timeoutMs: number;
  rateLimitPerMinute: number;
}

export type LogLevel = 'debug' | 'info' | 'warn' | 'error' | 'silent';

export class ConfigError extends Error {}

const LOCAL_HOSTS = new Set(['localhost', '127.0.0.1', '[::1]', '::1']);

const EnvSchema = z.object({
  EGS_API_BASE_URL: z.string().min(1, 'EGS_API_BASE_URL is required'),
  EGS_API_TOKEN: z.string().min(1, 'EGS_API_TOKEN is required'),
  MCP_READ_ONLY: z.string().optional(),
  MCP_TRANSPORT: z.string().optional(),
  MCP_LOG_LEVEL: z.enum(['debug', 'info', 'warn', 'error', 'silent']).optional(),
  MCP_HTTP_TIMEOUT_MS: z.string().regex(/^\d+$/, 'MCP_HTTP_TIMEOUT_MS must be an integer').optional(),
  MCP_RATE_LIMIT_PER_MINUTE: z.string().regex(/^\d+$/, 'MCP_RATE_LIMIT_PER_MINUTE must be an integer').optional(),
});

/**
 * Parse and validate the environment. Read-only unless MCP_READ_ONLY is exactly "false".
 * The base URL must be HTTPS except for a local development host.
 */
export function loadConfig(env: NodeJS.ProcessEnv): Config {
  const cleaned = Object.fromEntries(Object.entries(env).filter(([, value]) => value !== undefined && value !== ''));
  const parsed = EnvSchema.safeParse(cleaned);

  if (!parsed.success) {
    throw new ConfigError(parsed.error.issues.map((issue) => issue.message).join('; '));
  }

  const values = parsed.data;

  if ((values.MCP_TRANSPORT ?? 'stdio') !== 'stdio') {
    throw new ConfigError('Only MCP_TRANSPORT=stdio is supported by this build.');
  }

  let baseUrl: URL;

  try {
    baseUrl = new URL(values.EGS_API_BASE_URL);
  } catch {
    throw new ConfigError('EGS_API_BASE_URL is not a valid URL.');
  }

  if (baseUrl.protocol !== 'https:' && !(baseUrl.protocol === 'http:' && LOCAL_HOSTS.has(baseUrl.hostname))) {
    throw new ConfigError('EGS_API_BASE_URL must use https (plain http is allowed only for localhost).');
  }

  if (baseUrl.username !== '' || baseUrl.password !== '') {
    throw new ConfigError('EGS_API_BASE_URL must not contain credentials.');
  }

  const timeoutMs = Number(values.MCP_HTTP_TIMEOUT_MS ?? '15000');
  const rateLimitPerMinute = Number(values.MCP_RATE_LIMIT_PER_MINUTE ?? '60');

  if (timeoutMs < 1000 || timeoutMs > 120000) {
    throw new ConfigError('MCP_HTTP_TIMEOUT_MS must be between 1000 and 120000.');
  }

  if (rateLimitPerMinute < 1 || rateLimitPerMinute > 6000) {
    throw new ConfigError('MCP_RATE_LIMIT_PER_MINUTE must be between 1 and 6000.');
  }

  return {
    baseUrl,
    token: values.EGS_API_TOKEN,
    readOnly: (values.MCP_READ_ONLY ?? 'true').trim().toLowerCase() !== 'false',
    logLevel: values.MCP_LOG_LEVEL ?? 'info',
    timeoutMs,
    rateLimitPerMinute,
  };
}
