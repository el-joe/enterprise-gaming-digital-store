<?php

/**
 * Dedicated n8n credentials: issued, listed and revoked through `automation:credentials` and the
 * admin JSON endpoint behind service_accounts.manage. Token and secret are shown once.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\ServiceAccount;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function (): void {
    enableAutomationWorkflows();
});

it('issues a working credential from the console with default scopes only', function (): void {
    $this->artisan('automation:credentials', ['action' => 'issue', 'account' => 'n8n-main'])
        ->expectsOutputToContain('issued to n8n-main')
        ->expectsOutputToContain('Scopes: executions:write, reports:read, monitor:read, marketing:read')
        ->assertSuccessful();

    $account = ServiceAccount::query()->where('name', 'n8n-main')->sole();
    $token = PersonalAccessToken::query()->where('tokenable_id', $account->id)->sole();

    expect($account->kind)->toBe(ServiceAccountKind::N8n)
        ->and($account->is_read_only)->toBeTrue()
        ->and($account->hmac_secret)->toMatch('/^[0-9a-f]{64}$/')
        ->and(AutomationScope::fromAbilities($token->abilities))->not->toContain(AutomationScope::JobsRun, AutomationScope::CustomersMessage)
        ->and($token->expires_at)->not->toBeNull();
});

it('issues through the admin endpoint and the credential signs real requests', function (): void {
    actingAsStaffWith(['service_accounts.manage']);

    $issued = $this->postJson('/admin/api/automation-credentials', ['account' => 'n8n-prod', 'scopes' => ['executions:write', 'monitor:read'], 'rate_limit_per_minute' => 120])
        ->assertCreated()
        ->assertJsonPath('data.account', 'n8n-prod')
        ->assertJsonPath('data.scopes', ['executions:write', 'monitor:read'])
        ->json('data');

    auth()->forgetGuards();
    $this->flushSession();

    automationGet($issued['token'], 'monitor/exceptions', ['secret' => $issued['hmac_secret']])->assertOk();
    automationGet($issued['token'], 'reports/sales', ['secret' => $issued['hmac_secret']])->assertForbidden();

    $audit = AuditLog::query()->where('action', 'service_accounts.automation_credential_issued')->sole();
    expect(json_encode($audit->toArray()))->not->toContain($issued['token'])->not->toContain($issued['hmac_secret']);
});

it('lists credentials without token values or secrets and revokes one', function (): void {
    ['account' => $account, 'secret' => $secret] = automationCredential();
    $tokenId = PersonalAccessToken::query()->where('tokenable_id', $account->id)->value('id');
    actingAsStaffWith(['service_accounts.manage']);

    $list = $this->getJson('/admin/api/automation-credentials')->assertOk()->assertJsonPath('data.credentials.0.id', $tokenId);
    expect($list->getContent())->not->toContain($secret)->and($list->json('data.credentials.0.has_hmac_secret'))->toBeTrue();

    $this->deleteJson("/admin/api/automation-credentials/{$tokenId}")->assertOk();
    expect(PersonalAccessToken::query()->whereKey($tokenId)->exists())->toBeFalse();
});

it('denies the admin endpoint without service_accounts.manage', function (): void {
    actingAsStaffWith(['catalog.view']);

    $this->getJson('/admin/api/automation-credentials')->assertForbidden();
    $this->postJson('/admin/api/automation-credentials', ['account' => 'n8n-x'])->assertForbidden();
});

it('refuses to turn an MCP account into an automation account', function (): void {
    ServiceAccount::factory()->create(['name' => 'shared-name', 'kind' => ServiceAccountKind::Mcp]);

    $this->artisan('automation:credentials', ['action' => 'issue', 'account' => 'shared-name'])->assertFailed();
});

it('enables and disables workflows from the console', function (): void {
    ['token' => $token] = automationCredential();

    $this->artisan('automation:workflows', ['action' => 'disable', 'ids' => ['f03']])->assertSuccessful();
    automationGet($token, 'monitor/exceptions', ['workflow' => 'F03'])->assertForbidden()->assertJson(['code' => 'workflow_disabled']);

    $this->artisan('automation:workflows', ['action' => 'enable', 'ids' => ['F03']])->assertSuccessful();
    automationGet($token, 'monitor/exceptions', ['workflow' => 'F03'])->assertOk();

    $this->artisan('automation:workflows', ['action' => 'enable', 'ids' => ['X99']])->assertFailed();
});
