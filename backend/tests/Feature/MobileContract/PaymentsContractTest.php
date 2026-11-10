<?php

/**
 * Contract fixtures (M-A7, G11): every `next` shape, verify-payment, PayPal capture and
 * retry-payment for a stateless guest, plus guest OTP, the signed link, the guest order detail and
 * the guest reveal (sections 1c, 1e, 3a, 3b, 4a, 4c). The reveal fixtures carry the canary code
 * CONTRACT_CANARY_CODE only. Fixtures land in docs/api-fixtures/{payments,guest_orders,code_reveal}.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Notifications\GuestOrderAccessCode;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Payments\Models\Payment;
use App\Domains\Shared\Models\Country;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    contractSetUp();
    Queue::fake([FulfilOrder::class]);
});

/**
 * A pending guest order on `$gateway` with its fixture attempt removed, and a guest order token.
 *
 * @return array{0: Order, 1: array<string, string>}
 */
function contractGuestOrder(string $gateway, string $amount = '20.00', string $currency = 'USD', string $rate = '1', array $attributes = []): array
{
    [$order] = orderAwaitingPayment(gatewayFor($gateway, [$currency]), $amount, $currency, 'pi_contract', orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com', 'order_number' => 'BX-3001', ...$attributes], rate: $rate);
    Payment::query()->where('order_id', $order->id)->delete();

    return [$order, ['X-Guest-Order-Token' => app(GuestOrderTokenService::class)->issueForPlacement($order->public_id)->token]];
}

function contractOrderPath(Order $order, string $action): string
{
    return "api/v1/mobile/checkout/orders/{$order->public_id}/{$action}";
}

describe('next shapes from retry-payment', function (): void {
    it('returns a Stripe client secret with the app return URL', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_contract', 'requires_payment_method'))]);
        [$order, $guest] = contractGuestOrder('stripe');

        contractFixture('payments/retry_payment_stripe', statelessCall('POST', contractOrderPath($order, 'retry-payment'), headers: $guest)->assertCreated()->assertJsonPath('data.next.type', 'stripe'));
    });

    it('returns a PayPal approve URL, then captures', function (): void {
        fakePayPal();
        [$order, $guest] = contractGuestOrder('paypal');

        contractFixture('payments/retry_payment_paypal', statelessCall('POST', contractOrderPath($order, 'retry-payment'), headers: $guest)->assertCreated()->assertJsonPath('data.next.type', 'paypal'));
        contractFixture('payments/paypal_capture_ok', statelessCall('POST', contractOrderPath($order, 'paypal/capture'), headers: $guest)->assertOk()->assertJsonPath('data.payment_status', 'paid'));
        contractFixture('payments/retry_payment_not_payable', statelessCall('POST', contractOrderPath($order, 'retry-payment'), headers: $guest)->assertConflict()->assertJsonPath('code', 'order_not_payable'));
    });

    it('returns a Paymob redirect URL', function (): void {
        givenRate('EGP', '50');
        Country::query()->firstOrCreate(['code' => 'EG'], ['name' => 'Egypt', 'is_active' => true]);
        fakePaymob();
        [$order, $guest] = contractGuestOrder('paymob', '1000.00', 'EGP', '50', ['country_code' => 'EG']);

        contractFixture('payments/retry_payment_redirect', statelessCall('POST', contractOrderPath($order, 'retry-payment'), headers: $guest)->assertCreated()->assertJsonPath('data.next.type', 'redirect'));
    });

    it('returns the wallet shape when the balance paid at placement', function (): void {
        CountryFactory::ensure('US');
        walletGateway();
        $user = User::factory()->create(['name' => 'Sara Customer', 'email' => 'sara@example.com']);
        fundWallet($user, '50', 'contract:wallet');
        $access = mobileSignIn($user)['access_token'];
        mobileCall('POST', 'storefront/cart/items', ['variant_id' => contractVariant()->public_id, 'quantity' => 1], $access)->assertCreated();

        contractFixture('payments/place_order_wallet', statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('10.00', ['gateway' => 'wallet']), ['Idempotency-Key' => 'contract-wallet-01'], $access)
            ->assertCreated()->assertJsonPath('data.next.type', 'wallet'));
    });
});

