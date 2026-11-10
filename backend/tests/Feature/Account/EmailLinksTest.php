<?php

/**
 * M-A5 (G10): the links in account emails work from a mail client with no session, and land on
 * an operator-configured page or app link.
 */

use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

function verificationLink(User $user, ?string $email = null): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($email ?? $user->email)]);
}

describe('email verification without a session', function (): void {
    it('verifies from a mail client and redirects to the configured landing', function (): void {
        Event::fake([Verified::class]);
        config(['auth.links.email_verified_url' => 'https://boxcards.shop/app/email-verified?status={status}']);
        $user = User::factory()->unverified()->create();

        $this->get(verificationLink($user))->assertRedirect('https://boxcards.shop/app/email-verified?status=verified');

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
        $this->assertGuest();
        Event::assertDispatched(Verified::class);

        $this->get(verificationLink($user))->assertRedirect('https://boxcards.shop/app/email-verified?status=already_verified');
    });

    it('redirects to the site root with the status when no landing is configured', function (): void {
        $user = User::factory()->unverified()->create();

        $this->get(verificationLink($user))->assertRedirect(rtrim((string) config('app.url'), '/').'/?email_verification=verified');
    });

    it('answers JSON clients with the envelope, without a session or token', function (): void {
        $user = User::factory()->unverified()->create();

        statelessCall('GET', ltrim((string) parse_url(verificationLink($user), PHP_URL_PATH).'?'.parse_url(verificationLink($user), PHP_URL_QUERY), '/'))
            ->assertOk()->assertJsonPath('data.status', 'verified');

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    });

    it('refuses a tampered signature, a hash of another address and an address changed since the email', function (): void {
        $user = User::factory()->unverified()->create(['email' => 'sara@example.com']);
        $unsigned = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);
        $stale = verificationLink($user);
        $user->forceFill(['email' => 'new@example.com'])->save();

        $this->getJson($unsigned)->assertForbidden();
        $this->getJson(verificationLink($user, 'other@example.com'))->assertForbidden();
        $this->getJson($stale)->assertForbidden();
        $this->get($unsigned)->assertRedirect(rtrim((string) config('app.url'), '/').'/?email_verification=invalid');

        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('verifies the user of the link even when another user is signed in, and signs nobody in', function (): void {
        $owner = User::factory()->unverified()->create();
        $signedIn = User::factory()->unverified()->create();

        $this->actingAs($signedIn)->getJson(verificationLink($owner))->assertOk();

        expect($owner->fresh()->hasVerifiedEmail())->toBeTrue()->and($signedIn->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('resends the verification email to a bearer client', function (): void {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        mobileCall('POST', 'account/email/verification-notification', token: mobileSignIn($user)['access_token'])->assertOk();

        Notification::assertSentTo($user, VerifyEmail::class);
    });
});

describe('password reset link', function (): void {
    it('redirects to the configured page or app link with the token and email encoded', function (): void {
        config(['auth.links.password_reset_url' => 'boxcards://reset-password?token={token}&email={email}']);

        $this->get('/reset-password/abc123?email=sara%2Bshop%40example.com')
            ->assertRedirect('boxcards://reset-password?token=abc123&email=sara%2Bshop%40example.com')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    });

    it('keeps the JSON echo when no landing is configured', function (): void {
        config(['auth.links.password_reset_url' => null]);

        $this->getJson('/reset-password/abc123?email=sara@example.com')->assertOk()
            ->assertJsonPath('data.token', 'abc123')->assertJsonPath('data.email', 'sara@example.com');
    });
});
