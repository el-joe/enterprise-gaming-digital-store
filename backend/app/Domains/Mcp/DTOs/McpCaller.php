<?php

namespace App\Domains\Mcp\DTOs;

use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpScope;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * The authenticated MCP service account and the exact token used for this request.
 */
final readonly class McpCaller
{
    public const string REQUEST_ATTRIBUTE = 'mcp.caller';

    /**
     * @param  list<McpScope>  $scopes
     */
    public function __construct(
        public ServiceAccount $account,
        public PersonalAccessToken $token,
        public array $scopes,
    ) {}

    public function has(McpScope $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    public function tokenId(): int
    {
        return (int) $this->token->getKey();
    }
}
