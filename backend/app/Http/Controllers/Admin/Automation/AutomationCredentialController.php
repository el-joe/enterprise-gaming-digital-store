<?php

namespace App\Http\Controllers\Admin\Automation;

use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Automation\Services\AutomationCredentialService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Automation\IssueAutomationCredentialRequest;
use Illuminate\Http\JsonResponse;

/**
 * Issue, list and revoke n8n automation credentials (`service_accounts.manage`). The plain token
 * and HMAC secret appear once, in the 201 response; the admin screen arrives in M15.
 */
class AutomationCredentialController extends Controller
{
    public function __construct(private AutomationCredentialService $credentials) {}

    public function index(): JsonResponse
    {
        return $this->success(['credentials' => $this->credentials->list()->all()]);
    }

    public function store(IssueAutomationCredentialRequest $request): JsonResponse
    {
        $issued = $this->credentials->issue(
            accountName: (string) $request->validated('account'),
            scopes: $request->scopes(),
            rateLimitPerMinute: $request->validated('rate_limit_per_minute'),
            allowedIps: $request->validated('allowed_ips'),
            ttlDays: $request->validated('expires_in_days'),
        );

        return $this->success([
            'token' => $issued['plain_text'],
            'hmac_secret' => $issued['hmac_secret'],
            'token_id' => $issued['token']->getKey(),
            'account' => $issued['account']->name,
            'scopes' => array_map(static fn (AutomationScope $scope): string => $scope->value, AutomationScope::fromAbilities($issued['token']->abilities ?? [])),
            'expires_at' => $issued['token']->expires_at?->toIso8601String(),
        ], 'Credential issued. The token and secret are shown only once; issuing replaces the account\'s previous secret.', status: 201);
    }

    public function destroy(int $tokenId): JsonResponse
    {
        $this->credentials->revoke($tokenId);

        return $this->success(message: 'Token revoked.');
    }
}
