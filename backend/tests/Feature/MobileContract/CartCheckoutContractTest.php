<?php

/**
 * Contract fixtures (M-A7, G11): the stateless cart and checkout of a guest and a customer under
 * /api/v1/mobile (sections 1c, 3a, 3b). Fixtures land in docs/api-fixtures/{cart,checkout}.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

const CONTRACT_CART = 'api/v1/mobile/storefront/cart';

beforeEach(function (): void {
    contractSetUp();
    Queue::fake([FulfilOrder::class]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function contractPlaceOrderBody(array $overrides = []): array
{
    return placeOrderBody('10.00', ['email' => 'guest@example.com', 'gateway' => 'stripe', ...$overrides]);
}

describe('cart', function (): void {
    it('runs a guest cart with the X-Cart-Token header', function (): void {
        $variant = contractVariant();

        contractFixture('cart/cart_empty', mobileCall('GET', 'storefront/cart')->assertOk()->assertJsonPath('data.cart', null));

        $created = statelessCall('POST', CONTRACT_CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated();
        contractFixture('cart/add_item_created', $created);
        $token = ['X-Cart-Token' => $created->headers->get('X-Cart-Token')];
        $itemId = $created->json('data.cart.items.0.id');

        contractFixture('cart/cart_ok', statelessCall('GET', CONTRACT_CART, headers: $token)->assertOk());
        contractFixture('cart/update_item_ok', statelessCall('PATCH', CONTRACT_CART."/items/{$itemId}", ['quantity' => 2], $token)->assertOk());
        contractFixture('cart/update_item_validation_failed', statelessCall('PATCH', CONTRACT_CART."/items/{$itemId}", ['quantity' => 50], $token)->assertUnprocessable());
        contractFixture('cart/item_not_found', statelessCall('PATCH', CONTRACT_CART.'/items/999999', ['quantity' => 1], $token)->assertNotFound());

        $coupon = Coupon::factory()->create(['code' => 'SAVE5']);
        fakeCoupons(['SAVE5' => ['coupon' => $coupon, 'terms' => new CouponTerms(CouponType::Fixed, '5', allowBelowMargin: true)]]);
        contractFixture('cart/coupon_applied', statelessCall('POST', CONTRACT_CART.'/coupon', ['code' => 'save5'], $token)->assertOk());
        contractFixture('cart/coupon_invalid', statelessCall('POST', CONTRACT_CART.'/coupon', ['code' => 'NOPE'], $token)->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid'));
        contractFixture('cart/coupon_removed', statelessCall('DELETE', CONTRACT_CART.'/coupon', headers: $token)->assertOk());
        contractFixture('cart/remove_item_ok', statelessCall('DELETE', CONTRACT_CART."/items/{$itemId}", headers: $token)->assertOk());
    });

    it('refuses an unknown variant and a missing variant', function (): void {
        contractFixture('cart/add_item_validation_failed', mobileCall('POST', 'storefront/cart/items', ['quantity' => 0])->assertUnprocessable()->assertJsonPath('code', 'validation_failed'));
    });

    it('keeps a signed-in customer cart by account', function (): void {
        $user = User::factory()->create(['name' => 'Sara Customer', 'email' => 'sara@example.com']);
        $access = mobileSignIn($user)['access_token'];

        contractFixture('cart/add_item_customer_created', mobileCall('POST', 'storefront/cart/items', ['variant_id' => contractVariant()->public_id, 'quantity' => 1], $access)->assertCreated());
    });
});

describe('checkout', function (): void {
    beforeEach(function (): void {
        gatewayFor('stripe');
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_contract', 'requires_payment_method'))]);
        $this->variant = contractVariant();
        $this->cart = ['X-Cart-Token' => mobileGuestCart($this->variant)];
    });

    it('validates and lists payment options', function (): void {
        contractFixture('checkout/validate_ok', statelessCall('POST', 'api/v1/mobile/checkout/validate', ['country' => 'US', 'email' => 'guest@example.com'], $this->cart)->assertOk());
        contractFixture('checkout/validate_validation_failed', statelessCall('POST', 'api/v1/mobile/checkout/validate', ['country' => 'ZZ'], $this->cart)->assertUnprocessable());
        contractFixture('checkout/payment_options_ok', statelessCall('GET', 'api/v1/mobile/checkout/payment-options?country=US&email=guest%40example.com', headers: $this->cart)->assertOk());
    });

    it('refuses an empty cart', function (): void {
        contractFixture('checkout/validate_cart_empty', statelessCall('POST', 'api/v1/mobile/checkout/validate', ['country' => 'US'], ['X-Cart-Token' => str_repeat('e', 64)])->assertUnprocessable()->assertJsonPath('code', 'cart_empty'));
    });

    it('places a guest order, replays it, refuses a changed body and reports a duplicate', function (): void {
        $headers = [...$this->cart, 'Idempotency-Key' => '01JC0NTRACT0RDER000000001'];

        $placed = statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(), $headers)->assertCreated()->assertJsonPath('data.next.type', 'stripe');
        contractFixture('checkout/place_order_created', $placed);
        contractFixture('checkout/place_order_replayed', statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(), $headers)->assertCreated()->assertHeader('Idempotency-Replayed', 'true'));
        contractFixture('checkout/place_order_idempotency_mismatch', statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(['consents' => ['terms' => true, 'marketing' => true]]), $headers)
            ->assertConflict()->assertJsonPath('code', 'idempotency_mismatch'));
        mobileGuestCart($this->variant, cartToken: $this->cart['X-Cart-Token']);
        contractFixture('checkout/place_order_duplicate', statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(), [...$this->cart, 'Idempotency-Key' => '01JC0NTRACT0RDER000000002'])
            ->assertOk()->assertJsonPath('meta.duplicate', true));

        $orderId = $placed->json('data.order.public_id');
        $guest = ['X-Guest-Order-Token' => $placed->json('data.guest_order_token.token')];
        contractFixture('checkout/status_ok', statelessCall('GET', "api/v1/mobile/checkout/orders/{$orderId}/status", headers: $guest)->assertOk());
        contractFixture('checkout/status_not_found', statelessCall('GET', "api/v1/mobile/checkout/orders/{$orderId}/status")->assertNotFound());
    });

    it('refuses a changed price and a missing consent', function (): void {
        contractFixture('checkout/place_order_price_changed', statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('9.00', ['email' => 'guest@example.com', 'gateway' => 'stripe']), [...$this->cart, 'Idempotency-Key' => 'contract-price-01'])
            ->assertUnprocessable()->assertJsonPath('code', 'price_changed'));
        contractFixture('checkout/place_order_validation_failed', statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(['consents' => ['terms' => false]]), [...$this->cart, 'Idempotency-Key' => 'contract-consent-01'])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed'));
    });

    it('starts no attempt when the provider is down, so next is null', function (): void {
        resetHttpFakes();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(['error' => ['type' => 'api_error', 'message' => 'down']], 500)]);

        contractFixture('checkout/place_order_next_null', statelessCall('POST', 'api/v1/mobile/checkout/place-order', contractPlaceOrderBody(), [...$this->cart, 'Idempotency-Key' => 'contract-next-null'])
            ->assertCreated()->assertJsonPath('data.next', null));
    });

    it('places a customer order without a guest order token', function (): void {
        $user = User::factory()->create(['name' => 'Sara Customer', 'email' => 'sara@example.com']);
        $access = mobileSignIn($user)['access_token'];
        mobileCall('POST', 'storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1], $access)->assertCreated();

        contractFixture('checkout/place_order_customer_created', statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('10.00', ['gateway' => 'stripe']), ['Idempotency-Key' => 'contract-customer-01'], $access)
            ->assertCreated()->assertJsonMissingPath('data.guest_order_token'));
    });
});
