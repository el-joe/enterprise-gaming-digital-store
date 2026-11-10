<?php

/**
 * M-A5 (G5): profile, password change and signed-in devices, for the web session and the mobile
 * bearer token.
 */

use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\WebSessionRepository;
use App\Domains\Shared\Models\Country;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now(), 'name' => 'Sara']);
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);
});

describe('profile', function (): void {
    it('shows the profile', function (string $client): void {
        accountCall($client, $this->customer, 'GET', 'profile')->assertOk()
            ->assertJsonPath('data.profile.public_id', $this->customer->public_id)
            ->assertJsonPath('data.profile.name', 'Sara')
            ->assertJsonPath('data.profile.email', $this->customer->email)
            ->assertJsonPath('data.profile.locale', null)
            ->assertJsonStructure(['data' => ['profile' => ['email_verified', 'phone', 'display_currency', 'country_code', 'two_factor_enabled']]]);
    })->with(accountClients());

    it('updates name, locale, display currency, country and phone, and audits without the phone number', function (string $client): void {
        CurrencyFactory::ensure('EGP');
        CountryFactory::ensure('EG');

        accountCall($client, $this->customer, 'PATCH', 'profile', [
            'name' => 'Sara Ali',
            'locale' => 'ar',
            'display_currency' => 'egp',
            'country_code' => 'eg',
            'phone' => '+20 100 123 4567',
        ])->assertOk()
            ->assertJsonPath('data.profile.name', 'Sara Ali')
            ->assertJsonPath('data.profile.locale', 'ar')
            ->assertJsonPath('data.profile.display_currency', 'EGP')
            ->assertJsonPath('data.profile.country_code', 'EG')
            ->assertJsonPath('data.profile.phone', '+201001234567');

        $audit = DB::table('audit_logs')->where('action', 'account.profile_updated')->sole();

        expect($this->customer->fresh()->only(['name', 'locale', 'display_currency', 'country_code', 'phone']))
            ->toBe(['name' => 'Sara Ali', 'locale' => 'ar', 'display_currency' => 'EGP', 'country_code' => 'EG', 'phone' => '+201001234567'])
            ->and($audit->actor_id)->toBe($this->customer->id)
            ->and($audit->after)->toContain('phone')->not->toContain('1001234567');
    })->with(accountClients());

    it('changes only the fields sent and clears a field sent as null', function (string $client): void {
        $this->customer->forceFill(['locale' => 'ar', 'phone' => '+201001234567'])->save();

        accountCall($client, $this->customer, 'PATCH', 'profile', ['phone' => null])->assertOk();

        expect($this->customer->fresh()->only(['name', 'locale', 'phone']))->toBe(['name' => 'Sara', 'locale' => 'ar', 'phone' => null]);
    })->with(accountClients());

    it('validates every field', function (string $client, array $body, string $field): void {
        Currency::factory()->create(['code' => 'JPY', 'is_active' => false]);
        Country::query()->create(['code' => 'ZZ', 'name' => 'Closed', 'is_active' => false]);

        accountCall($client, $this->customer, 'PATCH', 'profile', $body)->assertUnprocessable()->assertJsonValidationErrors([$field]);

        expect($this->customer->fresh()->name)->toBe('Sara');
    })->with(accountClients())->with([
        'empty name' => [['name' => ''], 'name'],
        'unsupported locale' => [['locale' => 'fr'], 'locale'],
        'inactive currency' => [['display_currency' => 'JPY'], 'display_currency'],
        'unknown currency' => [['display_currency' => 'XYZ'], 'display_currency'],
        'inactive country' => [['country_code' => 'ZZ'], 'country_code'],
        'phone not E.164' => [['phone' => '0100 123'], 'phone'],
    ]);

    it('localizes the next web request with the saved locale; the app keeps Accept-Language', function (): void {
        accountCall('web', $this->customer, 'PATCH', 'profile', ['locale' => 'ar'])->assertOk();

        accountCall('web', $this->customer, 'GET', 'profile')->assertHeader('Content-Language', 'ar');
        accountCall('mobile', $this->customer, 'GET', 'profile')->assertHeader('Content-Language', 'en');
        accountCall('mobile', $this->customer, 'GET', 'profile', headers: ['Accept-Language' => 'ar'])->assertHeader('Content-Language', 'ar');
    });
});