describe('verification', function (): void {
    it('verifies a Stripe payment server side and refuses capture on a non-PayPal order', function (): void {
        [$order] = orderAwaitingPayment(gatewayFor('stripe'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);
        $guest = ['X-Guest-Order-Token' => app(GuestOrderTokenService::class)->issueForPlacement($order->public_id)->token];
        fakeStripe(Http::response(stripeIntent('pi_fixture', 'processing')));

        contractFixture('payments/verify_payment_processing', statelessCall('POST', contractOrderPath($order, 'verify-payment'), headers: $guest)->assertOk());
        contractFixture('payments/capture_not_supported', statelessCall('POST', contractOrderPath($order, 'paypal/capture'), headers: $guest)->assertUnprocessable()->assertJsonPath('code', 'capability_not_supported'));

        resetHttpFakes();
        fakeStripe();
        contractFixture('payments/verify_payment_paid', statelessCall('POST', contractOrderPath($order, 'verify-payment'), headers: $guest)->assertOk()->assertJsonPath('data.payment_status', 'paid'));
        contractFixture('payments/status_paid', statelessCall('GET', contractOrderPath($order, 'status'), headers: $guest)->assertOk());
        contractFixture('payments/verify_payment_not_found', statelessCall('POST', contractOrderPath($order, 'verify-payment'))->assertNotFound());
    });
});

describe('guest order access and reveal', function (): void {
    beforeEach(function (): void {
        $this->order = contractDecorateOrder(paidOrderFor(contractVariant(), unitPrice: '10', orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com', 'guest_email_verified_at' => now()]), 'BX-2001');
        $this->unit = contractCanaryUnit($this->order);
        $this->revealPath = "api/v1/mobile/checkout/guest/orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";
    });

    it('opens the order with the signed link and the email code, then reveals the canary', function (): void {
        $link = str_replace(url('/api/v1/checkout').'/', '/api/v1/mobile/checkout/', OrderDelivered::link($this->order));
        $opened = statelessCall('GET', $link)->assertOk();
        contractFixture('guest_orders/access_link_ok', $opened);
        $linkToken = ['X-Guest-Order-Token' => $opened->json('data.guest_order_token.token')];

        contractFixture('code_reveal/guest_reveal_denied', statelessCall('POST', $this->revealPath, headers: $linkToken)->assertForbidden()->assertJsonPath('code', 'reveal_denied'));

        Notification::fake();
        contractFixture('guest_orders/otp_send_ok', statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/send', ['order_public_id' => $this->order->public_id, 'email' => 'guest@example.com'])->assertOk());
        $code = null;
        Notification::assertSentOnDemand(GuestOrderAccessCode::class, function (GuestOrderAccessCode $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        contractFixture('guest_orders/otp_invalid', statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => $code === '000000' ? '111111' : '000000'], $linkToken)
            ->assertUnprocessable()->assertJsonPath('code', 'otp_invalid'));
        $verified = statelessCall('POST', 'api/v1/mobile/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => $code], $linkToken)->assertOk();
        contractFixture('guest_orders/otp_verify_ok', $verified);
        $token = ['X-Guest-Order-Token' => $verified->json('data.guest_order_token.token')];

        contractFixture('guest_orders/order_detail', statelessCall('GET', "api/v1/mobile/checkout/guest/orders/{$this->order->public_id}", headers: $token)->assertOk());
        contractFixture('guest_orders/order_detail_not_found', statelessCall('GET', "api/v1/mobile/checkout/guest/orders/{$this->order->public_id}")->assertNotFound());
        contractFixture('code_reveal/guest_reveal_ok', statelessCall('POST', $this->revealPath, headers: $token)->assertOk()->assertJsonPath('data.code', CONTRACT_CANARY_CODE));
    });
});
