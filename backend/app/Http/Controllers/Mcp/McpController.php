<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\DTOs\McpCaller;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LogicException;

/**
 * Base for /api/v1/mcp controllers. Authentication, scopes, rate limiting and the audit row are
 * route middleware; controllers only translate HTTP to the MCP read services and repositories.
 */
abstract class McpController extends Controller
{
    protected function caller(Request $request): McpCaller
    {
        $caller = $request->attributes->get(McpCaller::REQUEST_ATTRIBUTE);

        return $caller instanceof McpCaller ? $caller : throw new LogicException('MCP route without MCP authentication.');
    }
}
