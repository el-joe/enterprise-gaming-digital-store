<?php

/**
 * Token issuance and revocation (artisan and admin JSON), per-account rate limits, and the
 * absence of any route for the hard-denied capabilities.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Mcp\Models\McpAuditLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

it('issues a token once through the admin endpoint and lets it be listed and revoked', function (): void {
    actingAsStaffWith(['service_accounts.manage']);

    $issued = $this->postJson('/admin/api/mcp-tokens', ['account' => 'Claude Code', 'scopes' => ['products:read', 'orders:read'], 'rate_limit_per_minute' => 30])
        ->assertCreated()
        ->assertJsonPath('data.is_read_only', true)
        ->assertJsonPath('data.scopes', ['products:read', 'orders:read']);
    $token = $issued->json('data.token');

    $this->getJson('/admin/api/mcp-tokens')
        ->assertOk()
        ->assertJsonPath('data.tokens.0.account', 'Claude Code')
        ->assertJsonMissingPath('data.tokens.0.token');
    expect($this->getJson('/admin/api/mcp-tokens')->getContent())->not->toContain(explode('|', $token)[1]);

    mcpGet($token, 'products')->assertOk();
    mcpGet($token, 'suppliers/balances')->assertForbidden()->assertJson(['code' => 'scope_missing']);
    expect(ServiceAccount::query()->sole()->rate_limit_per_minute)->toBe(30);

    actingAsStaffWith(['service_accounts.manage']);
    $this->deleteJson('/admin/api/mcp-tokens/'.$issued->json('data.token_id'))->assertOk();
    mcpGet($token, 'products')->assertUnauthorized();

    expect(AuditLog::query()->pluck('action')->all())->toContain('service_accounts.mcp_token_issued', 'service_accounts.mcp_token_revoked')
        ->and(AuditLog::query()->get()->toJson())->not->toContain(explode('|', $token)[1]);
});

it('refuses write scopes without allow_writes and needs service_accounts.manage', function (): void {
    actingAsStaffWith(['mcp_actions.approve']);
    $this->postJson('/admin/api/mcp-tokens', ['account' => 'x-agent'])->assertForbidden();

    actingAsStaffWith(['service_accounts.manage']);
    $this->postJson('/admin/api/mcp-tokens', ['account' => 'x-agent', 'scopes' => ['products:write']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['scopes']);
    $this->postJson('/admin/api/mcp-tokens', ['account' => 'x-agent', 'scopes' => ['refunds:write']])->assertUnprocessable();

    $this->postJson('/admin/api/mcp-tokens', ['account' => 'x-agent', 'scopes' => ['products:read', 'products:write'], 'allow_writes' => true])
        ->assertCreated()
        ->assertJsonPath('data.is_read_only', false);
});

it('lists and revokes tokens from the command line without printing token values in the list', function (): void {
    Artisan::call('mcp:token', ['action' => 'issue', 'account' => 'cli-agent']);
    $output = Artisan::output();
    preg_match('/^(\d+\|\S+)$/m', $output, $match);
    expect($match)->not->toBeEmpty();

    Artisan::call('mcp:token', ['action' => 'list']);
    expect(Artisan::output())->toContain('cli-agent')->not->toContain(explode('|', $match[1])[1]);

    $this->artisan('mcp:token', ['action' => 'revoke', '--id' => explode('|', $match[1])[0]])->assertSuccessful();
    mcpGet($match[1], 'products')->assertUnauthorized();
    $this->artisan('mcp:token', ['action' => 'issue', 'account' => 'cli-agent', '--scope' => ['nonsense']])->assertFailed();
});

it('limits each account to its rate_limit_per_minute and audits the refusals', function (): void {
    ['token' => $token] = mcpToken(account: ['rate_limit_per_minute' => 2]);
    ['token' => $other] = mcpToken(account: ['rate_limit_per_minute' => 2]);

    mcpGet($token, 'products')->assertOk();
    mcpGet($token, 'products')->assertOk();
    mcpGet($token, 'products')->assertStatus(429)->assertJson(['success' => false, 'code' => 'rate_limited'])->assertHeader('Retry-After');
    mcpGet($other, 'products')->assertOk();

    expect(McpAuditLog::query()->where('outcome', McpAuditLogOutcome::RateLimited)->count())->toBe(1);

    $this->travel(61)->seconds();
    mcpGet($token, 'products')->assertOk();
});

it('has no MCP route for refunds, codes, credentials, settings, customers or queries', function (): void {
    $uris = collect(Route::getRoutes()->getRoutes())
        ->filter(static fn ($route): bool => str_starts_with($route->uri(), 'api/v1/mcp'))
        ->map(static fn ($route): string => $route->uri().' '.$route->getName());

    expect($uris)->toHaveCount(36);

    foreach ($uris as $uri) {
        expect($uri)->not->toMatch('/refund|code|credential|secret|setting|customer(?!-purchase|_purchase|_orders)|sql|query|reveal|wallet/i');
    }
});
