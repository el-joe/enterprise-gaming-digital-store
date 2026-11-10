<?php

use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Identity\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Sanctum\PersonalAccessToken;

it('returns the signed-in customer from me', function (): void {
    $user = User::factory()->create();
    $tokens = mobileSignIn($user);

    mobileCall('GET', 'auth/me', token: $tokens['access_token'])
        ->assertOk()
        ->assertExactJsonStructure(['success', 'message', 'data' => ['user' => ['public_id', 'name', 'email', 'type', 'email_verified', 'two_factor_enabled']], 'meta'])
        ->assertJsonPath('data.user.public_id', $user->public_id);
});

it('refuses me without a token, with a session, or with an unknown token', function (): void {
    $user = User::factory()->create();

    mobileCall('GET', 'auth/me')->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
    mobileCall('GET', 'auth/me', token: '1|not-a-real-token')->assertUnauthorized();

    $this->actingAs($user)->getJson('/api/v1/mobile/auth/me')->assertUnauthorized();
});

it('rejects an expired access token', function (): void {
    $tokens = mobileSignIn(User::factory()->create());

    $this->travel(16)->minutes();

    mobileCall('GET', 'auth/me', token: $tokens['access_token'])->assertUnauthorized();
});

it('rejects a customer token without the exact customer ability', function (array $abilities): void {
    $user = User::factory()->create();
    $token = $user->createToken('Pixel 9', $abilities, now()->addMinutes(15))->plainTextToken;

    mobileCall('GET', 'auth/me', token: $token)->assertUnauthorized();
})->with([
    'wildcard' => [['*']],
    'none' => [[]],
]);

it('rejects the token of an account suspended after sign-in', function (): void {
    $user = User::factory()->create();
    $tokens = mobileSignIn($user);

    $user->forceFill(['status' => UserStatus::Suspended])->save();

    mobileCall('GET', 'auth/me', token: $tokens['access_token'])->assertForbidden();
});

it('rotates the refresh token and replaces the access token', function (): void {
    $user = User::factory()->create();
    $first = mobileSignIn($user);
    $family = MobileRefreshToken::query()->sole();

    $this->travel(10)->days();

    $second = mobileCall('POST', 'auth/refresh', ['refresh_token' => $first['refresh_token']])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->json('data.tokens');

    $rows = MobileRefreshToken::query()->orderBy('id')->get();

    expect($second['refresh_token'])->not->toBe($first['refresh_token'])
        ->and($rows)->toHaveCount(2)
        ->and($rows[0]->used_at)->not->toBeNull()
        ->and($rows[1]->family_id)->toBe($family->family_id)
        ->and($rows[1]->expires_at->equalTo($family->expires_at))->toBeTrue()
        ->and($second['refresh_token_expires_at'])->toBe($family->expires_at->toIso8601String())
        ->and($rows[1]->device_name)->toBe('Pixel 9');

    mobileCall('GET', 'auth/me', token: $first['access_token'])->assertUnauthorized();
    mobileCall('GET', 'auth/me', token: $second['access_token'])->assertOk();
    mobileCall('POST', 'auth/refresh', ['refresh_token' => $second['refresh_token']])->assertOk();
});

it('revokes the whole family and audits when a used refresh token is presented again', function (): void {
    $user = User::factory()->create();
    $stolen = mobileSignIn($user);
    $current = mobileCall('POST', 'auth/refresh', ['refresh_token' => $stolen['refresh_token']])->json('data.tokens');
    $otherDevice = mobileSignIn($user, 'iPad');

    mobileCall('POST', 'auth/refresh', ['refresh_token' => $stolen['refresh_token']])
        ->assertUnauthorized()
        ->assertJsonPath('code', 'refresh_token_invalid');

    mobileCall('POST', 'auth/refresh', ['refresh_token' => $current['refresh_token']])->assertUnauthorized()->assertJsonPath('code', 'refresh_token_invalid');
    mobileCall('GET', 'auth/me', token: $current['access_token'])->assertUnauthorized();
    mobileCall('GET', 'auth/me', token: $otherDevice['access_token'])->assertOk();

    $family = MobileRefreshToken::query()->where('device_name', 'Pixel 9')->get();
    expect($family->pluck('revocation_reason')->unique()->all())->toBe([TokenRevocationReason::Reuse]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'auth.mobile_refresh_token_reused', 'actor_type' => 'system', 'subject_type' => 'user', 'subject_id' => $user->id]);
    $this->assertDatabaseHas('security_events', ['type' => 'auth.refresh_token_reused', 'user_id' => $user->id, 'severity' => 'warning']);
    expect(json_encode($this->getConnection()->table('audit_logs')->get()))->not->toContain($stolen['refresh_token'])->not->toContain($current['refresh_token']);
});

it('refuses an unknown or expired refresh token', function (): void {
    $tokens = mobileSignIn(User::factory()->create());

    mobileCall('POST', 'auth/refresh', ['refresh_token' => str_repeat('x', 64)])->assertUnauthorized()->assertJsonPath('code', 'refresh_token_invalid');

    $this->travel(31)->days();

    mobileCall('POST', 'auth/refresh', ['refresh_token' => $tokens['refresh_token']])->assertUnauthorized()->assertJsonPath('code', 'refresh_token_invalid');
});

