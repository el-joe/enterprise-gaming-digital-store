<?php

namespace App\Http\Middleware\Automation;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\DTOs\SignedRequest;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Automation\Services\AutomationSignature;
use App\Domains\Automation\Services\AutomationWorkflowRegistry;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Shared\Exceptions\ApplicationException;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication for /api/v1/automation (N8N_AUTOMATIONS.md section 2). A request needs:
 *
 * 1. a live Sanctum bearer token of an enabled `n8n` service account (sessions are ignored), from
 *    an allowed IP;
 * 2. `X-Automation-Signature`: hex HMAC-SHA256, with the account's own secret, over the
 *    canonical form in SignedRequest (timestamp, nonce, method, path and query as sent, workflow
 *    id, execution key, raw body);
 * 3. `X-Automation-Timestamp` within the signature window of the server clock;
 * 4. `X-Automation-Nonce` not seen before for this account (replay protection);
 * 5. `X-Workflow-Id` of a known, enabled workflow and an `X-Execution-Key`.
 *
 * The nonce is stored only after the signature has been verified. Token values and secrets are
 * never logged.
 */
class AuthenticateAutomationRequest
{
    public const string WORKFLOW_HEADER = 'X-Workflow-Id';

    public const string EXECUTION_HEADER = 'X-Execution-Key';

    public const string TIMESTAMP_HEADER = 'X-Automation-Timestamp';

    public const string NONCE_HEADER = 'X-Automation-Nonce';

    public const string SIGNATURE_HEADER = 'X-Automation-Signature';

    public const string WORKFLOW_PATTERN = '/^[A-Z][0-9]{2}$/';

    public const string EXECUTION_KEY_PATTERN = '/^[A-Za-z0-9:._-]{1,128}$/';

    public function __construct(private AutomationSignature $signature, private AutomationWorkflowRegistry $workflows) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Context::forgetHidden(AuditLogger::SERVICE_ACCOUNT_CONTEXT);

        [$account, $token] = $this->authenticate($request);

        if (is_array($account->allowed_ips) && $account->allowed_ips !== [] && ! IpUtils::checkIp((string) $request->ip(), $account->allowed_ips)) {
            throw new ApplicationException('This address is not allowed for this service account.', 'forbidden', 403);
        }

        $signed = $this->signedRequest($request);
        $this->verifySignature($account, $signed, (string) $request->header(self::SIGNATURE_HEADER));
        $this->assertFresh($signed->timestamp);
        $this->claimNonce($account, $signed->nonce);
        $this->workflows->assertEnabled($signed->workflowId);

        $request->attributes->set(AutomationCaller::REQUEST_ATTRIBUTE, new AutomationCaller(
            $account,
            $token,
            AutomationScope::fromAbilities($token->abilities ?? []),
            $signed->workflowId,
            $signed->executionKey,
        ));
        Context::addHidden(AuditLogger::SERVICE_ACCOUNT_CONTEXT, ['id' => $account->id, 'kind' => $account->kind->value]);

        $this->touch($account, $token);

        return $next($request);
    }

    /**
     * @return array{0: ServiceAccount, 1: PersonalAccessToken}
     */
    private function authenticate(Request $request): array
    {
        $bearer = $request->bearerToken();
        $token = is_string($bearer) && $bearer !== '' ? PersonalAccessToken::findToken($bearer) : null;

        if ($token === null || $token->tokenable_type !== (new ServiceAccount)->getMorphClass() || ($token->expires_at !== null && $token->expires_at->isPast())) {
            throw new AuthenticationException;
        }

        $account = ServiceAccount::query()->find($token->tokenable_id);

        if ($account === null || $account->kind !== ServiceAccountKind::N8n || ! $account->isUsable()) {
            throw new AuthenticationException;
        }

        return [$account, $token];
    }

    private function signedRequest(Request $request): SignedRequest
    {
        $timestamp = (string) $request->header(self::TIMESTAMP_HEADER);
        $nonce = (string) $request->header(self::NONCE_HEADER);
        $workflowId = (string) $request->header(self::WORKFLOW_HEADER);
        $executionKey = (string) $request->header(self::EXECUTION_HEADER);
        $signature = (string) $request->header(self::SIGNATURE_HEADER);

        if (
            preg_match('/^[0-9]{1,12}$/', $timestamp) !== 1
            || preg_match('/^[A-Za-z0-9_-]{16,64}$/', $nonce) !== 1
            || preg_match(self::WORKFLOW_PATTERN, $workflowId) !== 1
            || preg_match(self::EXECUTION_KEY_PATTERN, $executionKey) !== 1
            || preg_match('/^[0-9a-fA-F]{64}$/', $signature) !== 1
        ) {
            throw new ApplicationException(
                'Signed requests need valid X-Automation-Timestamp, X-Automation-Nonce, X-Automation-Signature, X-Workflow-Id and X-Execution-Key headers.',
                'signature_missing',
                401,
            );
        }

        return new SignedRequest($timestamp, $nonce, $request->getMethod(), $request->getRequestUri(), $workflowId, $executionKey, (string) $request->getContent());
    }

    private function verifySignature(ServiceAccount $account, SignedRequest $signed, string $signature): void
    {
        if (! $this->signature->verify($signed, (string) $account->hmac_secret, $signature)) {
            throw new ApplicationException('The request signature is not valid.', 'signature_invalid', 401);
        }
    }

    private function assertFresh(string $timestamp): void
    {
        if (abs(now()->getTimestamp() - (int) $timestamp) > (int) config('automation.signature_window_seconds')) {
            throw new ApplicationException('The request timestamp is outside the allowed window.', 'timestamp_stale', 401);
        }
    }

    private function claimNonce(ServiceAccount $account, string $nonce): void
    {
        if (! Cache::add("automation:nonce:{$account->id}:{$nonce}", 1, (int) config('automation.nonce_ttl_seconds'))) {
            throw new ApplicationException('This request was already received.', 'replay_detected', 401);
        }
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
