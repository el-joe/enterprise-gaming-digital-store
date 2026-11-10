<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Issues and revokes MCP service-account tokens (artisan `mcp:token` and the admin JSON endpoint).
 *
 * Read-only by default: a new account is created with `is_read_only = true` and only read scopes
 * are granted unless the caller explicitly allows writes. The plain token is returned once and is
 * never stored, logged or audited.
 */
class McpTokenService
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * @param  list<McpScope>  $scopes
     * @param  list<string>|null  $allowedIps
     * @return array{account: ServiceAccount, token: PersonalAccessToken, plain_text: string}
     */
    public function issue(
        string $accountName,
        array $scopes,
        bool $allowWrites = false,
        ?int $rateLimitPerMinute = null,
        ?array $allowedIps = null,
        ?int $ttlDays = null,
        string $tokenName = 'mcp',
    ): array {
        $scopes = array_values(array_unique($scopes === [] ? McpScope::readScopes() : $scopes, SORT_REGULAR));
        $writeScopes = array_values(array_filter($scopes, static fn (McpScope $scope): bool => $scope->isWrite() || $scope === McpScope::ActionsConfirm));

        if ($writeScopes !== [] && ! $allowWrites) {
            throw new ApplicationException(
                'Write scopes need writes to be allowed explicitly.',
                'validation_failed',
                422,
                ['scopes' => ['Write scopes ('.implode(', ', array_map(static fn (McpScope $scope): string => $scope->value, $writeScopes)).') need writes to be allowed explicitly.']],
            );
        }

        $ttlDays ??= (int) config('mcp.token_ttl_days');

        /** @var array{account: ServiceAccount, token: NewAccessToken} $issued */
        $issued = DB::transaction(function () use ($accountName, $scopes, $writeScopes, $rateLimitPerMinute, $allowedIps, $ttlDays, $tokenName): array {
            $account = ServiceAccount::query()->withTrashed()->where('name', $accountName)->lockForUpdate()->first();

            if ($account !== null && ($account->kind !== ServiceAccountKind::Mcp || ! $account->isUsable())) {
                throw new ApplicationException('This service account cannot receive MCP tokens.', 'conflict', 409);
            }

            $account ??= new ServiceAccount(['name' => $accountName, 'kind' => ServiceAccountKind::Mcp, 'is_read_only' => true]);

            if ($writeScopes !== []) {
                $account->is_read_only = false;
            }

            if ($rateLimitPerMinute !== null) {
                $account->rate_limit_per_minute = $rateLimitPerMinute;
            }

            if ($allowedIps !== null) {
                $account->allowed_ips = $allowedIps === [] ? null : $allowedIps;
            }

            $account->save();

            $token = $account->createToken(
                $tokenName,
                array_map(static fn (McpScope $scope): string => $scope->ability(), $scopes),
                now()->addDays($ttlDays),
            );

            $this->audit->record(new AuditEntry(
                action: 'service_accounts.mcp_token_issued',
                subject: $account,
                after: [
                    'token_id' => $token->accessToken->getKey(),
                    'scopes' => array_map(static fn (McpScope $scope): string => $scope->value, $scopes),
                    'is_read_only' => $account->is_read_only,
                    'rate_limit_per_minute' => $account->rate_limit_per_minute,
                    'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
                ],
            ));

            return ['account' => $account, 'token' => $token];
        });

        return ['account' => $issued['account']->refresh(), 'token' => $issued['token']->accessToken, 'plain_text' => $issued['token']->plainTextToken];
    }

    public function revoke(int $tokenId): void
    {
        DB::transaction(function () use ($tokenId): void {
            $token = $this->mcpTokens()->whereKey($tokenId)->lockForUpdate()->first();

            if ($token === null) {
                throw new ApplicationException('Token not found.', 'not_found', 404);
            }

            $account = ServiceAccount::query()->withTrashed()->findOrFail($token->tokenable_id);
            $token->delete();

            $this->audit->record(new AuditEntry(
                action: 'service_accounts.mcp_token_revoked',
                subject: $account,
                before: ['token_id' => $tokenId, 'name' => $token->name],
            ));
        });
    }

    /**
     * Every MCP token with its account; never the token value.
     *
     * @return Collection<int, array{id: int, name: string, account: string, account_id: int, is_read_only: bool, scopes: list<string>, last_used_at: ?string, expires_at: ?string, created_at: ?string}>
     */
    public function list(): Collection
    {
        $tokens = $this->mcpTokens()->orderByDesc('id')->get();
        $accounts = ServiceAccount::query()->withTrashed()->whereIn('id', $tokens->pluck('tokenable_id')->unique())->get()->keyBy('id');

        return $tokens->map(static function (PersonalAccessToken $token) use ($accounts): array {
            $account = $accounts->get($token->tokenable_id);

            return [
                'id' => (int) $token->getKey(),
                'name' => (string) $token->name,
                'account' => (string) $account?->name,
                'account_id' => (int) $token->tokenable_id,
                'is_read_only' => (bool) ($account->is_read_only ?? true),
                'scopes' => array_map(static fn (McpScope $scope): string => $scope->value, McpScope::fromAbilities($token->abilities ?? [])),
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ];
        })->values();
    }

    /**
     * @return Builder<PersonalAccessToken>
     */
    private function mcpTokens(): Builder
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', (new ServiceAccount)->getMorphClass())
            ->whereIn('tokenable_id', ServiceAccount::query()->withTrashed()->select('id')->where('kind', ServiceAccountKind::Mcp));
    }
}