describe('password change', function (): void {
    it('needs the current password and a new, different, policy-compliant password', function (string $client, array $body, string $field): void {
        accountCall($client, $this->customer, 'PUT', 'password', $body)->assertUnprocessable()->assertJsonValidationErrors([$field]);

        expect(Hash::check('password', $this->customer->fresh()->password))->toBeTrue();
    })->with(accountClients())->with([
        'wrong current password' => [['current_password' => 'not-my-password', 'password' => 'brand-new-passphrase', 'password_confirmation' => 'brand-new-passphrase'], 'current_password'],
        'too short' => [['current_password' => 'password', 'password' => 'short', 'password_confirmation' => 'short'], 'password'],
        'not confirmed' => [['current_password' => 'password', 'password' => 'brand-new-passphrase', 'password_confirmation' => 'other-passphrase'], 'password'],
    ]);

    it('keeps the caller signed in and signs out every other device and browser', function (string $client): void {
        config(['session.driver' => 'database']);
        $otherDevice = mobileSignIn($this->customer, 'Tablet');
        DB::table('sessions')->insert(['id' => str_repeat('a', 40), 'user_id' => $this->customer->id, 'ip_address' => '203.0.113.9', 'user_agent' => 'Firefox', 'payload' => '', 'last_activity' => now()->getTimestamp()]);
        $rememberToken = $this->customer->fresh()->remember_token;

        accountCall($client, $this->customer, 'PUT', 'password', ['current_password' => 'password', 'password' => 'brand-new-passphrase', 'password_confirmation' => 'brand-new-passphrase'])
            ->assertOk()
            ->assertJsonPath('data.signed_out.mobile_families', 1)
            ->assertJsonPath('data.signed_out.web_sessions', 1);

        $fresh = $this->customer->fresh();
        expect(Hash::check('brand-new-passphrase', $fresh->password))->toBeTrue()
            ->and($fresh->remember_token)->not->toBe($rememberToken)
            ->and(DB::table('sessions')->where('id', str_repeat('a', 40))->exists())->toBeFalse()
            ->and(DB::table('audit_logs')->where('action', 'account.password_changed')->where('actor_id', $this->customer->id)->exists())->toBeTrue();

        mobileCall('GET', 'account/profile', token: $otherDevice['access_token'])->assertUnauthorized();
        mobileCall('POST', 'auth/refresh', ['refresh_token' => $otherDevice['refresh_token']])->assertUnauthorized()->assertJsonPath('code', 'refresh_token_invalid');
        accountCall($client, $this->customer, 'GET', 'profile')->assertOk();
    })->with(accountClients());

    it('signs every device out after a password reset by email', function (): void {
        $device = mobileSignIn($this->customer);
        $token = Password::broker()->createToken($this->customer);

        $this->postJson('/reset-password', ['token' => $token, 'email' => $this->customer->email, 'password' => 'brand-new-passphrase', 'password_confirmation' => 'brand-new-passphrase'])->assertOk();

        mobileCall('GET', 'account/profile', token: $device['access_token'])->assertUnauthorized();
        expect(MobileRefreshToken::query()->sole()->revocation_reason->value)->toBe('password_reset');
    });
});

