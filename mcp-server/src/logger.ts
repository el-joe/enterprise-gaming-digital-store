import type { LogLevel } from './config.js';

const ORDER: Record<LogLevel, number> = { debug: 10, info: 20, warn: 30, error: 40, silent: 100 };

export interface Logger {
  debug(message: string, context?: Record<string, unknown>): void;
  info(message: string, context?: Record<string, unknown>): void;
  warn(message: string, context?: Record<string, unknown>): void;
  error(message: string, context?: Record<string, unknown>): void;
}

/**
 * JSON lines on stderr. stdout carries only the MCP protocol. Values under keys that look like
 * secrets are replaced, so a token can never be logged by mistake.
 */
export function createLogger(level: LogLevel, write: (line: string) => void = (line) => process.stderr.write(line + '\n')): Logger {
  const log = (at: Exclude<LogLevel, 'silent'>, message: string, context?: Record<string, unknown>): void => {
    if (ORDER[at] < ORDER[level]) {
      return;
    }

    write(JSON.stringify({ time: new Date().toISOString(), level: at, message, ...(context === undefined ? {} : { context: redact(context) }) }));
  };

  return {
    debug: (message, context) => log('debug', message, context),
    info: (message, context) => log('info', message, context),
    warn: (message, context) => log('warn', message, context),
    error: (message, context) => log('error', message, context),
  };
}

const SENSITIVE = /token|secret|password|authorization|credential|code$/i;

function redact(value: Record<string, unknown>): Record<string, unknown> {
  return Object.fromEntries(
    Object.entries(value).map(([key, item]) => [
      key,
      SENSITIVE.test(key) ? '[REDACTED]' : item !== null && typeof item === 'object' && !Array.isArray(item) ? redact(item as Record<string, unknown>) : item,
    ]),
  );
}
