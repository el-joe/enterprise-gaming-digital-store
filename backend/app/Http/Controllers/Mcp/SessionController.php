<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Services\McpFormat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The calling account as the MCP server needs it at start-up: name, read-only flag, scopes, rate
 * limit and token expiry. Never the token or any secret.
 */
class SessionController extends McpController
{
    public function show(Request $request): JsonResponse
    {
        $caller = $this->caller($request);

        return $this->success([
            'account' => $caller->account->name,
            'is_read_only' => $caller->account->is_read_only,
            'scopes' => array_map(static fn (McpScope $scope): string => $scope->value, $caller->scopes),
            'rate_limit_per_minute' => $caller->account->rate_limit_per_minute,
            'token_expires_at' => McpFormat::iso($caller->token->expires_at),
        ]);
    }
}