describe('devices', function (): void {
    it('lists mobile sign-ins with masked IPs and marks the caller', function (string $client): void {
        $tablet = mobileSignIn($this->customer, 'Tablet');
        MobileRefreshToken::query()->where('device_name', 'Tablet')->update(['ip' => '203.0.113.45']);
        accountMobileToken($this->customer);

        $devices = collect(accountCall($client, $this->customer, 'GET', 'devices')->assertOk()->json('data.devices'))->keyBy('device_name');

        expect($devices->keys()->sort()->values()->all())->toBe(['Pixel 9', 'Tablet'])
            ->and($devices['Tablet']['ip'])->toBe('203.0.113.x')
            ->and($devices['Tablet']['type'])->toBe('mobile')
            ->and($devices['Tablet']['current'])->toBeFalse()
            ->and($devices['Pixel 9']['current'])->toBe($client === 'mobile')
            ->and($devices['Tablet']['created_at'])->not->toBeNull()
            ->and($devices['Tablet']['last_used_at'])->not->toBeNull()
            ->and(json_encode($devices))->not->toContain($tablet['refresh_token']);
        expect(DB::table('audit_logs')->where('action', 'account.devices_viewed')->exists())->toBeTrue();
    })->with(accountClients());

    it('signs another device out and audits it', function (string $client): void {
        $tablet = mobileSignIn($this->customer, 'Tablet');
        $familyId = MobileRefreshToken::query()->where('device_name', 'Tablet')->value('family_id');

        accountCall($client, $this->customer, 'DELETE', "devices/{$familyId}")->assertOk();

        mobileCall('GET', 'account/profile', token: $tablet['access_token'])->assertUnauthorized();
        mobileCall('POST', 'auth/refresh', ['refresh_token' => $tablet['refresh_token']])->assertUnauthorized();
        expect(DB::table('audit_logs')->where('action', 'account.device_revoked')->exists())->toBeTrue();
        accountCall($client, $this->customer, 'DELETE', "devices/{$familyId}")->assertNotFound();
    })->with(accountClients());

    it('refuses to revoke the calling device', function (): void {
        accountMobileToken($this->customer);
        $familyId = MobileRefreshToken::query()->where('user_id', $this->customer->id)->value('family_id');

        accountCall('mobile', $this->customer, 'DELETE', "devices/{$familyId}")->assertStatus(409)->assertJsonPath('code', 'current_device');
        accountCall('mobile', $this->customer, 'GET', 'profile')->assertOk();
    });

    it('answers 404 for another customer\'s device and an unknown id', function (string $client): void {
        mobileSignIn(User::factory()->create(['email' => 'other@example.com']), 'Their phone');
        $foreign = MobileRefreshToken::query()->where('device_name', 'Their phone')->value('family_id');

        accountCall($client, $this->customer, 'DELETE', "devices/{$foreign}")->assertNotFound();
        accountCall($client, $this->customer, 'DELETE', 'devices/web_'.str_repeat('0', 32))->assertNotFound();
        accountCall($client, $this->customer, 'DELETE', 'devices/01JZZZZZZZZZZZZZZZZZZZZZZZ')->assertNotFound();

        expect(MobileRefreshToken::query()->where('family_id', $foreign)->value('revoked_at'))->toBeNull();
    })->with(accountClients());

    it('lists and signs out browser sessions when sessions are stored in the database', function (): void {
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert([
            ['id' => str_repeat('b', 40), 'user_id' => $this->customer->id, 'ip_address' => '2001:db8::1', 'user_agent' => 'Safari', 'payload' => '', 'last_activity' => now()->getTimestamp()],
            ['id' => str_repeat('c', 40), 'user_id' => User::factory()->create()->id, 'ip_address' => '198.51.100.1', 'user_agent' => 'Chrome', 'payload' => '', 'last_activity' => now()->getTimestamp()],
        ]);

        $response = accountCall('mobile', $this->customer, 'GET', 'devices')->assertOk()->assertJsonPath('meta.web_sessions_listed', true);
        $web = collect($response->json('data.devices'))->firstWhere('type', 'web');

        expect($web['ip'])->toBe('2001:0db8:0000:0000:x')
            ->and($web['id'])->toBe(WebSessionRepository::publicId(str_repeat('b', 40)))
            ->and($response->getContent())->not->toContain(str_repeat('b', 40))
            ->and(collect($response->json('data.devices'))->where('type', 'web'))->toHaveCount(1);

        accountCall('mobile', $this->customer, 'DELETE', "devices/{$web['id']}")->assertOk();
        expect(DB::table('sessions')->where('id', str_repeat('b', 40))->exists())->toBeFalse()
            ->and(DB::table('sessions')->where('id', str_repeat('c', 40))->exists())->toBeTrue();
    });
});
