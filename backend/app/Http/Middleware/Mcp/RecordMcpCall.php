<?php

namespace App\Http\Middleware\Mcp;

use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Services\McpAuditRecorder;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Outermost MCP middleware: every call, including unauthenticated, forbidden, invalid and
 * rate-limited ones, writes one mcp_audit_logs row. Responses are never cached.
 */
class RecordMcpCall
{
    public function __construct(private McpAuditRecorder $recorder) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $started = hrtime(true);

        $response = $next($request);

        $caller = $request->attributes->get(McpCaller::REQUEST_ATTRIBUTE);

        $this->recorder->record(
            $caller instanceof McpCaller ? $caller : null,
            Str::after((string) $request->route()?->getName(), 'mcp.'),
            [...($request->route()?->parameters() ?? []), ...$request->query(), ...$request->json()->all()],
            $response->getStatusCode(),
            $this->errorCode($response),
            intdiv(hrtime(true) - $started, 1_000_000),
            $request->ip(),
        );

        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }

    private function errorCode(Response $response): ?string
    {
        if ($response->getStatusCode() < 400 || ! $response instanceof JsonResponse) {
            return null;
        }

        $code = $response->getData(true)['code'] ?? null;

        return is_string($code) ? $code : null;
    }
}
