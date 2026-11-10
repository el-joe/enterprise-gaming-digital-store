<?php

use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function mobileRegistrationPayload(array $overrides = []): array
{
    return [
        'name' => 'Sara Customer',
        'email' => 'Sara@Example.com',
        'password' => 'long-enough-passphrase',
        'password_confirmation' => 'long-enough-passphrase',
        'device_name' => 'Pixel 9',
        ...$overrides,
    ];
}

it('registers a customer and returns a token pair without a session', function (): void {
    Notification::fake();
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

    $response = mobileCall('POST', 'auth/register', mobileRegistrationPayload(['type' => 'staff']))
        ->assertCreated()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.user.type', 'customer')
        ->assertJsonPath('data.user.email_verified', false)
        ->assertJsonPath('data.tokens.token_type', 'Bearer')
        ->assertCookieMissing((string) config('session.cookie'));

    $user = User::query()->where('email', 'sara@example.com')->firstOrFail();

    expect($user->type)->toBe(UserType::Customer);
    $this->assertDatabaseHas('customer_profiles', ['user_id' => $user->id]);
    Notification::assertSentTo($user, VerifyEmail::class);
    mobileCall('GET', 'auth/me', token: $response->json('data.tokens.access_token'))->assertOk()->assertJsonPath('data.user.public_id', $user->public_id);
});

it('requires a device name to register and to sign in', function (): void {
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

    mobileCall('POST', 'auth/register', mobileRegistrationPayload(['device_name' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);

    mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);
});

it('signs a customer in with a customer token and a refresh token', function (): void {
    $user = User::factory()->create(['email' => 'sara@example.com']);

    $response = mobileCall('POST', 'auth/login', ['email' => 'SARA@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.two_factor_required', false)
        ->assertJsonPath('data.challenge', null)
        ->assertJsonPath('data.user.public_id', $user->public_id)
        ->assertJsonPath('data.tokens.token_type', 'Bearer');

    $accessToken = PersonalAccessToken::findToken($response->json('data.tokens.access_token'));
    $refreshToken = MobileRefreshToken::query()->sole();

    expect($accessToken->tokenable_id)->toBe($user->id)
        ->and($accessToken->abilities)->toBe(['customer'])
        ->and($accessToken->name)->toBe('Pixel 9')
        ->and($accessToken->expires_at->equalTo(now()->addMinutes(15)->startOfSecond()))->toBeTrue()
        ->and($refreshToken->token_hash)->toBe(hash('sha256', $response->json('data.tokens.refresh_token')))
        ->and($refreshToken->access_token_id)->toBe($accessToken->id)
        ->and($refreshToken->expires_at->equalTo(now()->addDays(30)->startOfSecond()))->toBeTrue()
        ->and($user->fresh()->last_login_at)->not->toBeNull();
});

it('refuses a wrong password with the same error as an unknown email', function (): void {
    User::factory()->create(['email' => 'sara@example.com']);

    $wrongPassword = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'nope', 'device_name' => 'Pixel 9'])->assertUnprocessable();
    $unknownEmail = mobileCall('POST', 'auth/login', ['email' => 'other@example.com', 'password' => 'nope', 'device_name' => 'Pixel 9'])->assertUnprocessable();

    expect($wrongPassword->json('errors'))->toBe($unknownEmail->json('errors'))->toBe(['email' => [__('auth.failed')]]);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('refuses staff accounts with the generic sign-in error', function (): void {
    User::factory()->staff()->create(['email' => 'staff@example.com']);

    mobileCall('POST', 'auth/login', ['email' => 'staff@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', __('auth.failed'));

    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->assertDatabaseCount('audit_logs', 0);
});

it('refuses staff accounts with two-factor enabled before any challenge', function (): void {
    User::factory()->staff()->withTwoFactor()->create(['email' => 'staff@example.com']);

    mobileCall('POST', 'auth/login', ['email' => 'staff@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', __('auth.failed'));

    $this->assertDatabaseCount('mobile_login_challenges', 0);
});

it('refuses suspended and closed accounts like the web login', function (UserStatus $status): void {
    User::factory()->create(['email' => 'sara@example.com', 'status' => $status]);

    mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', __('auth.failed'));

    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([UserStatus::Suspended, UserStatus::Closed]);

it('returns a challenge instead of tokens when two-factor is enabled', function (): void {
    $user = User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);

    $response = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertOk()
        ->assertJsonPath('data.two_factor_required', true)
        ->assertJsonPath('data.tokens', null)
        ->assertJsonPath('data.user', null);

    $challenge = MobileLoginChallenge::query()->sole();

    expect($challenge->user_id)->toBe($user->id)
        ->and($challenge->token_hash)->toBe(hash('sha256', $response->json('data.challenge.token')))
        ->and($response->json('data.challenge.expires_at'))->toBe(now()->addSeconds(300)->toIso8601String());
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('completes the two-factor challenge with a code or a recovery code', function (array $proof): void {
    $user = User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);
    $challenge = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'iPhone 17'])->json('data.challenge.token');

    $body = ['challenge_token' => $challenge, ...array_map(static fn (string|Closure $value): string => $value instanceof Closure ? $value() : $value, $proof)];

    $response = mobileCall('POST', 'auth/two-factor-challenge', $body)
        ->assertOk()
        ->assertJsonPath('data.user.public_id', $user->public_id);

    expect(PersonalAccessToken::findToken($response->json('data.tokens.access_token'))->name)->toBe('iPhone 17')
        ->and(MobileLoginChallenge::query()->sole()->consumed_at)->not->toBeNull();

    mobileCall('POST', 'auth/two-factor-challenge', $body)->assertUnprocessable()->assertJsonPath('code', 'two_factor_expired');
})->with([
    'totp' => [['code' => fn (): string => mobileTotp()]],
    'recovery code' => [['recovery_code' => 'recovery-code-one']],
]);

it('rejects a wrong code and spends the challenge after the attempt limit', function (): void {
    User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);
    config(['mobile.auth.challenge_max_attempts' => 2]);
    $challenge = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])->json('data.challenge.token');

    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => '000000'])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'two_factor_invalid');
    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => '000000'])
        ->assertJsonPath('code', 'two_factor_invalid');

    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => mobileTotp()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'two_factor_expired');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('rejects an expired or unknown challenge', function (): void {
    User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);
    $challenge = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])->json('data.challenge.token');

    $this->travel(301)->seconds();

    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => mobileTotp()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'two_factor_expired');
    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => 'not-a-challenge', 'code' => mobileTotp()])
        ->assertJsonPath('code', 'two_factor_expired');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('rejects a challenge of an account suspended after the password step', function (): void {
    $user = User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);
    $challenge = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])->json('data.challenge.token');

    $user->forceFill(['status' => UserStatus::Suspended])->save();

    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => mobileTotp()])
        ->assertJsonPath('code', 'two_factor_expired');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('audits the first sign-in from each device name only', function (): void {
    $user = User::factory()->create();

    mobileSignIn($user, 'Pixel 9');
    mobileSignIn($user, 'Pixel 9');
    mobileSignIn($user, 'iPhone 17');

    $rows = $this->getConnection()->table('audit_logs')->where('action', 'auth.mobile_new_device')->orderBy('id')->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('actor_type')->unique()->all())->toBe(['customer'])
        ->and($rows->pluck('actor_id')->unique()->all())->toBe([$user->id])
        ->and($rows->pluck('subject_id')->unique()->all())->toBe([$user->id])
        ->and(array_map(static fn (string $after): string => json_decode($after, true)['device_name'], $rows->pluck('after')->all()))->toBe(['Pixel 9', 'iPhone 17']);
});

