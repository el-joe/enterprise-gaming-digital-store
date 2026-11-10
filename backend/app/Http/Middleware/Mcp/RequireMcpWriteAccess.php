<?php

namespace App\Http\Middleware\Mcp;

use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Services\McpPendingActionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Before any argument is validated: the token must hold the write tool's scope and the account
 * must not be read-only. The service checks the same again when the action is stored and run.
 */
class RequireMcpWriteAccess
{
    public function __construct(private McpPendingActionService $actions) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $caller = $request->attributes->get(McpCaller::REQUEST_ATTRIBUTE);

        if (! $caller instanceof McpCaller) {
            throw new LogicException('MCP write route without MCP authentication.');
        }

        $this->actions->assertMayWrite($caller->account, $caller->scopes, McpWriteTool::from(Str::after((string) $request->route()?->getName(), 'mcp.')));

        return $next($request);
    }
}
