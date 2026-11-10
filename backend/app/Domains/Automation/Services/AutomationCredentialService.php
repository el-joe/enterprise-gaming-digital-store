<?php

namespace App\Domains\Automation\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Issues, lists and revokes the dedicated n8n credentials (artisan `automation:credentials` and
 * the admin JSON endpoint). A credential is a Sanctum token with `automation:` scopes plus the
 * account's HMAC secret. Privileged scopes (jobs, suggestions, customer messaging) are granted
 * only when named. The plain token and secret are returned once and never stored in plain text,
 * logged or audited; the secret is stored encrypted on the account.
 */
class AutomationCredentialService
{
    public function __construct(private AuditLogger $audit, private AutomationSignature $signature) {}

    /**
     * @param  list<AutomationScope>  $scopes  empty means the default scopes
     * @param  list<string>|null  $allowedIps
     * @return array{account: ServiceAccount, token: PersonalAccessToken, plain_text: string, hmac_secret: string}
     */
    public function issue(string $accountName, array $scopes, ?int $rateLimitPerMinute = null, ?array $allowedIps = null, ?int $ttlDays = null): array
    {
        $scopes = array_values(array_unique($scopes === [] ? AutomationScope::defaultScopes() : $scopes, SORT_REGULAR));
        $ttlDays ??= (int) config('automation.token_ttl_days');
        $secret = $this->signature->newSecret();

        /** @var array{account: ServiceAccount, token: NewAccessToken} $issued */
        $issued = DB::transaction(function () use ($accountName, $scopes, $rateLimitPerMinute, $allowedIps, $ttlDays, $secret): array {
            $account = ServiceAccount::query()->withTrashed()->where('name', $accountName)->lockForUpdate()->first();

            if ($account !== null && ($account->kind !== ServiceAccountKind::N8n || ! $account->isUsable())) {
                throw new ApplicationException('This service account cannot receive automation credentials.', 'conflict', 409);
            }

            $account ??= new ServiceAccount(['name' => $accountName, 'kind' => ServiceAccountKind::N8n]);
            $account->is_read_only = array_filter($scopes, static fn (AutomationScope $scope): bool => $scope->isPrivileged()) === [];
            $account->hmac_secret = $secret;

            if ($rateLimitPerMinute !== null) {
                $account->rate_limit_per_minute = $rateLimitPerMinute;
            }

            if ($allowedIps !== null) {
                $account->allowed_ips = $allowedIps === [] ? null : $allowedIps;
            }

            $account->save();

            $token = $account->createToken(
                'n8n',
                array_map(static fn (AutomationScope $scope): string => $scope->ability(), $scopes),
                now()->addDays($ttlDays),
            );

            $this->audit->record(new AuditEntry(
                action: 'service_accounts.automation_credential_issued',
                subject: $account,
                after: [
                    'token_id' => $token->accessToken->getKey(),
                    'scopes' => array_map(static fn (AutomationScope $scope): string => $scope->value, $scopes),
                    'rate_limit_per_minute' => $account->rate_limit_per_minute,
                    'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
                    'hmac_secret_rotated' => true,
                ],
            ));

            return ['account' => $account, 'token' => $token];
        });

        return ['account' => $issued['account']->refresh(), 'token' => $issued['token']->accessToken, 'plain_text' => $issued['token']->plainTextToken, 'hmac_secret' => $secret];
    }

    /**
     * Revoke one token. The account and its other tokens stay valid.
     */
    public function revoke(int $tokenId): void
    {
        DB::transaction(function () use ($tokenId): void {
            $token = $this->automationTokens()->whereKey($tokenId)->lockForUpdate()->first();

            if ($token === null) {
                throw new ApplicationException('Token not found.', 'not_found', 404);
            }

            $account = ServiceAccount::query()->withTrashed()->findOrFail($token->tokenable_id);
            $token->delete();

            $this->audit->record(new AuditEntry(
                action: 'service_accounts.automation_credential_revoked',
                subject: $account,
                before: ['token_id' => $tokenId, 'name' => $token->name],
            ));
        });
    }

    /**
     * Every automation token with its account; never the token or the secret.
     *
     * @return Collection<int, array{id: int, account: string, account_id: int, scopes: list<string>, rate_limit_per_minute: int, has_hmac_secret: bool, last_used_at: ?string, expires_at: ?string, created_at: ?string}>
     */
    public function list(): Collection
    {
        $tokens = $this->automationTokens()->orderByDesc('id')->get();
        $accounts = ServiceAccount::query()->withTrashed()->whereIn('id', $tokens->pluck('tokenable_id')->unique())->get()->keyBy('id');

        return $tokens->map(static function (PersonalAccessToken $token) use ($accounts): array {
            $account = $accounts->get($token->tokenable_id);

            return [
                'id' => (int) $token->getKey(),
                'account' => (string) $account?->name,
                'account_id' => (int) $token->tokenable_id,
                'scopes' => array_map(static fn (AutomationScope $scope): string => $scope->value, AutomationScope::fromAbilities($token->abilities ?? [])),
                'rate_limit_per_minute' => (int) ($account->rate_limit_per_minute ?? 0),
                'has_hmac_secret' => $account?->hmac_secret !== null,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ];
        })->values();
    }

    /**
     * @return Builder<PersonalAccessToken>
     */
    private function automationTokens(): Builder
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', (new ServiceAccount)->getMorphClass())
            ->whereIn('tokenable_id', ServiceAccount::query()->withTrashed()->select('id')->where('kind', ServiceAccountKind::N8n));
    }
}