it('throttles sign-in attempts per email and IP with the auth limiter', function (): void {
    User::factory()->create(['email' => 'sara@example.com']);

    foreach (range(1, 5) as $attempt) {
        mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'nope', 'device_name' => 'Pixel 9'])->assertUnprocessable();
    }

    mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
        ->assertTooManyRequests()
        ->assertJsonPath('code', 'rate_limited');
    $this->assertDatabaseHas('security_events', ['type' => 'auth.throttled']);
    mobileCall('POST', 'auth/login', ['email' => 'other@example.com', 'password' => 'nope', 'device_name' => 'Pixel 9'])->assertUnprocessable();
});

it('throttles registration with the auth limiter', function (): void {
    foreach (range(1, 5) as $attempt) {
        mobileCall('POST', 'auth/register', mobileRegistrationPayload(['password_confirmation' => 'different']))->assertUnprocessable();
    }

    mobileCall('POST', 'auth/register', mobileRegistrationPayload())->assertTooManyRequests();
});

it('throttles two-factor attempts per challenge with the otp limiter', function (): void {
    User::factory()->withTwoFactor(MOBILE_TOTP_SECRET)->create(['email' => 'sara@example.com']);
    config(['mobile.auth.challenge_max_attempts' => 50]);
    $challenge = mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])->json('data.challenge.token');

    foreach (range(1, 5) as $attempt) {
        mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => '000000'])->assertUnprocessable();
    }

    mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $challenge, 'code' => mobileTotp()])->assertTooManyRequests();
});

it('keeps the web login on the session and issues no tokens', function (): void {
    $user = User::factory()->create(['email' => 'sara@example.com']);

    $this->postJson('/login', ['email' => 'sara@example.com', 'password' => 'password'])->assertOk();

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->assertDatabaseCount('mobile_refresh_tokens', 0);
});
