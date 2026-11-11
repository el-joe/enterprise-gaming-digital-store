<?php

/**
 * M-A3 (G2): a stateless guest checks out, pays, follows its order and reveals its code with
 * headers only: `X-Cart-Token`, `Idempotency-Key` and the per-order `X-Guest-Order-Token`.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\GuestOrderTokenSource;
use App\Domains\Orders\Models\GuestOrderToken;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Notifications\GuestOrderAccessCode;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

/**
 * @param  array<string, string>  $headers
 */
function mobilePlaceOrder(string $cartToken, string $key = 'mobile-key-0001', array $headers = [], array $overrides = []): TestResponse
{
    return statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('10.00', ['email' => 'guest@example.com', ...$overrides]), ['X-Cart-Token' => $cartToken, 'Idempotency-Key' => $key, ...$headers]);
}

function guestOrderPath(string $publicId, string $action): string
{
    return "api/v1/mobile/checkout/orders/{$publicId}/{$action}";
}

function mobileOtpCode(Order $order): string
{
    Notification::fake();
    statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/send', ['order_public_id' => $order->public_id, 'email' => 'guest@example.com'])->assertOk();

    $code = null;
    Notification::assertSentOnDemand(GuestOrderAccessCode::class, function (GuestOrderAccessCode $notification) use (&$code): bool {
        $code = $notification->code;

        return true;
    });

    return $code;
}

/**
 * The signed delivery link, rewritten to the stateless twin of the access route.
 */
function mobileAccessLink(Order $order): string
{
    return str_replace(url('/api/v1/checkout').'/', '/api/v1/mobile/checkout/', OrderDelivered::link($order));
}

describe('guest place-order', function (): void {
    beforeEach(function (): void {
        // OD-1 (owner decision 4): guest checkout is off by default on the app route too; these
        // tests cover the guest flow, which stays available behind `storefront.guest_checkout`.
        config(['storefront.guest_checkout' => true]);
        fakePayments();
        $this->cartToken = mobileGuestCart(sellableVariant('10'));
    });

    it('places an order with headers only and returns a guest order token stored as a hash', function (): void {
        $response = mobilePlaceOrder($this->cartToken)
            ->assertCreated()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('meta.duplicate', false);

        $order = Order::query()->sole();
        $token = $response->json('data.guest_order_token.token');

        expect($order->user_id)->toBeNull()
            ->and($response->json('data.guest_order_token.order_public_id'))->toBe($order->public_id)
            ->and(GuestOrderToken::query()->sole())
            ->token_hash->toBe(hash('sha256', $token))
            ->source->toBe(GuestOrderTokenSource::Placement)
            ->and(json_encode(DB::table('idempotency_keys')->get()))->not->toContain($token);

        statelessCall('GET', guestOrderPath($order->public_id, 'status'), headers: ['X-Guest-Order-Token' => $token])
            ->assertOk()->assertJsonPath('data.status', 'pending_payment');
        statelessCall('GET', guestOrderPath($order->public_id, 'status'))->assertNotFound();
    });

    it('replays the same key and body without a second order and hands out a token again', function (): void {
        $first = mobilePlaceOrder($this->cartToken)->assertCreated();

        $replay = mobilePlaceOrder($this->cartToken)->assertCreated()->assertHeader('Idempotency-Replayed', 'true');

        expect(Order::query()->count())->toBe(1)
            ->and($replay->json('data.order'))->toEqual($first->json('data.order'))
            ->and($replay->json('data.guest_order_token.token'))->not->toBe($first->json('data.guest_order_token.token'))
            ->and(GuestOrderToken::query()->count())->toBe(2);

        statelessCall('GET', guestOrderPath($replay->json('data.order.public_id'), 'status'), headers: ['X-Guest-Order-Token' => $replay->json('data.guest_order_token.token')])->assertOk();
    });

    it('records the request locale on the order', function (): void {
        mobilePlaceOrder($this->cartToken, headers: ['Accept-Language' => 'ar'])->assertCreated()->assertHeader('Content-Language', 'ar');

        expect(Order::query()->sole()->locale)->toBe('ar');
    });

    it('refuses the same key with a different body', function (): void {
        mobilePlaceOrder($this->cartToken)->assertCreated();

        mobilePlaceOrder($this->cartToken, overrides: ['consents' => ['terms' => true, 'marketing' => true]])
            ->assertConflict()->assertJsonPath('code', 'idempotency_mismatch');
    });

    it('returns no guest token for a signed-in customer, whose orders open by account', function (): void {
        $user = User::factory()->create();
        $access = mobileSignIn($user)['access_token'];
        statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 1], token: $access)->assertCreated();

        $publicId = statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('10.00'), ['Idempotency-Key' => 'customer-key-01'], $access)
            ->assertCreated()
            ->assertJsonMissingPath('data.guest_order_token')
            ->json('data.order.public_id');

        statelessCall('GET', guestOrderPath($publicId, 'status'), token: $access)->assertOk();
        statelessCall('GET', guestOrderPath($publicId, 'status'), token: mobileSignIn(User::factory()->create())['access_token'])->assertNotFound();
    });
});

