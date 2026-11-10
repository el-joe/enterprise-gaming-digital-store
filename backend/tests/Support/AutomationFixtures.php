<?php

/**
 * Fixtures for the automation API tests (M17).
 */

use App\Domains\Automation\DTOs\SignedRequest;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Automation\Services\AutomationSignature;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use Database\Seeders\AutomationWorkflowSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

const AUTOMATION_TEST_SECRET = 'automation-test-hmac-secret-0123456789abcdef';

/**
 * An n8n service account with an HMAC secret and a token holding the scopes (all by default).
 *
 * @param  list<AutomationScope|string>|null  $scopes  strings are raw abilities
 * @param  array<string, mixed>  $account
 * @return array{account: ServiceAccount, token: string, secret: string}
 */
function automationCredential(?array $scopes = null, array $account = [], ?DateTimeInterface $expiresAt = null): array
{
    $serviceAccount = ServiceAccount::factory()->create([
        'kind' => ServiceAccountKind::N8n,
        'is_read_only' => false,
        'hmac_secret' => AUTOMATION_TEST_SECRET,
        ...$account,
    ]);

    $abilities = array_map(
        static fn (AutomationScope|string $scope): string => $scope instanceof AutomationScope ? $scope->ability() : $scope,
        $scopes ?? AutomationScope::cases(),
    );

    return ['account' => $serviceAccount, 'token' => $serviceAccount->createToken('n8n-test', $abilities, $expiresAt)->plainTextToken, 'secret' => (string) ($account['hmac_secret'] ?? AUTOMATION_TEST_SECRET)];
}

/**
 * Seed the workflow inventory and enable every workflow (or the given ones).
 *
 * @param  list<string>|null  $only
 */
function enableAutomationWorkflows(?array $only = null): void
{
    test()->seed(AutomationWorkflowSeeder::class);

    AutomationWorkflow::query()->when($only !== null, static fn ($query) => $query->whereIn('workflow_id', $only))->update(['is_enabled' => true]);
}

/**
 * Send a signed automation request the way the n8n workflows do.
 *
 * @param  array<string, mixed>|null  $body  JSON body (null sends none)
 * @param  array<string, mixed>  $options  workflow, key, timestamp, nonce, secret, signature, headers, raw_body, skip
 */
function automationCall(string $token, string $method, string $path, ?array $body = null, array $options = []): TestResponse
{
    $uri = '/api/v1/automation/'.ltrim($path, '/');
    $raw = $options['raw_body'] ?? ($body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR));
    $workflow = $options['workflow'] ?? 'A01';
    $key = $options['key'] ?? $workflow.':'.now()->format('Y-m-d');
    $timestamp = (string) ($options['timestamp'] ?? now()->getTimestamp());
    $nonce = $options['nonce'] ?? Str::random(32);

    $signature = $options['signature'] ?? app(AutomationSignature::class)->sign(
        new SignedRequest($timestamp, $nonce, $method, $uri, $workflow, $key, $options['signed_body'] ?? $raw),
        $options['secret'] ?? AUTOMATION_TEST_SECRET,
    );

    $headers = [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
        'X-Workflow-Id' => $workflow,
        'X-Execution-Key' => $key,
        'X-Automation-Timestamp' => $timestamp,
        'X-Automation-Nonce' => $nonce,
        'X-Automation-Signature' => $signature,
        ...($options['headers'] ?? []),
    ];

    foreach ($options['skip'] ?? [] as $header) {
        unset($headers[$header]);
    }

    $server = ['CONTENT_TYPE' => 'application/json'];

    foreach ($headers as $name => $value) {
        $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
    }

    return test()->call($method, $uri, [], [], [], $server, $raw);
}

/**
 * @param  array<string, mixed>  $options
 */
function automationGet(string $token, string $path, array $options = []): TestResponse
{
    return automationCall($token, 'GET', $path, null, $options);
}

/**
 * @param  array<string, mixed>  $body
 * @param  array<string, mixed>  $options
 */
function automationPost(string $token, string $path, array $body = [], array $options = []): TestResponse
{
    return automationCall($token, 'POST', $path, $body, $options);
}

/**
 * Start an execution for the workflow and key and assert it started.
 */
function startAutomationExecution(string $token, string $workflow, string $key): void
{
    automationPost($token, 'executions', ['action' => 'start'], ['workflow' => $workflow, 'key' => $key])->assertCreated();
}
