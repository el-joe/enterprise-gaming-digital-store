<?php

namespace App\Http\Middleware\Mcp;

use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `mcp.scope:products:read` requires that exact scope on the token. A wildcard ability grants
 * nothing here.
 */
class RequireMcpScope
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $caller = $request->attributes->get(McpCaller::REQUEST_ATTRIBUTE);
        $required = McpScope::from($scope);

        if (! $caller instanceof McpCaller || ! $caller->has($required)) {
            throw new ApplicationException("This token lacks the {$required->value} scope.", 'scope_missing', 403);
        }

        return $next($request);
    }
}
