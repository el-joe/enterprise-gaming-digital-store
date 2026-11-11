<?php

/**
 * M12 auth pages: they replace the JSON landing pages for browsers and post to the existing
 * session routes. JSON clients keep their envelopes.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\AuthenticationService;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

it('renders the sign-in and registration pages in both languages, posting to the session routes', function (string $prefix, string $login, string $register): void {
    $page = $this->get($prefix.'/login')->assertOk()
        ->assertSee($login)
        ->assertSee('data-action="'.url('/login').'"', false)
        ->assertSee('data-error-for="email"', false)
        ->assertSee('noindex,follow');
    assertCspSafeHtml($page);

    $this->get($prefix.'/register')->assertOk()->assertSee($register)->assertSee('data-action="'.url('/register').'"', false);
})->with([
    'en' => ['', 'Welcome back', 'Create your account'],
    'ar' => ['/ar', 'مرحبًا بعودتك', 'أنشئ حسابك'],
]);

it('sends a signed-in customer from sign-in and registration to the account page', function (): void {
    $this->actingAs(User::factory()->create(['name' => 'Sara Ali']));

    $this->get('/login')->assertRedirect(url('/account'));
    $this->get('/ar/register')->assertRedirect(url('/ar/account'));
    $this->get('/account')->assertOk()->assertSee('Welcome back, Sara Ali')->assertSee('data-module="logout"', false);
});

it('requires sign-in for the account and verification pages and redirects guests to the localized sign-in page', function (): void {
    $this->get('/account')->assertRedirect(url('/login'));
    $this->get('/ar/email/verify')->assertRedirect(url('/ar/login'));
});

it('shows the two-factor page only while a challenge is pending', function (): void {
    $this->get('/two-factor-challenge')->assertRedirect(url('/login'));

    $this->withSession([AuthenticationService::PENDING_KEY => ['id' => 1]])
        ->get('/two-factor-challenge')->assertOk()
        ->assertSee('Two-step verification')
        ->assertSee('data-action="'.url('/two-factor-challenge').'"', false);
});

it('shows the forgotten password page and the verification page for an unverified customer', function (): void {
    $this->get('/forgot-password')->assertOk()->assertSee('data-action="'.url('/forgot-password').'"', false);

    $this->actingAs(User::factory()->unverified()->create())->get('/email/verify')->assertOk()
        ->assertSee('Verify your email')
        ->assertSee('data-action="'.url('/email/verification-notification').'"', false);
});

it('renders the reset page for the email link in a browser and keeps the JSON echo for API clients', function (): void {
    config(['auth.links.password_reset_url' => null]);

    $this->get('/reset-password/abc123?email=sara%40example.com')->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertSee('Choose a new password')
        ->assertSee('name="token" value="abc123"', false)
        ->assertSee('value="sara@example.com"', false);

    $this->getJson('/reset-password/abc123?email=sara@example.com')->assertOk()->assertJsonPath('data.token', 'abc123');
});

it('uses the language the shopper last browsed in for the reset page', function (): void {
    config(['auth.links.password_reset_url' => null]);

    $this->get('/ar/login')->assertOk();
    $this->get('/reset-password/abc123')->assertOk()->assertSee('dir="rtl"', false)->assertSee('اختر كلمة مرور جديدة');
});
