<?php

/**
 * Contract fixtures (M-A7, G11): mobile authentication (API_DOCUMENTATION.md 1b) and app config
 * (1d), as the app calls them. Fixtures land in docs/api-fixtures/{auth,app_config}.
 */

use App\Domains\Identity\Models\User;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    contractSetUp();
    Notification::fake();
});

function contractCustomer(array $attributes = []): User
{
    return User::factory()->create(['name' => 'Sara Customer', 'email' => 'sara@example.com', ...$attributes]);
}

describe('auth', function (): void {
    it('registers', function (): void {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

        contractFixture('auth/register_created', mobileCall('POST', 'auth/register', [
            'name' => 'Sara Customer', 'email' => 'sara@example.com', 'password' => 'long-enough-passphrase',
            'password_confirmation' => 'long-enough-passphrase', 'device_name' => 'Pixel 9',
        ])->assertCreated());

        contractFixture('auth/register_validation_failed', mobileCall('POST', 'auth/register', [
            'name' => '', 'email' => 'not-an-email', 'password' => 'short', 'password_confirmation' => 'other', 'device_name' => 'Pixel 9',
        ])->assertUnprocessable()->assertJsonPath('code', 'validation_failed'));
    });

    it('signs in with and without a second factor', function (): void {
        contractCustomer();
        contractFixture('auth/login_ok', mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])->assertOk());

        contractCustomer(['email' => 'totp@example.com', 'name' => 'Omar Secure', 'two_factor_secret' => MOBILE_TOTP_SECRET, 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['recovery-code-one']]);
        $challenge = mobileCall('POST', 'auth/login', ['email' => 'totp@example.com', 'password' => 'password', 'device_name' => 'Pixel 9'])
            ->assertOk()->assertJsonPath('data.two_factor_required', true);
        contractFixture('auth/login_two_factor_required', $challenge);

        $token = $challenge->json('data.challenge.token');
        contractFixture('auth/two_factor_invalid', mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $token, 'code' => '000000'])
            ->assertUnprocessable()->assertJsonPath('code', 'two_factor_invalid'));
        contractFixture('auth/two_factor_challenge_ok', mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $token, 'code' => mobileTotp()])->assertOk());
        contractFixture('auth/two_factor_expired', mobileCall('POST', 'auth/two-factor-challenge', ['challenge_token' => $token, 'code' => mobileTotp()])
            ->assertUnprocessable()->assertJsonPath('code', 'two_factor_expired'));
    });

    it('refuses wrong credentials and then rate limits', function (): void {
        contractCustomer();

        contractFixture('auth/login_invalid_credentials', mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'wrong-password', 'device_name' => 'Pixel 9'])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed'));

        foreach (range(2, 5) as $attempt) {
            mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'wrong-password', 'device_name' => 'Pixel 9'])->assertUnprocessable();
        }

        contractFixture('auth/login_rate_limited', mobileCall('POST', 'auth/login', ['email' => 'sara@example.com', 'password' => 'wrong-password', 'device_name' => 'Pixel 9'])
            ->assertTooManyRequests()->assertJsonPath('code', 'rate_limited'));
    });

    it('refreshes, detects reuse and reads the signed-in user', function (): void {
        $pair = mobileSignIn(contractCustomer());

        contractFixture('auth/me_ok', mobileCall('GET', 'auth/me', token: $pair['access_token'])->assertOk());
        contractFixture('auth/me_unauthenticated', mobileCall('GET', 'auth/me', token: 'expired-or-unknown')->assertUnauthorized()->assertJsonPath('code', 'unauthenticated'));
        contractFixture('auth/refresh_ok', mobileCall('POST', 'auth/refresh', ['refresh_token' => $pair['refresh_token']])->assertOk());
        contractFixture('auth/refresh_token_invalid', mobileCall('POST', 'auth/refresh', ['refresh_token' => $pair['refresh_token']])
            ->assertUnauthorized()->assertJsonPath('code', 'refresh_token_invalid'));
    });

    it('signs out this device and every device', function (): void {
        $user = contractCustomer();

        contractFixture('auth/logout_ok', mobileCall('POST', 'auth/logout', token: mobileSignIn($user)['access_token'])->assertOk());
        contractFixture('auth/logout_all_ok', mobileCall('POST', 'auth/logout-all', token: mobileSignIn($user, 'Tablet')['access_token'])->assertOk());
    });
});

describe('app config', function (): void {
    it('returns the public configuration with an update verdict', function (): void {
        CurrencyFactory::ensure('USD');
        config(['mobile.app.android_min_version' => '1.2.0', 'mobile.app.android_latest_version' => '1.4.0', 'mobile.app.support_url' => 'https://help.boxcards.test', 'mobile.app.terms_url' => '/pages/terms']);
        gatewayFor('stripe');

        contractFixture('app_config/config_ok', mobileCall('GET', 'app/config?platform=android&version=1.1.0')->assertOk()->assertJsonPath('data.config.update.required', true));
        contractFixture('app_config/config_ar', statelessCall('GET', 'api/v1/mobile/app/config', headers: ['Accept-Language' => 'ar'])->assertOk());
        contractFixture('app_config/config_validation_failed', mobileCall('GET', 'app/config?platform=windows&version=1')->assertUnprocessable());
    });
});
