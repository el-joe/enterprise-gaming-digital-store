<?php

/**
 * N8N_AUTOMATIONS.md section 2: token, HMAC signature, timestamp window, replay nonce, workflow
 * registry, scopes, rate limit and the automation_logs call row for every request.
 */

use App\Domains\Automation\DTOs\SignedRequest;
use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationLogStatus;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Automation\Services\AutomationSignature;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function (): void {
    enableAutomationWorkflows();
});

function automationCalls(): Collection
{
    return AutomationLog::query()->where('kind', AutomationLogKind::Call)->orderBy('id')->get();
}

it('accepts a correctly signed request and records the call', function (): void {
    ['token' => $token, 'account' => $account] = automationCredential();

    automationGet($token, 'monitor/exceptions', ['workflow' => 'F03', 'key' => 'F03:2026-11-10T10:05'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertHeader('Cache-Control', 'no-store, private');

    $call = automationCalls()->sole();
    expect($call->workflow_id)->toBe('F03')
        ->and($call->execution_key)->toBe('F03:2026-11-10T10:05')
        ->and($call->endpoint)->toBe('monitor.exceptions')
        ->and($call->status)->toBe(AutomationLogStatus::Success)
        ->and($call->http_status)->toBe(200)
        ->and($call->service_account_id)->toBe($account->id)
        ->and($call->request_id)->not->toBeNull()
        ->and($account->refresh()->last_used_at)->not->toBeNull();
});

it('rejects a request without a token with the 401 envelope and records it', function (): void {
    $this->getJson('/api/v1/automation/monitor/exceptions')
        ->assertUnauthorized()
        ->assertJson(['success' => false, 'code' => 'unauthenticated'])
        ->assertJsonStructure(['request_id']);

    $call = automationCalls()->sole();
    expect($call->service_account_id)->toBeNull()
        ->and($call->workflow_id)->toBe('-')
        ->and($call->status)->toBe(AutomationLogStatus::Failed)
        ->and($call->error_code)->toBe('unauthenticated')
        ->and($call->http_status)->toBe(401);
});

it('rejects tokens that are not live n8n service-account tokens', function (Closure $makeToken): void {
    automationGet($makeToken(), 'monitor/exceptions')->assertUnauthorized()->assertJson(['code' => 'unauthenticated']);
})->with([
    'garbage' => [fn (): string => '1|not-a-real-token'],
    'expired' => [fn (): string => automationCredential(expiresAt: now()->subMinute())['token']],
    'disabled account' => [fn (): string => automationCredential(account: ['disabled_at' => now()])['token']],
    'mcp account' => [fn (): string => automationCredential(account: ['kind' => ServiceAccountKind::Mcp])['token']],
    'token of a user' => [function (): string {
        $user = User::factory()->staff()->create();
        $plain = Str::random(40);
        (new PersonalAccessToken)->forceFill(['tokenable_type' => $user->getMorphClass(), 'tokenable_id' => $user->id, 'name' => 'x', 'token' => hash('sha256', $plain), 'abilities' => ['*']])->save();

        return PersonalAccessToken::query()->latest('id')->value('id').'|'.$plain;
    }],
]);

it('rejects a request whose signature headers are missing or malformed', function (array $options): void {
    ['token' => $token] = automationCredential();

    automationGet($token, 'monitor/exceptions', $options)->assertUnauthorized()->assertJson(['code' => 'signature_missing']);
})->with([
    'no signature' => [['skip' => ['X-Automation-Signature']]],
    'no timestamp' => [['skip' => ['X-Automation-Timestamp']]],
    'no nonce' => [['skip' => ['X-Automation-Nonce']]],
    'short nonce' => [['nonce' => 'abc']],
    'no workflow' => [['skip' => ['X-Workflow-Id']]],
    'bad workflow id' => [['workflow' => 'a1']],
    'bad execution key' => [['key' => 'has spaces']],
]);

it('rejects a wrong or tampered signature', function (array $options): void {
    ['token' => $token] = automationCredential();

    automationPost($token, 'executions', ['action' => 'start'], $options)->assertUnauthorized()->assertJson(['code' => 'signature_invalid']);

    expect(AutomationLog::query()->where('kind', AutomationLogKind::Execution)->count())->toBe(0);
})->with([
    'another secret' => [['secret' => 'some-other-secret']],
    'tampered body' => [['signed_body' => '{"action":"finish"}']],
    'signature of another workflow' => [['signature' => hash_hmac('sha256', 'unrelated', AUTOMATION_TEST_SECRET)]],
]);

it('binds the signature to method, path, query, workflow and execution key', function (): void {
    ['token' => $token] = automationCredential();
    $timestamp = (string) now()->getTimestamp();
    $nonce = Str::random(32);
    $signedFor = app(AutomationSignature::class)->sign(
        new SignedRequest($timestamp, $nonce, 'GET', '/api/v1/automation/reports/sales?period=yesterday', 'A01', 'A01:k', ''),
        AUTOMATION_TEST_SECRET,
    );

    automationGet($token, 'reports/sales?period=today', ['timestamp' => $timestamp, 'nonce' => $nonce, 'signature' => $signedFor, 'workflow' => 'A01', 'key' => 'A01:k'])
        ->assertUnauthorized()->assertJson(['code' => 'signature_invalid']);
    automationGet($token, 'reports/sales?period=yesterday', ['timestamp' => $timestamp, 'nonce' => Str::random(32), 'signature' => $signedFor, 'workflow' => 'A01', 'key' => 'A01:k'])
        ->assertUnauthorized()->assertJson(['code' => 'signature_invalid']);
    automationGet($token, 'reports/sales?period=yesterday', ['timestamp' => $timestamp, 'nonce' => $nonce, 'signature' => $signedFor, 'workflow' => 'A02', 'key' => 'A01:k'])
        ->assertUnauthorized()->assertJson(['code' => 'signature_invalid']);
    automationGet($token, 'reports/sales?period=yesterday', ['timestamp' => $timestamp, 'nonce' => $nonce, 'signature' => $signedFor, 'workflow' => 'A01', 'key' => 'A01:k'])
        ->assertOk();
});

it('rejects a stale or future timestamp even when correctly signed', function (int $offsetSeconds): void {
    ['token' => $token] = automationCredential();

    automationGet($token, 'monitor/exceptions', ['timestamp' => now()->getTimestamp() + $offsetSeconds])
        ->assertUnauthorized()
        ->assertJson(['code' => 'timestamp_stale']);
})->with(['six minutes old' => -360, 'one hour old' => -3600, 'six minutes ahead' => 360]);

it('accepts a timestamp at the edge of the five-minute window', function (): void {
    ['token' => $token] = automationCredential();

    automationGet($token, 'monitor/exceptions', ['timestamp' => now()->getTimestamp() - 299])->assertOk();
});

it('rejects a replayed request with the same nonce', function (): void {
    ['token' => $token] = automationCredential();
    $options = ['nonce' => Str::random(32), 'workflow' => 'A01', 'key' => 'A01:2026-11-10'];

    automationPost($token, 'executions', ['action' => 'start'], $options)->assertCreated();
    automationPost($token, 'executions', ['action' => 'start'], $options)
        ->assertUnauthorized()
        ->assertJson(['code' => 'replay_detected']);

    $calls = automationCalls();
    expect($calls)->toHaveCount(2)
        ->and($calls->last()->error_code)->toBe('replay_detected')
        ->and(AutomationLog::query()->where('kind', AutomationLogKind::Execution)->count())->toBe(1);
});

it('does not burn a nonce on a request with a bad signature', function (): void {
    ['token' => $token] = automationCredential();
    $nonce = Str::random(32);

    automationGet($token, 'monitor/exceptions', ['nonce' => $nonce, 'secret' => 'wrong'])->assertUnauthorized()->assertJson(['code' => 'signature_invalid']);
    automationGet($token, 'monitor/exceptions', ['nonce' => $nonce])->assertOk();
});

it('refuses unknown and disabled workflows', function (): void {
    ['token' => $token] = automationCredential();
    AutomationWorkflow::query()->where('workflow_id', 'A02')->update(['is_enabled' => false]);

    automationGet($token, 'monitor/exceptions', ['workflow' => 'Q99'])->assertForbidden()->assertJson(['code' => 'workflow_unknown']);
    automationGet($token, 'monitor/exceptions', ['workflow' => 'A02'])->assertForbidden()->assertJson(['code' => 'workflow_disabled']);
});

it('requires the exact scope for each route group', function (string $method, string $path, AutomationScope $scope): void {
    $all = AutomationScope::cases();
    ['token' => $token] = automationCredential(array_values(array_filter($all, static fn (AutomationScope $held): bool => $held !== $scope)));

    automationCall($token, $method, $path, $method === 'POST' ? [] : null)
        ->assertForbidden()
        ->assertJson(['code' => 'scope_missing']);
})->with([
    'executions' => ['POST', 'executions', AutomationScope::ExecutionsWrite],
    'alerts' => ['POST', 'alerts', AutomationScope::ExecutionsWrite],
    'reports' => ['GET', 'reports/sales', AutomationScope::ReportsRead],
    'reconciliation' => ['GET', 'payments/reconciliation', AutomationScope::ReportsRead],
    'monitor' => ['GET', 'monitor/queues', AutomationScope::MonitorRead],
    'jobs' => ['POST', 'jobs/catalog-sync', AutomationScope::JobsRun],
    'marketing data' => ['GET', 'data/trending', AutomationScope::MarketingRead],
    'suggestions' => ['POST', 'suggestions', AutomationScope::SuggestionsWrite],
    'customer segments' => ['GET', 'customers/segments/abandoned', AutomationScope::CustomersMessage],
    'customer messages' => ['POST', 'customers/messages/log', AutomationScope::CustomersMessage],
]);

it('grants nothing for a wildcard or foreign ability', function (): void {
    ['token' => $token] = automationCredential(['*', 'mcp:operations:read', 'automation:*']);

    automationGet($token, 'monitor/queues')->assertForbidden()->assertJson(['code' => 'scope_missing']);
});

it('enforces the account IP allow-list', function (): void {
    ['token' => $token] = automationCredential(account: ['allowed_ips' => ['10.0.0.0/8']]);

    automationGet($token, 'monitor/exceptions')->assertForbidden()->assertJson(['code' => 'forbidden']);
});

it('rate limits per account, records the refusal and leaves other accounts alone', function (): void {
    ['token' => $limited] = automationCredential(account: ['rate_limit_per_minute' => 2]);
    ['token' => $other] = automationCredential();

    automationGet($limited, 'monitor/exceptions')->assertOk();
    automationGet($limited, 'monitor/exceptions')->assertOk();
    automationGet($limited, 'monitor/exceptions')->assertTooManyRequests()->assertJson(['code' => 'rate_limited']);
    automationGet($other, 'monitor/exceptions')->assertOk();

    expect(automationCalls()->where('http_status', 429))->toHaveCount(1);

    $this->travel(61)->seconds();
    automationGet($limited, 'monitor/exceptions')->assertOk();
});

it('ignores a staff session on automation routes', function (): void {
    actingAsStaffWith(['service_accounts.manage']);

    $this->getJson('/api/v1/automation/monitor/exceptions')->assertUnauthorized();
});

it('stores only the redacted query, never the body, in the call row', function (): void {
    ['token' => $token] = automationCredential();
    enableAutomationWorkflows();

    automationPost($token, 'alerts', ['severity' => 'warning', 'title' => 'Body title', 'message' => 'secret body text'], ['workflow' => 'Z01']);
    automationGet($token, 'monitor/orders/pending?older_than=30&token=abc', ['workflow' => 'A04']);

    $rows = automationCalls();
    expect(json_encode($rows->pluck('summary')))->not->toContain('secret body text')->not->toContain('Body title')
        ->and($rows->last()->summary['query']['older_than'])->toBe('30')
        ->and($rows->last()->summary['query']['token'])->not->toBe('abc');
});
