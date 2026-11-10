<?php

namespace App\Console\Commands\Mcp;

use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Services\McpTokenService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mcp:token
    {action : issue, revoke or list}
    {account? : Service account name (issue)}
    {--scope=* : Scope to grant, repeatable; default all read scopes}
    {--allow-writes : Allow write scopes and make the account writable}
    {--rate-limit= : Requests per minute for the account}
    {--ip=* : Allowed IP or CIDR, repeatable}
    {--days= : Token lifetime in days}
    {--id= : Token id (revoke)}')]
#[Description('Issue, list or revoke MCP service-account tokens (read-only by default)')]
class McpTokenCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(McpTokenService $tokens): int
    {
        try {
            return match ($this->argument('action')) {
                'issue' => $this->issue($tokens),
                'revoke' => $this->revoke($tokens),
                'list' => $this->listTokens($tokens),
                default => $this->failWith('Action must be issue, revoke or list.'),
            };
        } catch (ApplicationException $exception) {
            return $this->failWith(implode(' ', [$exception->getMessage(), ...array_merge(...array_values($exception->errors))]));
        }
    }

    private function issue(McpTokenService $tokens): int
    {
        $account = (string) $this->argument('account');

        if ($account === '' || preg_match('/^[A-Za-z0-9 _.-]{3,120}$/', $account) !== 1) {
            return $this->failWith('Give a service account name of 3 to 120 letters, digits, spaces, dots, dashes or underscores.');
        }

        $scopes = [];

        foreach ((array) $this->option('scope') as $value) {
            $scope = McpScope::tryFrom((string) $value);

            if ($scope === null) {
                return $this->failWith("Unknown scope {$value}. Known: ".implode(', ', array_map(static fn (McpScope $case): string => $case->value, McpScope::cases())));
            }

            $scopes[] = $scope;
        }

        $issued = $tokens->issue(
            accountName: $account,
            scopes: $scopes,
            allowWrites: (bool) $this->option('allow-writes'),
            rateLimitPerMinute: $this->option('rate-limit') === null ? null : max(1, (int) $this->option('rate-limit')),
            allowedIps: $this->option('ip') === [] ? null : array_values((array) $this->option('ip')),
            ttlDays: $this->option('days') === null ? null : max(1, (int) $this->option('days')),
        );

        $this->info("Token {$issued['token']->getKey()} issued to {$issued['account']->name} (".($issued['account']->is_read_only ? 'read-only' : 'writes allowed').').');
        $this->line('Scopes: '.implode(', ', array_map(static fn (McpScope $scope): string => $scope->value, McpScope::fromAbilities($issued['token']->abilities ?? []))));
        $this->line('Expires: '.($issued['token']->expires_at?->toIso8601String() ?? 'never'));
        $this->warn('Store this token now; it is not shown again:');
        $this->line($issued['plain_text']);

        return self::SUCCESS;
    }

    private function revoke(McpTokenService $tokens): int
    {
        $id = $this->option('id');

        if ($id === null || ! ctype_digit((string) $id)) {
            return $this->failWith('Give the token id with --id.');
        }

        $tokens->revoke((int) $id);
        $this->info("Token {$id} revoked.");

        return self::SUCCESS;
    }

    private function listTokens(McpTokenService $tokens): int
    {
        $this->table(
            ['id', 'account', 'read-only', 'scopes', 'last used', 'expires'],
            $tokens->list()->map(static fn (array $token): array => [
                $token['id'],
                $token['account'],
                $token['is_read_only'] ? 'yes' : 'no',
                implode(', ', $token['scopes']),
                $token['last_used_at'] ?? '-',
                $token['expires_at'] ?? '-',
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function failWith(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
