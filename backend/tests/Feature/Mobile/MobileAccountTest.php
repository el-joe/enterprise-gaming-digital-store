<?php

/**
 * M-A3 (G2): the account routes under /api/v1/mobile/account for customer bearer tokens, with
 * recent authentication for reveal stored on the access token.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Enums\DeviceIntegrity;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Support\Models\SupportTicket;

function revealPathFor(OrderItemUnit $unit): string
{
    return "account/orders/{$unit->order->public_id}/units/{$unit->public_id}/reveal";
}

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
    $this->access = mobileSignIn($this->customer)['access_token'];
});

describe('account routes with a bearer token', function (): void {
    it('reads the wallet, reviews and refund requests', function (string $path): void {
        mobileCall('GET', $path, token: $this->access)->assertOk()->assertJsonPath('success', true);
    })->with(['account/wallet', 'account/wallet/transactions', 'account/reviews', 'account/reviews/eligible', 'account/refund-requests']);

    it('opens, reads and answers a ticket and hides other customers\' tickets', function (): void {
        $ticket = mobileCall('POST', 'account/tickets', ['category' => 'order', 'subject' => 'Code missing', 'body' => 'Nothing arrived.'], $this->access)
            ->assertCreated()->json('data.ticket.public_id');

        mobileCall('GET', 'account/tickets', token: $this->access)->assertOk()->assertJsonPath('data.0.public_id', $ticket);
        mobileCall('POST', "account/tickets/{$ticket}/messages", ['body' => 'Any news?'], $this->access)->assertCreated();
        mobileCall('GET', "account/tickets/{$ticket}", token: $this->access)->assertOk()->assertJsonCount(2, 'data.ticket.messages');

        $other = mobileSignIn(User::factory()->create())['access_token'];
        mobileCall('GET', "account/tickets/{$ticket}", token: $other)->assertNotFound();
        expect(SupportTicket::query()->sole()->user_id)->toBe($this->customer->id);
    });

    it('enrols two-factor authentication without a session', function (): void {
        mobileCall('POST', 'account/security/two-factor', ['current_password' => 'wrong-password'], $this->access)->assertUnprocessable();
        $secret = mobileCall('POST', 'account/security/two-factor', ['current_password' => 'password'], $this->access)->assertOk()->json('data.secret');

        mobileCall('POST', 'account/security/two-factor/confirm', ['code' => mobileTotp($secret)], $this->access)
            ->assertOk()->assertJsonCount(8, 'data.recovery_codes');

        expect($this->customer->fresh()->two_factor_confirmed_at)->not->toBeNull();
    });

    it('needs a bearer token: no token, a web session and a bearer token on the web path are refused', function (): void {
        mobileCall('GET', 'account/wallet')->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
        statelessCall('GET', 'api/v1/account/wallet', token: $this->access)->assertUnauthorized();

        $this->actingAs($this->customer)->getJson('/api/v1/mobile/account/wallet')->assertUnauthorized();
    });
});

describe('reveal with token-scoped recent authentication', function (): void {
    beforeEach(function (): void {
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id]);
        $this->unit = deliveredUnitOf($order);
    });

    it('asks for the password, then reveals within the window', function (): void {
        mobileCall('POST', revealPathFor($this->unit), token: $this->access)->assertStatus(423)->assertJsonPath('code', 'recent_auth_required');

        mobileCall('POST', 'account/confirm-password', ['password' => 'wrong-password'], $this->access)->assertUnprocessable();
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $this->access)
            ->assertOk()->assertJsonPath('data.confirmed_for_minutes', 15);

        mobileCall('POST', revealPathFor($this->unit), token: $this->access)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.code', 'GIFT-MOBI-LE00-1234');

    });

    it('keeps the confirmation on the token that confirmed: another device must confirm itself', function (): void {
        $otherDevice = mobileSignIn($this->customer, 'Tablet')['access_token'];

        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $this->access)->assertOk();

        mobileCall('POST', revealPathFor($this->unit), token: $otherDevice)->assertStatus(423);
        mobileCall('POST', revealPathFor($this->unit), token: $this->access)->assertOk();
    });

    it('carries the confirmation over a refresh of the same sign-in, without extending the window', function (): void {
        $pair = mobileSignIn($this->customer, 'Phone 2');
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $pair['access_token'])->assertOk();

        $this->travel(10)->minutes();
        $refreshed = mobileCall('POST', 'auth/refresh', ['refresh_token' => $pair['refresh_token']])->assertOk()->json('data.tokens.access_token');
        mobileCall('POST', revealPathFor($this->unit), token: $refreshed)->assertOk();

        $this->travel(6)->minutes();
        mobileCall('POST', revealPathFor($this->unit), token: $refreshed)->assertStatus(423);
    });

    it('stores the X-Device-Integrity verdict in the reveal audit and never blocks on it (owner decision 5)', function (?string $header, ?DeviceIntegrity $stored): void {
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $this->access)->assertOk();
        $headers = $header === null ? [] : [DeviceIntegrity::HEADER => $header];

        statelessCall('POST', 'api/v1/mobile/'.revealPathFor($this->unit), headers: $headers, token: $this->access)->assertOk();

        $access = DigitalCodeAccess::query()->sole();
        $audit = AuditLog::query()->where('action', 'digital_codes.revealed')->sole();

        expect($access->granted)->toBeTrue()
            ->and($access->device_integrity)->toBe($stored)
            ->and($audit->after['device_integrity'])->toBe($stored?->value);
    })->with([
        'ok' => ['ok', DeviceIntegrity::Ok],
        'compromised (warn, do not block)' => ['compromised', DeviceIntegrity::Compromised],
        'unknown' => ['unknown', DeviceIntegrity::Unknown],
        'malformed is kept as unknown' => ['rooted!!', DeviceIntegrity::Unknown],
        'absent' => [null, null],
    ]);

    it('records the verdict on a refused reveal too', function (): void {
        statelessCall('POST', 'api/v1/mobile/'.revealPathFor($this->unit), headers: [DeviceIntegrity::HEADER => 'compromised'], token: $this->access)->assertStatus(423);

        expect(DigitalCodeAccess::query()->sole()->device_integrity)->toBe(DeviceIntegrity::Compromised);
    });

    it('still refuses another customer\'s unit with 404', function (): void {
        $other = mobileSignIn(User::factory()->create(['email_verified_at' => now()]))['access_token'];
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $other)->assertOk();

        mobileCall('POST', revealPathFor($this->unit), token: $other)->assertNotFound();
    });
});

describe('who may use the customer routes', function (): void {
    it('refuses a customer token without the customer ability on account and checkout routes', function (array $abilities): void {
        $token = $this->customer->createToken('Pixel 9', $abilities, now()->addMinutes(15))->plainTextToken;

        mobileCall('GET', 'account/wallet', token: $token)->assertUnauthorized();
        mobileCall('GET', 'storefront/cart', token: $token)->assertUnauthorized();
        mobileCall('POST', 'checkout/validate', ['country' => 'US'], $token)->assertUnauthorized();
    })->with(['no ability' => [[]], 'wildcard' => [['*']], 'other ability' => [['mcp:read']]]);

    it('never treats a bad bearer token as a guest', function (): void {
        mobileCall('GET', 'storefront/cart', token: 'not-a-token')->assertUnauthorized();
    });

    it('refuses staff tokens and service-account tokens even with the customer ability', function (): void {
        $staff = User::factory()->staff()->withTwoFactor()->create()->createToken('Pixel 9', ['customer'], now()->addMinutes(15))->plainTextToken;
        $service = ServiceAccount::factory()->create(['kind' => ServiceAccountKind::Mcp])->createToken('service', ['customer'])->plainTextToken;

        foreach ([$staff, $service] as $token) {
            mobileCall('GET', 'account/wallet', token: $token)->assertUnauthorized();
            mobileCall('GET', 'storefront/cart', token: $token)->assertUnauthorized();
        }
    });

    it('refuses a staff web session on the mobile account routes', function (): void {
        actingAsVerifiedStaff();

        $this->getJson('/api/v1/mobile/account/wallet')->assertUnauthorized();
    });

    it('answers 403 to a suspended account on account and checkout routes', function (): void {
        $this->customer->forceFill(['status' => UserStatus::Suspended])->save();

        mobileCall('GET', 'account/wallet', token: $this->access)->assertForbidden();
        mobileCall('GET', 'storefront/cart', token: $this->access)->assertForbidden();
    });
});
