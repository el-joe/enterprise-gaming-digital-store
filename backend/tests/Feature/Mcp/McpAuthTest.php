<?php

/**
 * MCP_SERVER.md section 8: authentication, scopes and the audit row for every call.
 */

use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Models\McpAuditLog;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

it('rejects a call without a token with the 401 envelope and audits it', function (): void {
    $this->getJson('/api/v1/mcp/products')
        ->assertUnauthorized()
        ->assertJson(['success' => false, 'code' => 'unauthenticated'])
        ->assertJsonStructure(['request_id']);

    $row = McpAuditLog::query()->sole();
    expect($row->tool)->toBe('list_products')
        ->and($row->outcome)->toBe(McpAuditLogOutcome::Denied)
        ->and($row->http_status)->toBe(401)
        ->and($row->service_account_id)->toBeNull()
        ->and($row->error_code)->toBe('unauthenticated');
});

it('rejects tokens that are not live MCP service-account tokens', function (Closure $makeToken): void {
    mcpGet($makeToken(), 'products')->assertUnauthorized()->assertJson(['code' => 'unauthenticated']);
})->with([
    'garbage' => [fn (): string => '1|not-a-real-token'],
    'expired' => [fn (): string => mcpToken(expiresAt: now()->subMinute())['token']],
    'disabled account' => [fn (): string => mcpToken(account: ['disabled_at' => now()])['token']],
    'n8n account' => [fn (): string => mcpToken(account: ['kind' => ServiceAccountKind::N8n])['token']],
    'token of a user, not a service account' => [function (): string {
        $user = User::factory()->staff()->create();
        $plain = Str::random(40);
        $token = (new PersonalAccessToken)->forceFill(['tokenable_type' => $user->getMorphClass(), 'tokenable_id' => $user->id, 'name' => 'x', 'token' => hash('sha256', $plain), 'abilities' => ['*', McpScope::ProductsRead->ability()]]);
        $token->save();

        return $token->id.'|'.$plain;
    }],
]);

it('rejects a deleted account and a revoked token', function (): void {
    ['account' => $account, 'token' => $token, 'model' => $model] = mcpToken();
    mcpGet($token, 'products')->assertOk();

    $model->delete();
    mcpGet($token, 'products')->assertUnauthorized();

    ['account' => $other, 'token' => $second] = mcpToken();
    $other->delete();
    mcpGet($second, 'products')->assertUnauthorized();
});

it('ignores a staff session on MCP routes', function (): void {
    actingAsStaffWith(['catalog.view']);

    $this->getJson('/api/v1/mcp/products')->assertUnauthorized();
});

it('requires the exact scope of each read tool', function (string $path, array $query, McpScope $scope): void {
    $others = array_values(array_filter(McpScope::cases(), static fn (McpScope $case): bool => $case !== $scope));
    ['token' => $token] = mcpToken($others, writable: true);

    mcpGet($token, $path, $query)->assertForbidden()->assertJson(['success' => false, 'code' => 'scope_missing']);

    ['token' => $allowed] = mcpToken([$scope]);
    expect(mcpGet($allowed, $path, $query)->status())->toBeIn([200, 404]);
})->with(fn (): array => mcpReadTools());

it('grants nothing to a wildcard or unprefixed ability', function (): void {
    ['token' => $token] = mcpToken(['*', 'products:read', 'mcp:*']);

    mcpGet($token, 'products')->assertForbidden()->assertJson(['code' => 'scope_missing']);
});

it('enforces the account IP allow-list', function (): void {
    ['token' => $token] = mcpToken(account: ['allowed_ips' => ['10.9.8.0/24']]);
    mcpGet($token, 'products')->assertForbidden()->assertJson(['code' => 'forbidden']);

    ['token' => $local] = mcpToken(account: ['allowed_ips' => ['127.0.0.1']]);
    mcpGet($local, 'products')->assertOk();
});

it('writes one audit row per call with account, token, outcome and redacted arguments', function (): void {
    ['account' => $account, 'token' => $token, 'model' => $model] = mcpToken();

    mcpGet($token, 'orders/search', ['email' => 'jane.doe@example.com'])->assertOk();
    mcpGet($token, 'products/search', ['query' => 'x'])->assertUnprocessable()->assertJson(['code' => 'validation_failed']);

    $rows = McpAuditLog::query()->orderBy('id')->get();
    expect($rows)->toHaveCount(2);

    expect($rows[0]->tool)->toBe('search_customer_orders')
        ->and($rows[0]->outcome)->toBe(McpAuditLogOutcome::Ok)
        ->and($rows[0]->service_account_id)->toBe($account->id)
        ->and($rows[0]->token_id)->toBe($model->id)
        ->and($rows[0]->arguments_redacted)->toBe(['email' => 'j***@example.com'])
        ->and($rows[0]->duration_ms)->toBeGreaterThanOrEqual(0)
        ->and($rows[0]->request_id)->not->toBeNull();

    expect($rows[1]->outcome)->toBe(McpAuditLogOutcome::Invalid)
        ->and($rows[1]->http_status)->toBe(422)
        ->and($rows[1]->error_code)->toBe('validation_failed');

    expect(McpAuditLog::query()->get()->toJson())->not->toContain(explode('|', $token)[1]);
});

it('marks MCP responses as not cacheable and records last use', function (): void {
    ['account' => $account, 'token' => $token, 'model' => $model] = mcpToken();

    $response = mcpGet($token, 'products')->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($account->fresh()->last_used_at)->not->toBeNull()
        ->and($model->fresh()->last_used_at)->not->toBeNull();
});

it('describes the calling account for the MCP server start-up, without secrets', function (): void {
    ['token' => $token, 'account' => $account] = mcpToken([McpScope::ProductsRead, McpScope::ProductsWrite], writable: true);

    mcpGet($token, 'session')
        ->assertOk()
        ->assertExactJson(['success' => true, 'message' => 'OK', 'meta' => [], 'data' => [
            'account' => $account->name,
            'is_read_only' => false,
            'scopes' => ['products:read', 'products:write'],
            'rate_limit_per_minute' => 60,
            'token_expires_at' => null,
        ]]);
});
