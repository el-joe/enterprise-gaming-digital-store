<?php

use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

function fakePasswordBreachApi(string $body = ''): void
{
    Http::fake(['api.pwnedpasswords.com/*' => Http::response($body)]);
}

/**
 * @return array<string, string>
 */
function registrationPayload(array $overrides = []): array
{
    return [
        'name' => 'Sara Customer',
        'email' => 'Sara@Example.com',
        'password' => 'long-enough-passphrase',
        'password_confirmation' => 'long-enough-passphrase',
        ...$overrides,
    ];
}

it('registers a customer, signs them in and sends verification', function (): void {
    Notification::fake();
    fakePasswordBreachApi();

    $this->postJson('/register', registrationPayload())
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.type', 'customer')
        ->assertJsonPath('data.user.email_verified', false);

    $user = User::query()->where('email', 'sara@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user->public_id)->toHaveLength(26);
    $this->assertDatabaseHas('customer_profiles', ['user_id' => $user->id]);
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('cannot register as staff through mass assignment', function (): void {
    Notification::fake();
    fakePasswordBreachApi();

    $this->postJson('/register', registrationPayload(['type' => 'staff', 'status' => 'suspended']))->assertCreated();

    $user = User::query()->where('email', 'sara@example.com')->firstOrFail();

    expect($user->type)->toBe(UserType::Customer);
});

it('rejects a short password and a duplicate email', function (): void {
    fakePasswordBreachApi();
    User::factory()->create(['email' => 'sara@example.com']);

    $this->postJson('/register', registrationPayload(['password' => 'short', 'password_confirmation' => 'short']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password', 'email']);
});

it('rejects a password found in a breach', function (): void {
    $password = 'compromised-passphrase';
    fakePasswordBreachApi(strtoupper(substr(sha1($password), 5)).':42');

    $this->postJson('/register', registrationPayload(['password' => $password, 'password_confirmation' => $password]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('signs in with valid credentials and records the login', function (): void {
    $user = User::factory()->create(['email' => 'sara@example.com']);

    $this->postJson('/login', ['email' => 'SARA@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('data.two_factor_required', false)
        ->assertJsonPath('data.user.public_id', $user->public_id);

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('rejects a wrong password with the same error as an unknown email', function (): void {
    User::factory()->create(['email' => 'sara@example.com']);

    $wrongPassword = $this->postJson('/login', ['email' => 'sara@example.com', 'password' => 'nope'])->assertStatus(422);
    $unknownEmail = $this->postJson('/login', ['email' => 'other@example.com', 'password' => 'nope'])->assertStatus(422);

    expect($wrongPassword->json('errors'))->toBe($unknownEmail->json('errors'));
    $this->assertGuest();
});

it('refuses suspended accounts', function (): void {
    User::factory()->suspended()->create(['email' => 'sara@example.com']);

    $this->postJson('/login', ['email' => 'sara@example.com', 'password' => 'password'])->assertStatus(422);

    $this->assertGuest();
});

it('denies a suspended account holding a live session on account routes', function (): void {
    $this->actingAs(User::factory()->suspended()->create())
        ->postJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])
        ->assertForbidden();
});

it('audits staff logins but not customer logins', function (): void {
    $staff = User::factory()->staff()->create(['email' => 'staff@example.com']);
    User::factory()->create(['email' => 'customer@example.com']);

    $this->postJson('/login', ['email' => 'customer@example.com', 'password' => 'password'])->assertOk();
    $this->assertDatabaseCount('audit_logs', 0);
    $this->postJson('/logout')->assertOk();

    $this->postJson('/login', ['email' => 'staff@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('data.two_factor_enrollment_required', true);

    $this->assertDatabaseHas('audit_logs', ['action' => 'auth.staff_login', 'actor_type' => 'staff', 'actor_id' => $staff->id]);
});

it('signs out', function (): void {
    $this->actingAs(User::factory()->create())->postJson('/logout')->assertOk();

    $this->assertGuest();
});

it('verifies email through a signed link', function (): void {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    $this->actingAs($user)->getJson($url)->assertOk();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects a verification link with a bad signature or wrong hash', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->getJson(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))
        ->assertForbidden();

    $wrongHash = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1('other@example.com')]);

    $this->actingAs($user)->getJson($wrongHash)->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('resends the verification email', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->postJson('/email/verification-notification')->assertOk();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('responds identically to a reset request whether or not the email exists', function (): void {
    Notification::fake();
    $user = User::factory()->create(['email' => 'sara@example.com']);

    $known = $this->postJson('/forgot-password', ['email' => 'sara@example.com'])->assertOk();
    $unknown = $this->postJson('/forgot-password', ['email' => 'ghost@example.com'])->assertOk();

    expect($known->json('message'))->toBe($unknown->json('message'));
    Notification::assertSentTo($user, ResetPassword::class);
});

it('expires reset tokens after thirty minutes', function (): void {
    expect(config('auth.passwords.users.expire'))->toBe(30);
});

it('resets a password with a valid token', function (): void {
    fakePasswordBreachApi();
    $user = User::factory()->create(['email' => 'sara@example.com']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/reset-password', [
        'token' => $token,
        'email' => 'sara@example.com',
        'password' => 'brand-new-passphrase',
        'password_confirmation' => 'brand-new-passphrase',
    ])->assertOk();

    expect(Hash::check('brand-new-passphrase', $user->fresh()->password))->toBeTrue();
});

it('rejects an invalid reset token', function (): void {
    fakePasswordBreachApi();
    User::factory()->create(['email' => 'sara@example.com']);

    $this->postJson('/reset-password', [
        'token' => 'not-a-token',
        'email' => 'sara@example.com',
        'password' => 'brand-new-passphrase',
        'password_confirmation' => 'brand-new-passphrase',
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

it('applies the stricter staff policy on reset once the token is valid', function (): void {
    fakePasswordBreachApi();
    $staff = User::factory()->staff()->create(['email' => 'staff@example.com']);
    $token = Password::broker()->createToken($staff);

    $this->postJson('/reset-password', [
        'token' => $token,
        'email' => 'staff@example.com',
        'password' => 'alllowercase1',
        'password_confirmation' => 'alllowercase1',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);

    expect(Hash::check('password', $staff->fresh()->password))->toBeTrue();
});
