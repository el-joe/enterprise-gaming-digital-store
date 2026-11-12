<?php

/**
 * M12 bag, checkout, payment return, order result and guest order access pages. Stripe is mocked
 * with Http::fake (awaiting sandbox verification). Web checkout requires sign-in by default
 * (owner decision 4, `storefront.guest_checkout`).
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Notifications\OrderDelivered;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    storefrontWithoutBuild();
    Queue::fake([FulfilOrder::class]);
});

describe('bag page', function (): void {
    it('shows the empty state', function (): void {
        $this->get('/cart')->assertOk()->assertSee('Your bag is empty')->assertSee('noindex,nofollow');
        $this->get('/ar/cart')->assertOk()->assertSee('حقيبتك فارغة');
    });

    it('shows the lines and server totals, and serves the cart fragment alone', function (): void {
        $user = User::factory()->create();
        $variant = storefrontVariant(storefrontProduct('Steam Wallet $20', '20'));
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 2])->assertCreated();

        $page = $this->get('/cart')->assertOk()
            ->assertSee('Steam Wallet $20')
            ->assertSee(storefrontPrice('40.00'))
            ->assertSee('data-cart-quantity="3"', false)
            ->assertSee('Subtotal (2 items)')
            ->assertSee('data-bag-count aria-hidden="true">2<', false);
        assertCspSafeHtml($page);

        $fragment = $this->get('/cart', ['X-Fragment' => 'cart'])->assertOk();
        expect($fragment->getContent())->not->toContain('<html')->toContain(storefrontPrice('40.00'));
    });
});

describe('bag badge after a change on the bag page (M19 defect D1)', function (): void {
    it('returns the cart lines on quantity change and remove, and the bag module updates the header badge from them', function (): void {
        $variant = storefrontVariant(storefrontProduct('Steam Wallet $20', '20'));
        $this->actingAs(User::factory()->create());
        $line = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated()->json('data.cart.items.0.id');

        $this->patchJson("/api/v1/storefront/cart/items/{$line}", ['quantity' => 3])->assertOk()->assertJsonPath('data.cart.items.0.quantity', 3);
        $this->deleteJson("/api/v1/storefront/cart/items/{$line}")->assertOk()->assertJsonCount(0, 'data.cart.items');

        expect(file_get_contents(resource_path('js/modules/cart.js')))
            ->toContain("document.querySelectorAll('[data-bag-count]')")
            ->toContain('updateBagCount(result.body?.data?.cart)');
    });
});

describe('checkout access (owner decision 4)', function (): void {
    it('sends a guest to sign in and back to checkout while guest checkout is off', function (): void {
        $variant = storefrontVariant(storefrontProduct());
        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated();

        $this->get('/checkout')->assertRedirect(url('/login'))->assertSessionHas('url.intended', url('/checkout'));
        $this->get('/login')->assertOk()->assertSee('data-redirect="'.url('/checkout').'"', false);
        $this->get('/ar/checkout')->assertRedirect(url('/ar/login'))->assertSessionHas('url.intended', url('/ar/checkout'));
    });

    it('shows the guest email field when guest checkout is switched on', function (): void {
        config(['storefront.guest_checkout' => true]);
        $variant = storefrontVariant(storefrontProduct());
        $token = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated()->getCookie('cart_token')->getValue();

        $this->withCookie('cart_token', $token)->get('/checkout')->assertOk()->assertSee('Email for your codes')->assertSee('name="email"', false);
    });

    it('hides the guest email field from a signed-in customer and sends an empty bag back to the bag page', function (): void {
        $user = User::factory()->create(['email' => 'sara@example.com']);
        $this->actingAs($user)->get('/checkout')->assertRedirect(url('/cart'));

        $variant = storefrontVariant(storefrontProduct());
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated();

        $response = $this->actingAs($user)->get('/checkout')->assertOk()
            ->assertSee('Signed in as')
            ->assertSee('sara@example.com')
            ->assertDontSee('name="email"', false)
            ->assertSee('What you will be charged')
            ->assertSee('Secure checkout');
        assertCspSafeHtml($response);
    });

    it('refuses a browser guest at place-order while the flag is off, and accepts one when it is on', function (): void {
        fakePayments();
        $variant = storefrontVariant(storefrontProduct('Card', '5'));
        $token = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated()->getCookie('cart_token')->getValue();
        $this->withCredentials()->withCookie('cart_token', $token);
        $body = placeOrderBody('5.00', ['email' => 'buyer@example.com']);

        $this->postJson('/api/v1/checkout/place-order', $body, ['Idempotency-Key' => 'guest-off-0001'])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'guest_checkout_disabled')
            ->assertJsonPath('success', false);
        expect(Order::query()->count())->toBe(0);

        config(['storefront.guest_checkout' => true]);

        $this->postJson('/api/v1/checkout/place-order', $body, ['Idempotency-Key' => 'guest-on-00001'])->assertCreated();
        expect(Order::query()->sole()->guest_email)->toBe('buyer@example.com');
    });

    it('translates the refusal for Arabic shoppers', function (): void {
        CountryFactory::ensure('US');
        $this->postJson('/api/v1/checkout/place-order', placeOrderBody('5.00', ['email' => 'buyer@example.com']), ['Idempotency-Key' => 'guest-off-0002', 'Accept-Language' => 'ar'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'يُرجى تسجيل الدخول أو إنشاء حساب لإتمام الطلب.');
    });
});

describe('customer checkout end to end (mocked Stripe)', function (): void {
    beforeEach(function (): void {
        foreach (['US', 'EG', 'SA'] as $country) {
            CountryFactory::ensure($country);
        }

        CurrencyFactory::ensure('USD');
        CurrencyFactory::ensure('EGP');
    });

    it('goes from product page to a paid order page showing the display and payment amounts', function (): void {
        $this->seed(PaymentGatewaySeeder::class);
        gatewayFor('stripe', ['USD'], keepRules: true);
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_web', 'requires_payment_method', 4000))]);
        $user = User::factory()->create(['email' => 'sara@example.com']);
        $product = storefrontProduct('Steam Wallet $20', '20');
        $variant = storefrontVariant($product);
        $this->actingAs($user);

        $this->get('/products/'.$product->slug)->assertOk()->assertSee('data-variant-option="'.$variant->public_id.'"', false);
        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 2])->assertCreated();
        $this->get('/cart')->assertOk()->assertSee(storefrontPrice('40.00'));
        $this->get('/checkout')->assertOk()->assertSee('data-options-url', false);

        $option = collect($this->getJson('/api/v1/checkout/payment-options?country=US')->assertOk()->json('data.options'))->firstWhere('gateway', 'stripe');
        expect($option['display_amount']['amount'])->toBe('40.00')
            ->and($option['payment_amount']['amount'])->toBe('40.00')
            ->and($option['payment_currency'])->toBe('USD');

        $placed = $this->postJson('/api/v1/checkout/place-order', [
            'country' => 'US',
            'gateway' => 'stripe',
            'currency' => $option['display_currency'],
            'expected_total' => $option['display_amount']['amount'],
            'consents' => ['terms' => true],
            'client' => 'web',
        ], ['Idempotency-Key' => 'web-e2e-0000001'])->assertCreated()
            ->assertJsonPath('data.next.type', 'stripe')
            ->assertJsonPath('data.next.client_secret', 'pi_web_secret_fixture');
        $orderId = $placed->json('data.order.public_id');

        $this->get("/checkout/orders/{$orderId}/return")->assertOk()
            ->assertSee('Confirming your payment')
            ->assertSee('data-verify-url="'.url("/api/v1/checkout/orders/{$orderId}/verify-payment").'"', false)
            ->assertSee('data-gateway="stripe"', false);

        resetHttpFakes();
        fakeStripe(Http::response(stripeIntent('pi_web', 'succeeded', 4000)));
        $this->postJson("/api/v1/checkout/orders/{$orderId}/verify-payment")->assertOk()->assertJsonPath('data.payment_status', 'paid');

        $page = $this->get("/orders/{$orderId}")->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Payment received. Your order is on its way.')
            ->assertSee('Display total')
            ->assertSee('Actual payment amount')
            ->assertSee(storefrontPrice('40.00'))
            ->assertSee('sara@example.com');
        assertCspSafeHtml($page);
        expect(Order::query()->sole()->status)->toBe(OrderStatus::Paid);
    });

    it('offers to retry an unpaid order', function (): void {
        $this->seed(PaymentGatewaySeeder::class);
        gatewayFor('stripe', ['USD'], keepRules: true);
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_web', 'requires_payment_method', 2000))]);
        $user = User::factory()->create();
        $variant = storefrontVariant(storefrontProduct('Card', '20'));
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated();
        $orderId = $this->postJson('/api/v1/checkout/place-order', placeOrderBody('20.00', ['gateway' => 'stripe']), ['Idempotency-Key' => 'web-retry-00001'])->assertCreated()->json('data.order.public_id');

        $this->get("/ar/orders/{$orderId}")->assertOk()
            ->assertSee('بانتظار الدفع.')
            ->assertSee('data-retry-url="'.url("/api/v1/checkout/orders/{$orderId}/retry-payment").'"', false);
    });
});

describe('order and guest access pages', function (): void {
    it('is 404 for anyone who neither owns nor opened the order', function (): void {
        $order = paidOrderFor(storefrontVariant(storefrontProduct()));

        $this->get("/orders/{$order->public_id}")->assertNotFound();
        $this->actingAs(User::factory()->create())->get("/orders/{$order->public_id}")->assertNotFound();
    });

    it('shows an unknown-order message on the payment return page without revealing anything', function (): void {
        $order = paidOrderFor(storefrontVariant(storefrontProduct()));

        $this->get("/checkout/orders/{$order->public_id}/return")->assertOk()->assertSee('We could not open this order here')->assertDontSee($order->order_number);
    });

    it('opens the guest order page from the email link, asks for the one-time code, then shows the order', function (): void {
        $order = paidOrderFor(storefrontVariant(storefrontProduct('Steam Wallet $20', '20')), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);

        $this->get(OrderDelivered::link($order))->assertRedirect(url("/orders/{$order->public_id}"));

        $this->get("/orders/{$order->public_id}")->assertOk()
            ->assertSee('Open your order')
            ->assertSee('data-send-url="'.url('/api/v1/checkout/guest/otp/send').'"', false)
            ->assertDontSee($order->order_number);

        $this->withSession([
            'guest.order_links' => [$order->public_id],
            'guest.verified_orders' => [$order->public_id],
            'checkout.orders' => [$order->public_id],
        ])->get("/orders/{$order->public_id}")->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('data-reveal-url="'.url("/api/v1/checkout/guest/orders/{$order->public_id}/units/__unit__/reveal").'"', false);
    });

    it('keeps the JSON answer of the email link for API clients', function (): void {
        $order = paidOrderFor(storefrontVariant(storefrontProduct()), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);

        $this->getJson(OrderDelivered::link($order))->assertOk()->assertJsonPath('data.otp_required', true);
    });
});