describe('guest checkout switch (owner decision 4, OD-1)', function (): void {
    it('refuses a guest place-order on the app route with 401 guest_checkout_disabled by default, creating nothing', function (): void {
        fakePayments();
        $cartToken = mobileGuestCart(sellableVariant('10'));

        mobilePlaceOrder($cartToken)->assertUnauthorized()->assertJsonPath('code', 'guest_checkout_disabled');

        expect(Order::query()->exists())->toBeFalse();
    });
});

describe('guest order token scope', function (): void {
    beforeEach(function (): void {
        Queue::fake([FulfilOrder::class]);
        [$this->order] = orderAwaitingPayment(gatewayFor('stripe'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);
        $this->token = app(GuestOrderTokenService::class)->issueForPlacement($this->order->public_id)->token;
    });

    it('verifies a payment server side for the holder of the token', function (): void {
        fakeStripe();

        statelessCall('POST', guestOrderPath($this->order->public_id, 'verify-payment'), headers: ['X-Guest-Order-Token' => $this->token])
            ->assertOk()->assertJsonPath('data.status', 'paid');
    });

    it('refuses a token of another order, an expired token and a customer order', function (): void {
        [$other] = orderAwaitingPayment(gatewayFor('stripe'), providerPaymentId: 'pi_other', orderAttributes: ['user_id' => null]);
        $otherToken = app(GuestOrderTokenService::class)->issueForPlacement($other->public_id)->token;

        foreach (['status', 'verify-payment', 'paypal/capture', 'retry-payment'] as $action) {
            $method = $action === 'status' ? 'GET' : 'POST';
            statelessCall($method, guestOrderPath($this->order->public_id, $action), headers: ['X-Guest-Order-Token' => $otherToken])->assertNotFound();
        }

        $this->travel((int) config('orders.guest_order_token_ttl_hours') + 1)->hours();
        statelessCall('GET', guestOrderPath($this->order->public_id, 'status'), headers: ['X-Guest-Order-Token' => $this->token])->assertNotFound();
        $this->travelBack();

        $this->order->forceFill(['user_id' => User::factory()->create()->id])->save();
        statelessCall('GET', guestOrderPath($this->order->public_id, 'status'), headers: ['X-Guest-Order-Token' => $this->token])->assertNotFound();
    });

    it('prunes expired tokens on schedule', function (): void {
        $this->travel((int) config('orders.guest_order_token_ttl_hours') + 1)->hours();

        $this->artisan('orders:prune-guest-order-tokens')->assertSuccessful();

        expect(GuestOrderToken::query()->count())->toBe(0);
    });

    it('keeps the session list for the browser and ignores the header there', function (): void {
        $this->getJson("/api/v1/checkout/orders/{$this->order->public_id}/status", ['X-Guest-Order-Token' => $this->token])->assertNotFound();

        $this->withSession([OrderStatusReader::SESSION_KEY => [$this->order->public_id]])
            ->getJson("/api/v1/checkout/orders/{$this->order->public_id}/status")->assertOk();
    });
});

describe('stateless guest OTP and reveal', function (): void {
    beforeEach(function (): void {
        [$this->order, $this->unit] = deliveredGuestUnit();
        $this->revealPath = "api/v1/mobile/checkout/guest/orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";
    });

    it('needs the signed link and the email code on the token, as the session does', function (): void {
        $link = statelessCall('GET', mobileAccessLink($this->order))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.otp_required', true);
        $linkToken = $link->json('data.guest_order_token.token');

        statelessCall('GET', guestOrderPath($this->order->public_id, 'status'), headers: ['X-Guest-Order-Token' => $linkToken])->assertNotFound();
        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $linkToken])->assertForbidden()->assertJsonPath('code', 'reveal_denied');

        $verified = statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => mobileOtpCode($this->order)], ['X-Guest-Order-Token' => $linkToken])
            ->assertOk()
            ->assertJsonPath('data.email_verified', true);
        $token = $verified->json('data.guest_order_token.token');

        expect($token)->not->toBe($linkToken)
            ->and(GuestOrderToken::query()->sole())->source->toBe(GuestOrderTokenSource::Otp);

        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $linkToken])->assertForbidden();
        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $token])
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.code', 'GIFT-MOBI-LE00-1234');
        statelessCall('GET', guestOrderPath($this->order->public_id, 'status'), headers: ['X-Guest-Order-Token' => $token])->assertOk();

        expect(DigitalCodeAccess::query()->where('granted', true)->sole()->actor_type->value)->toBe('guest')
            ->and(AuditLog::query()->where('action', 'orders.guest_order_token_issued')->count())->toBe(2);
    });

    it('does not reveal with the email code alone', function (): void {
        $token = statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => mobileOtpCode($this->order)])
            ->assertOk()->json('data.guest_order_token.token');

        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $token])->assertForbidden();

        statelessCall('GET', mobileAccessLink($this->order), headers: ['X-Guest-Order-Token' => $token])
            ->assertOk()->assertJsonPath('data.otp_required', false)->assertJsonPath('data.guest_order_token', null);
        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $token])->assertOk();
    });

    it('refuses a tampered or expired link and a token of another order', function (): void {
        $link = mobileAccessLink($this->order);
        statelessCall('GET', $link.'x')->assertForbidden();

        [$other] = deliveredGuestUnit('GIFT-OTHE-RORD-9999');
        $otherToken = app(GuestOrderTokenService::class)->issueAfterOtp($other, null)->token;
        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $otherToken])->assertForbidden();

        $this->travel((int) config('fulfillment.guest_link_hours') + 1)->hours();
        statelessCall('GET', $link)->assertForbidden();
    });

    it('refuses an expired token with both proofs', function (): void {
        statelessCall('GET', mobileAccessLink($this->order))->assertOk();
        $token = statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => mobileOtpCode($this->order)])->json('data.guest_order_token.token');
        statelessCall('GET', mobileAccessLink($this->order), headers: ['X-Guest-Order-Token' => $token])->assertOk();

        $this->travel((int) config('orders.guest_order_token_ttl_hours') + 1)->hours();

        statelessCall('POST', $this->revealPath, headers: ['X-Guest-Order-Token' => $token])->assertForbidden();
    });

    it('still refuses a wrong code', function (): void {
        mobileOtpCode($this->order);

        statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => '000000'])
            ->assertUnprocessable()->assertJsonPath('code', 'otp_invalid');

        expect(GuestOrderToken::query()->count())->toBe(0);
    });
});

describe('limiters without a session', function (): void {
    it('counts the checkout limiter per cart token, not per request', function (): void {
        $first = str_repeat('a', 64);

        foreach (range(1, 20) as $attempt) {
            statelessCall('GET', guestOrderPath('01JC000000000000000000000Z', 'status'), headers: ['X-Cart-Token' => $first])->assertNotFound();
        }

        statelessCall('GET', guestOrderPath('01JC000000000000000000000Z', 'status'), headers: ['X-Cart-Token' => $first])->assertTooManyRequests()->assertJsonPath('code', 'rate_limited');
        statelessCall('GET', guestOrderPath('01JC000000000000000000000Z', 'status'), headers: ['X-Cart-Token' => str_repeat('b', 64)])->assertNotFound();
    });

    it('counts the checkout limiter per customer for bearer clients', function (): void {
        $access = mobileSignIn(User::factory()->create())['access_token'];

        foreach (range(1, 20) as $attempt) {
            statelessCall('GET', guestOrderPath('01JC000000000000000000000Z', 'status'), token: $access)->assertNotFound();
        }

        statelessCall('GET', guestOrderPath('01JC000000000000000000000Z', 'status'), token: $access)->assertTooManyRequests();
    });
});