it('refuses to refresh for an account suspended after sign-in', function (): void {
    $user = User::factory()->create();
    $tokens = mobileSignIn($user);

    $user->forceFill(['status' => UserStatus::Suspended])->save();

    mobileCall('POST', 'auth/refresh', ['refresh_token' => $tokens['refresh_token']])->assertUnauthorized();

    expect(MobileRefreshToken::query()->sole()->revocation_reason)->toBe(TokenRevocationReason::AccountInactive);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('throttles refresh attempts per token', function (): void {
    foreach (range(1, 5) as $attempt) {
        mobileCall('POST', 'auth/refresh', ['refresh_token' => 'guess'])->assertUnauthorized();
    }

    mobileCall('POST', 'auth/refresh', ['refresh_token' => 'guess'])->assertTooManyRequests();
});

it('signs out the current device only', function (): void {
    $user = User::factory()->create();
    $phone = mobileSignIn($user, 'Pixel 9');
    $tablet = mobileSignIn($user, 'iPad');

    mobileCall('POST', 'auth/logout', token: $phone['access_token'])->assertOk();

    mobileCall('GET', 'auth/me', token: $phone['access_token'])->assertUnauthorized();
    mobileCall('POST', 'auth/refresh', ['refresh_token' => $phone['refresh_token']])->assertUnauthorized();
    mobileCall('GET', 'auth/me', token: $tablet['access_token'])->assertOk();
    expect(MobileRefreshToken::query()->where('device_name', 'Pixel 9')->sole()->revocation_reason)->toBe(TokenRevocationReason::Logout);
    $this->assertDatabaseCount('audit_logs', 2);
});

it('signs out every device', function (): void {
    $user = User::factory()->create();
    $phone = mobileSignIn($user, 'Pixel 9');
    $tablet = mobileSignIn($user, 'iPad');
    $otherCustomer = mobileSignIn(User::factory()->create());

    mobileCall('POST', 'auth/logout-all', token: $phone['access_token'])->assertOk();

    mobileCall('GET', 'auth/me', token: $tablet['access_token'])->assertUnauthorized();
    mobileCall('POST', 'auth/refresh', ['refresh_token' => $tablet['refresh_token']])->assertUnauthorized();
    mobileCall('GET', 'auth/me', token: $otherCustomer['access_token'])->assertOk();
    expect(MobileRefreshToken::query()->where('user_id', $user->id)->pluck('revocation_reason')->unique()->all())->toBe([TokenRevocationReason::LogoutAll]);
});

it('refuses logout without a customer token', function (): void {
    mobileCall('POST', 'auth/logout')->assertUnauthorized();
    mobileCall('POST', 'auth/logout-all')->assertUnauthorized();
});

it('never accepts a customer token on MCP, automation or admin routes', function (): void {
    $tokens = mobileSignIn(User::factory()->create());

    $this->withToken($tokens['access_token'])->getJson('/admin/api/me')->assertUnauthorized();
    mcpGet($tokens['access_token'], 'session')->assertUnauthorized();
    enableAutomationWorkflows(['A01']);
    automationGet($tokens['access_token'], 'monitor/exceptions')->assertUnauthorized();
    mobileCall('GET', 'auth/me', token: $tokens['access_token'])->assertOk();
});

it('never accepts a service-account token on mobile routes, whatever its abilities', function (ServiceAccountKind $kind, array $abilities): void {
    $token = ServiceAccount::factory()->create(['kind' => $kind])->createToken('service', $abilities)->plainTextToken;

    mobileCall('GET', 'auth/me', token: $token)->assertUnauthorized();
    mobileCall('POST', 'auth/logout', token: $token)->assertUnauthorized();
    $this->assertDatabaseCount('personal_access_tokens', 1);
})->with([
    'mcp with customer ability' => [ServiceAccountKind::Mcp, ['customer']],
    'mcp with wildcard' => [ServiceAccountKind::Mcp, ['*']],
    'n8n with customer ability' => [ServiceAccountKind::N8n, ['customer']],
]);

it('keeps MCP tokens working beside customer tokens', function (): void {
    mobileSignIn(User::factory()->create());
    $mcp = mcpToken();

    mcpGet($mcp['token'], 'session')->assertOk();
});

it('prunes expired refresh tokens, challenges and customer access tokens only', function (): void {
    $user = User::factory()->create();
    mobileSignIn($user, 'Old phone');
    MobileLoginChallenge::factory()->create(['expires_at' => now()->addMinutes(5)]);
    $mcp = mcpToken(expiresAt: now()->addDays(10));

    $this->travel(31)->days();
    mobileSignIn($user, 'New phone');
    MobileLoginChallenge::factory()->create(['expires_at' => now()->addMinutes(5)]);
    $mcp['model']->forceFill(['expires_at' => now()->subDay()])->save();

    $this->artisan('auth:prune-mobile-tokens')->assertSuccessful();

    expect(MobileRefreshToken::query()->pluck('device_name')->all())->toBe(['New phone'])
        ->and(MobileLoginChallenge::query()->count())->toBe(1)
        ->and(PersonalAccessToken::query()->where('tokenable_type', 'user')->count())->toBe(1)
        ->and(PersonalAccessToken::query()->whereKey($mcp['model']->id)->exists())->toBeTrue();
});

it('schedules the prune hourly on one server', function (): void {
    $event = collect(app(Schedule::class)->events())->first(static fn (Event $event): bool => str_contains((string) $event->command, 'auth:prune-mobile-tokens'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 * * * *')
        ->and($event->onOneServer)->toBeTrue()
        ->and($event->withoutOverlapping)->toBeTrue();
});
