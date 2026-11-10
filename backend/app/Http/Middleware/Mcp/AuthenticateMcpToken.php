<?php

namespace App\Http\Middleware\Mcp;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-token authentication for /api/v1/mcp. Only a live Sanctum token of an enabled `mcp`
 * service account is accepted; sessions and cookies are ignored. The account's IP allow-list is
 * enforced. The token value is never logged.
 */
class AuthenticateMcpToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Context::forgetHidden(AuditLogger::SERVICE_ACCOUNT_CONTEXT);

        $bearer = $request->bearerToken();
        $token = is_string($bearer) && $bearer !== '' ? PersonalAccessToken::findToken($bearer) : null;

        if ($token === null || $token->tokenable_type !== (new ServiceAccount)->getMorphClass() || ($token->expires_at !== null && $token->expires_at->isPast())) {
            throw new AuthenticationException;
        }

        $account = ServiceAccount::query()->find($token->tokenable_id);

        if ($account === null || $account->kind !== ServiceAccountKind::Mcp || ! $account->isUsable()) {
            throw new AuthenticationException;
        }

        if (is_array($account->allowed_ips) && $account->allowed_ips !== [] && ! IpUtils::checkIp((string) $request->ip(), $account->allowed_ips)) {
            throw new ApplicationException('This address is not allowed for this service account.', 'forbidden', 403);
        }

        $request->attributes->set(McpCaller::REQUEST_ATTRIBUTE, new McpCaller($account, $token, McpScope::fromAbilities($token->abilities ?? [])));
        Context::addHidden(AuditLogger::SERVICE_ACCOUNT_CONTEXT, ['id' => $account->id, 'kind' => $account->kind->value]);

        $this->touch($account, $token);

        return $next($request);
    }

    private function touch(ServiceAccount $account, PersonalAccessToken $token): void
    {
        $stale = now()->subMinute();

        if ($token->last_used_at === null || $token->last_used_at->lt($stale)) {
            $token->forceFill(['last_used_at' => now()])->save();
        }

        if ($account->last_used_at === null || $account->last_used_at->lt($stale)) {
            $account->forceFill(['last_used_at' => now()])->save();
        }
    }
}
