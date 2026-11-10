/**
 * The structured error every tool returns on failure (MCP_SERVER.md section 6):
 * `{ code, message, request_id }`, plus field errors for invalid input. Laravel exceptions and
 * stack traces are never passed through.
 */
export interface ToolErrorBody {
  code: string;
  message: string;
  request_id: string | null;
  errors?: Record<string, string[]>;
}

export class ToolFailure extends Error {
  constructor(
    public readonly code: string,
    message: string,
    public readonly requestId: string | null = null,
    public readonly errors?: Record<string, string[]>,
  ) {
    super(message);
  }

  toBody(): ToolErrorBody {
    return {
      code: this.code,
      message: this.message,
      request_id: this.requestId,
      ...(this.errors === undefined ? {} : { errors: this.errors }),
    };
  }
}

/**
 * Laravel error codes are kept when they are specific (for example `retry_not_allowed` or
 * `confirmation_expired`); generic ones are mapped to the MCP error vocabulary.
 */
export function mapLaravelCode(code: unknown, status: number): string {
  if (status === 429) {
    return 'rate_limited';
  }

  if (status >= 500) {
    return 'upstream_error';
  }

  switch (code) {
    case 'validation_failed':
      return 'invalid_input';
    case 'unauthenticated':
      return 'forbidden';
    case undefined:
    case null:
      return status === 404 ? 'not_found' : status === 401 || status === 403 ? 'forbidden' : 'upstream_error';
    default:
      return typeof code === 'string' && /^[a-z][a-z0-9_]{1,63}$/.test(code) ? code : 'upstream_error';
  }
}

/**
 * Field errors from the envelope, kept only when they have the expected shape.
 */
export function sanitizeFieldErrors(value: unknown): Record<string, string[]> | undefined {
  if (value === null || typeof value !== 'object' || Array.isArray(value)) {
    return undefined;
  }

  const result: Record<string, string[]> = {};

  for (const [field, messages] of Object.entries(value as Record<string, unknown>)) {
    if (Array.isArray(messages)) {
      result[field.slice(0, 120)] = messages.filter((item): item is string => typeof item === 'string').map((item) => item.slice(0, 300));
    }
  }

  return Object.keys(result).length === 0 ? undefined : result;
}
