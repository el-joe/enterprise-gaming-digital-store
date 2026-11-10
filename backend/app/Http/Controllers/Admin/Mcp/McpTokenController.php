<?php

namespace App\Http\Controllers\Admin\Mcp;

use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Services\McpTokenService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Mcp\IssueMcpTokenRequest;
use Illuminate\Http\JsonResponse;

/**
 * Issue, list and revoke MCP tokens (`service_accounts.manage`). The plain token appears once,
 * in the 201 response; the admin screen arrives in M15.
 */
class McpTokenController extends Controller
{
    public function __construct(private McpTokenService $tokens) {}

    public function index(): JsonResponse
    {
        return $this->success(['tokens' => $this->tokens->list()->all()]);
    }

    public function store(IssueMcpTokenRequest $request): JsonResponse
    {
        $issued = $this->tokens->issue(
            accountName: (string) $request->validated('account'),
            scopes: $request->scopes(),
            allowWrites: (bool) $request->validated('allow_writes', false),
            rateLimitPerMinute: $request->validated('rate_limit_per_minute'),
            allowedIps: $request->validated('allowed_ips'),
            ttlDays: $request->validated('expires_in_days'),
        );

        return $this->success([
            'token' => $issued['plain_text'],
            'token_id' => $issued['token']->getKey(),
            'account' => $issued['account']->name,
            'is_read_only' => $issued['account']->is_read_only,
            'scopes' => array_map(static fn (McpScope $scope): string => $scope->value, McpScope::fromAbilities($issued['token']->abilities ?? [])),
            'expires_at' => $issued['token']->expires_at?->toIso8601String(),
        ], 'Token issued. It is shown only once.', status: 201);
    }

    public function destroy(int $tokenId): JsonResponse
    {
        $this->tokens->revoke($tokenId);

        return $this->success(message: 'Token revoked.');
    }
}
