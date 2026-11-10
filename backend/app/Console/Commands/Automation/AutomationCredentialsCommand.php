<?php

namespace App\Console\Commands\Automation;

use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Automation\Services\AutomationCredentialService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('automation:credentials
    {action : issue, revoke or list}
    {account? : Service account name (issue)}
    {--scope=* : Scope to grant, repeatable; default executions:write, reports:read, monitor:read, marketing:read}
    {--rate-limit= : Requests per minute for the account}
    {--ip=* : Allowed IP or CIDR, repeatable}
    {--days= : Token lifetime in days}
    {--id= : Token id (revoke)}')]
#[Description('Issue, list or revoke n8n automation credentials (token and HMAC secret)')]
class AutomationCredentialsCommand extends Command
{
    public function handle(AutomationCredentialService $credentials): int
    {
        try {
            return match ($this->argument('action')) {
                'issue' => $this->issue($credentials),
                'revoke' => $this->revoke($credentials),
                'list' => $this->listCredentials($credentials),
                default => $this->failWith('Action must be issue, revoke or list.'),
            };
        } catch (ApplicationException $exception) {
            return $this->failWith(implode(' ', [$exception->getMessage(), ...array_merge(...array_values($exception->errors))]));
        }
    }

    private function issue(AutomationCredentialService $credentials): int
    {
        $account = (string) $this->argument('account');

        if ($account === '' || preg_match('/^[A-Za-z0-9 _.-]{3,120}$/', $account) !== 1) {
            return $this->failWith('Give a service account name of 3 to 120 letters, digits, spaces, dots, dashes or underscores.');
        }

        $scopes = [];

        foreach ((array) $this->option('scope') as $value) {
            $scope = AutomationScope::tryFrom((string) $value);

            if ($scope === null) {
                return $this->failWith("Unknown scope {$value}. Known: ".implode(', ', array_map(static fn (AutomationScope $case): string => $case->value, AutomationScope::cases())));
            }

            $scopes[] = $scope;
        }

        $issued = $credentials->issue(
            accountName: $account,
            scopes: $scopes,
            rateLimitPerMinute: $this->option('rate-limit') === null ? null : max(1, (int) $this->option('rate-limit')),
            allowedIps: $this->option('ip') === [] ? null : array_values((array) $this->option('ip')),
            ttlDays: $this->option('days') === null ? null : max(1, (int) $this->option('days')),
        );

        $this->info("Token {$issued['token']->getKey()} issued to {$issued['account']->name}.");
        $this->line('Scopes: '.implode(', ', array_map(static fn (AutomationScope $scope): string => $scope->value, AutomationScope::fromAbilities($issued['token']->abilities ?? []))));
        $this->line('Expires: '.($issued['token']->expires_at?->toIso8601String() ?? 'never'));
        $this->warn('Store both values now; they are not shown again. Issuing replaced the account\'s previous HMAC secret.');
        $this->line('Token (n8n credential LaravelAutomationApi, header "Authorization: Bearer <token>"):');
        $this->line($issued['plain_text']);
        $this->line('HMAC secret (n8n environment variable EGS_AUTOMATION_HMAC_SECRET):');
        $this->line($issued['hmac_secret']);

        return self::SUCCESS;
    }

    private function revoke(AutomationCredentialService $credentials): int
    {
        $id = $this->option('id');

        if ($id === null || ! ctype_digit((string) $id)) {
            return $this->failWith('Give the token id with --id.');
        }

        $credentials->revoke((int) $id);
        $this->info("Token {$id} revoked.");

        return self::SUCCESS;
    }

    private function listCredentials(AutomationCredentialService $credentials): int
    {
        $this->table(
            ['id', 'account', 'scopes', 'rate/min', 'last used', 'expires'],
            $credentials->list()->map(static fn (array $credential): array => [
                $credential['id'],
                $credential['account'],
                implode(', ', $credential['scopes']),
                $credential['rate_limit_per_minute'],
                $credential['last_used_at'] ?? '-',
                $credential['expires_at'] ?? '-',
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
