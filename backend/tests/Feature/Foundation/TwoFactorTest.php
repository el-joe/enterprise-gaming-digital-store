<?php

use App\Domains\Identity\Models\User;
use PragmaRX\Google2FA\Google2FA;

function currentTotp(string $secret): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

const TOTP_SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

it('denies staff without two-factor from admin routes', function (): void {
    $this->actingAs(User::factory()->staff()->create())
        ->getJson('/admin/api/me')
        ->assertForbidden()
        ->assertJsonPath('code', 'two_factor_required');
});

it('denies a session that never passed the challenge even when enrolled', function (): void {
    $this->actingAs(User::factory()->staff()->withTwoFactor()->create())
        ->getJson('/admin/api/me')
        ->assertForbidden()
        ->assertJsonPath('code', 'two_factor_required');
});

it('lets staff enrol and reach admin in the same session', function (): void {
    $staff = User::factory()->staff()->create();

    $enrollment = $this->actingAs($staff)
        ->postJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['data' => ['secret', 'otpauth_uri']]);

    expect($staff->fresh()->getRawOriginal('two_factor_secret'))->not->toBe($enrollment->json('data.secret'));
    $this->getJson('/admin/api/me')->assertForbidden();

    $this->postJson('/api/v1/account/security/two-factor/confirm', ['code' => currentTotp($enrollment->json('data.secret'))])
        ->assertOk()
        ->assertJsonCount(8, 'data.recovery_codes');

    $this->getJson('/admin/api/me')->assertOk()->assertJsonPath('data.user.two_factor_enabled', true);
});

it('requires the current password to enrol', function (): void {
    $this->actingAs(User::factory()->staff()->create())
        ->postJson('/api/v1/account/security/two-factor', ['current_password' => 'wrong'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

it('rejects enrolment confirmation with a wrong code', function (): void {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->postJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])->assertOk();

    $this->postJson('/api/v1/account/security/two-factor/confirm', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonPath('code', 'two_factor_invalid');

    expect($staff->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('does not sign in until the challenge passes', function (): void {
    User::factory()->staff()->withTwoFactor(TOTP_SECRET)->create(['email' => 'staff@example.com']);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('data.two_factor_required', true)
        ->assertJsonPath('data.user', null);

    $this->assertGuest();
    $this->getJson('/admin/api/me')->assertUnauthorized();

    $this->postJson('/two-factor-challenge', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonPath('code', 'two_factor_invalid');
    $this->assertGuest();

    $this->postJson('/two-factor-challenge', ['code' => currentTotp(TOTP_SECRET)])->assertOk();

    $this->assertAuthenticated();
    $this->getJson('/admin/api/me')->assertOk();
});

it('rejects a replayed TOTP code', function (): void {
    User::factory()->staff()->withTwoFactor(TOTP_SECRET)->create(['email' => 'staff@example.com']);
    $code = currentTotp(TOTP_SECRET);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();
    $this->postJson('/two-factor-challenge', ['code' => $code])->assertOk();
    $this->postJson('/logout')->assertOk();

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();
    $this->postJson('/two-factor-challenge', ['code' => $code])
        ->assertStatus(422)
        ->assertJsonPath('code', 'two_factor_invalid');
});

it('accepts a recovery code exactly once', function (): void {
    $staff = User::factory()->staff()->withTwoFactor(TOTP_SECRET)->create(['email' => 'staff@example.com']);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();
    $this->postJson('/two-factor-challenge', ['recovery_code' => 'recovery-code-one'])->assertOk();
    $this->postJson('/logout')->assertOk();

    expect($staff->fresh()->two_factor_recovery_codes)->toBe(['recovery-code-two']);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();
    $this->postJson('/two-factor-challenge', ['recovery_code' => 'recovery-code-one'])->assertStatus(422);
});

it('expires a pending challenge after five minutes', function (): void {
    User::factory()->staff()->withTwoFactor(TOTP_SECRET)->create(['email' => 'staff@example.com']);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();

    $this->travel(6)->minutes();

    $this->postJson('/two-factor-challenge', ['code' => currentTotp(TOTP_SECRET)])
        ->assertStatus(422)
        ->assertJsonPath('code', 'two_factor_expired');
});

it('throttles challenge attempts', function (): void {
    User::factory()->staff()->withTwoFactor(TOTP_SECRET)->create(['email' => 'staff@example.com']);

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])->assertOk();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/two-factor-challenge', ['code' => '000000'])->assertStatus(422);
    }

    $this->postJson('/two-factor-challenge', ['code' => '000000'])->assertStatus(429);
});

it('refuses to let staff disable two-factor', function (): void {
    $staff = User::factory()->staff()->withTwoFactor()->create();

    $this->actingAs($staff)
        ->deleteJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])
        ->assertForbidden();

    expect($staff->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('lets a customer disable two-factor with the current password', function (): void {
    $customer = User::factory()->withTwoFactor()->create();

    $this->actingAs($customer)
        ->deleteJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])
        ->assertOk();

    expect($customer->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('refuses to start enrolment when already enabled', function (): void {
    $this->actingAs(User::factory()->withTwoFactor()->create())
        ->postJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])
        ->assertStatus(409)
        ->assertJsonPath('code', 'two_factor_already_enabled');
});

it('ends an admin session after thirty idle minutes', function (): void {
    actingAsVerifiedStaff();

    $this->getJson('/admin/api/me')->assertOk();

    $this->travel(31)->minutes();

    $this->getJson('/admin/api/me')->assertUnauthorized();
    $this->assertGuest();
});
