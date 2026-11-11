<?php

/**
 * Payment eligibility (architecture 12), payment currency resolution (11.2), payment-options and
 * place-order with the real resolver, and GeoIP. Providers mocked; awaiting sandbox verification.
 */

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Payments\Services\HeaderGeoIpResolver;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\ValueObjects\Money;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    CountryFactory::ensure('US');
    CountryFactory::ensure('EG');
    CountryFactory::ensure('SA');
    CurrencyFactory::ensure('USD');
    CurrencyFactory::ensure('EGP');
    $this->seed(PaymentGatewaySeeder::class);
    $this->user = User::factory()->create();
    $this->variant = sellableVariant('10');
});

function addToPaymentCart(User $user, ProductVariant $variant, int $quantity = 1): void
{
    test()->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => $quantity])->assertCreated();
}

/**
 * @param  array<string, mixed>  $body
 */
function placePaymentOrder(User $user, array $body, string $key = 'payment-order-key-1'): TestResponse
{
    return test()->actingAs($user)->postJson('/api/v1/checkout/place-order', $body, ['Idempotency-Key' => $key]);
}

function useHeaderGeoIp(): void
{
    config(['payments.geoip.driver' => 'header']);
    app()->forgetInstance(GeoIpResolver::class);
}

function optionsFor(string $country, ?string $ipCountry = null, ?string $displayCurrency = null): TestResponse
{
    $request = test()->actingAs(test()->user);

    if ($displayCurrency !== null) {
        $request = $request->withUnencryptedCookie('currency', $displayCurrency)->withCredentials();
    }

    return $request->getJson('/api/v1/checkout/payment-options?country='.$country, $ipCountry === null ? [] : ['CF-IPCountry' => $ipCountry]);
}

/**
 * @return list<string>
 */
function offeredGateways(TestResponse $response): array
{
    return collect($response->assertOk()->json('data.options'))->pluck('gateway')->all();
}

describe('default deny', function (): void {
    it('offers nothing while the seeded gateways are inactive', function (): void {
        addToPaymentCart($this->user, $this->variant);

        expect(offeredGateways(optionsFor('US')))->toBe([]);
        placePaymentOrder($this->user, placeOrderBody('10.00', ['gateway' => 'stripe']))->assertUnprocessable()->assertJsonPath('code', 'gateway_not_eligible');
        expect(Order::query()->exists())->toBeFalse();
    });

    it('refuses an active gateway that has no matching allow rule', function (): void {
        gatewayFor('stripe', rules: []);
        addToPaymentCart($this->user, $this->variant);

        expect(offeredGateways(optionsFor('US')))->toBe([]);
        placePaymentOrder($this->user, placeOrderBody('10.00', ['gateway' => 'stripe']))->assertJsonPath('code', 'gateway_not_eligible');
    });

    it('refuses a gateway without credentials for its mode, without a merchant currency, or excluded for a product', function (Closure $break): void {
        $gateway = gatewayFor('stripe', keepRules: true);
        $break($gateway, $this->variant);
        addToPaymentCart($this->user, $this->variant);

        expect(offeredGateways(optionsFor('US')))->toBe([]);
    })->with([
        'no credentials' => [fn ($gateway) => PaymentGatewayCredential::query()->where('payment_gateway_id', $gateway->id)->delete()],
        'credentials for the other mode only' => [fn ($gateway) => $gateway->forceFill(['mode' => EnvironmentMode::Production])->save()],
        'currency disabled for the merchant' => [fn ($gateway) => PaymentGatewayCurrency::query()->where('payment_gateway_id', $gateway->id)->update(['is_enabled' => false])],
        'amount above the currency maximum' => [fn ($gateway) => PaymentGatewayCurrency::query()->where('payment_gateway_id', $gateway->id)->update(['max_amount' => '5'])],
        'product excluded' => [fn ($gateway, $variant) => $gateway->excludedProducts()->attach($variant->product_id)],
    ]);

    it('never offers the wallet to a customer without a wallet balance', function (): void {
        gatewayFor('wallet', keepRules: true);
        addToPaymentCart($this->user, $this->variant);

        expect(offeredGateways(optionsFor('US')))->toBe([]);
    });
});

describe('seeded country and currency rules', function (): void {
    beforeEach(function (): void {
        givenRate('EGP', '50');
        givenRate('SAR', '3.75');
        givenRate('AED', '3.6725');
        gatewayFor('stripe', ['USD'], keepRules: true);
        gatewayFor('paypal', ['USD'], keepRules: true);
        gatewayFor('paymob', ['EGP'], keepRules: true);
        useHeaderGeoIp();
    });

    it('offers Paymob only to a customer declared and located in Egypt paying in EGP', function (): void {
        addToPaymentCart($this->user, $this->variant);

        $options = optionsFor('EG', 'EG', 'EGP')->json('data.options');

        expect(collect($options)->pluck('gateway')->all())->toBe(['paymob'])
            ->and($options[0]['payment_currency'])->toBe('EGP')
            ->and($options[0]['payment_amount']['amount'])->toBe('500.00')
            ->and($options[0]['display_amount']['amount'])->toBe('500.00')
            ->and($options[0]['notice'])->toBeNull();
    });

    it('does not offer Paymob outside Egypt, from a non-Egyptian or unknown IP, or for a non-EGP display currency', function (string $country, ?string $ip, string $currency): void {
        addToPaymentCart($this->user, $this->variant);

        expect(offeredGateways(optionsFor($country, $ip, $currency)))->not->toContain('paymob');
    })->with([
        'declared US' => ['US', 'EG', 'EGP'],
        'IP in the US' => ['EG', 'US', 'EGP'],
        'IP unknown' => ['EG', null, 'EGP'],
        'IP is Tor' => ['EG', 'T1', 'EGP'],
    ]);

    // OD-1 (owner decision 1): Paymob always charges EGP and is offered for any display currency.
    // Before OD-1 a USD display refused Paymob (`charge_display_currency_only`).
    it('offers Paymob for any display currency in Egypt and always charges EGP', function (string $display, string $displayAmount): void {
        addToPaymentCart($this->user, $this->variant);

        $paymob = collect(optionsFor('EG', 'EG', $display)->json('data.options'))->firstWhere('gateway', 'paymob');

        expect($paymob)->not->toBeNull()
            ->and($paymob['payment_currency'])->toBe('EGP')
            ->and($paymob['payment_amount']['amount'])->toBe('500.00')
            ->and($paymob['display_currency'])->toBe($display)
            ->and($paymob['display_amount']['amount'])->toBe($displayAmount)
            ->and($paymob['notice'])->toBe('charged_in_payment_currency');
    })->with([
        'display in USD' => ['USD', '10.00'],
        'display in SAR' => ['SAR', '37.50'],
    ]);

    // OD-1: was "refuses Paymob at place-order for a non-EGP order"; a USD display now places a
    // Paymob order charged in EGP, converted once with the checkout rate snapshot.
    it('places a Paymob order from a USD display, charging EGP once at the snapshot rate', function (): void {
        fakePaymob();
        addToPaymentCart($this->user, $this->variant);

        $this->withHeaders(['CF-IPCountry' => 'EG']);
        placePaymentOrder($this->user, placeOrderBody('10.00', ['gateway' => 'paymob', 'country' => 'EG']))
            ->assertCreated()
            ->assertJsonPath('data.order.display_total.amount', '10.00')
            ->assertJsonPath('data.order.payment_total.amount', '500.00')
            ->assertJsonPath('data.order.payment_total.currency', 'EGP');

        $order = Order::query()->sole();

        expect($order->display_currency)->toBe('USD')
            ->and($order->payment_currency)->toBe('EGP')
            ->and(Money::of($order->payment_total, 'EGP')->amount)->toBe(Money::of('500', 'EGP')->amount)
            ->and((string) $order->payment_rate)->toStartWith('50');
    });

    it('still refuses Paymob at place-order outside Egypt, whatever the client sends', function (): void {
        addToPaymentCart($this->user, $this->variant);

        $this->withHeaders(['CF-IPCountry' => 'EG']);
        placePaymentOrder($this->user, placeOrderBody('10.00', ['gateway' => 'paymob', 'country' => 'US']))->assertJsonPath('code', 'gateway_not_eligible');
    });

    it('charges Stripe in USD even when the merchant account also enables the display currency', function (): void {
        gatewayFor('stripe', ['USD', 'EGP'], keepRules: true, defaultCurrency: 'USD');
        addToPaymentCart($this->user, $this->variant);

        $stripe = collect(optionsFor('US', 'US', 'EGP')->json('data.options'))->firstWhere('gateway', 'stripe');

        expect($stripe['payment_currency'])->toBe('USD')
            ->and($stripe['payment_amount']['amount'])->toBe('10.00')
            ->and($stripe['display_amount']['amount'])->toBe('500.00');
    });

    it('offers Stripe and PayPal internationally but not to customers declared in Egypt', function (): void {
        addToPaymentCart($this->user, $this->variant);

        // OD-1: Paymob is now offered to a customer declared and located in Egypt whatever the
        // display currency (here USD); before OD-1 a USD display offered nothing in Egypt.
        expect(offeredGateways(optionsFor('US', 'US')))->toBe(['stripe', 'paypal'])
            ->and(offeredGateways(optionsFor('EG', 'EG')))->toBe(['paymob']);
    });

    it('charges PayPal in its default currency when the customer browses in EGP, SAR or AED, and says so', function (string $display): void {
        addToPaymentCart($this->user, $this->variant);

        $paypal = collect(optionsFor('US', 'US', $display)->json('data.options'))->firstWhere('gateway', 'paypal');

        expect($paypal['payment_currency'])->toBe('USD')
            ->and($paypal['payment_amount']['amount'])->toBe('10.00')
            ->and($paypal['display_currency'])->toBe($display)
            ->and($paypal['notice'])->toBe('charged_in_payment_currency');
    })->with(['EGP', 'SAR', 'AED']);

    it('lists a gateway as unavailable when its payment currency would push a unit below the margin floor', function (): void {
        app(SettingsService::class)->set('pricing', 'conversion_cost_pct', '5', SettingType::Decimal);
        givenRate('EUR', '0.9');
        gatewayFor('paypal', ['EUR'], keepRules: true);
        PaymentGatewayCurrency::query()->where('currency', 'USD')->whereHas('paymentGateway', fn ($query) => $query->where('key', 'paypal'))->delete();
        addToPaymentCart($this->user, $this->variant);

        $options = collect(optionsFor('US', 'US')->json('data.options'));
        $paypal = $options->firstWhere('gateway', 'paypal');

        expect($options->firstWhere('gateway', 'stripe')['available'])->toBeTrue()
            ->and($paypal['payment_currency'])->toBe('EUR');

        expect($paypal['available'])->toBeFalse()->and($paypal['notice'])->toBe('payment_currency_below_margin');
    });
});

describe('place-order with the real resolver', function (): void {
    it('places the order, snapshots the payment currency and IP country, and creates the Stripe attempt', function (): void {
        gatewayFor('stripe', ['USD'], keepRules: true);
        useHeaderGeoIp();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent(status: 'requires_payment_method'))]);
        addToPaymentCart($this->user, $this->variant, 2);

        $this->withHeaders(['CF-IPCountry' => 'US']);
        $response = placePaymentOrder($this->user, placeOrderBody('20.00', ['gateway' => 'stripe']))
            ->assertCreated()
            ->assertJsonPath('data.order.status', 'pending_payment')
            ->assertJsonPath('data.next.gateway', 'stripe')
            ->assertJsonPath('data.next.type', 'stripe')
            ->assertJsonPath('data.next.client_secret', 'pi_fixture_secret_fixture');

        $order = Order::query()->sole();
        $payment = Payment::query()->sole();
        expect($order->ip_country)->toBe('US')
            ->and($order->payment_currency)->toBe('USD')
            ->and($order->payment_total)->toBe('20.000000')
            ->and($payment->amount)->toBe('20.000000')
            ->and($payment->public_id)->toBe($response->json('data.next.payment_public_id'));
        expect(json_encode($response->json()))->not->toContain('sk_test');

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/payment_intents') && str_contains($request->body(), 'amount=2000'));
    });

    it('returns the order with a null next when the first attempt cannot start, and lets the customer retry', function (): void {
        gatewayFor('stripe', ['USD'], keepRules: true);
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(['error' => ['type' => 'api_error', 'message' => 'down']], 500)]);
        addToPaymentCart($this->user, $this->variant);

        $public = placePaymentOrder($this->user, placeOrderBody('10.00', ['gateway' => 'stripe']))->assertCreated()->assertJsonPath('data.next', null)->json('data.order.public_id');

        resetHttpFakes();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_retry', 'requires_payment_method'))]);

        $this->actingAs($this->user)->postJson("/api/v1/checkout/orders/{$public}/retry-payment")
            ->assertCreated()->assertJsonPath('data.next.client_secret', 'pi_retry_secret_fixture');

        expect(Payment::query()->orderBy('attempt_no')->pluck('attempt_no')->all())->toBe([1, 2])
            ->and(Order::query()->sole()->status)->toBe(OrderStatus::PendingPayment);
    });

    it('refuses to retry with another gateway or once the order is paid', function (): void {
        $gateway = gatewayFor('stripe', ['USD'], keepRules: true);
        [$order, $payment] = orderAwaitingPayment($gateway, '10.00', orderAttributes: ['user_id' => $this->user->id]);

        $this->actingAs($this->user)->postJson("/api/v1/checkout/orders/{$order->public_id}/retry-payment", ['gateway' => 'paypal'])
            ->assertUnprocessable()->assertJsonPath('code', 'gateway_change_unsupported');

        app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('10', 'USD')), OrderActor::webhook());

        $this->actingAs($this->user)->postJson("/api/v1/checkout/orders/{$order->public_id}/retry-payment")
            ->assertStatus(409)->assertJsonPath('code', 'order_not_payable');
    });
});

describe('GeoIP', function (): void {
    it('resolves nothing with the default null driver', function (): void {
        $request = HttpRequest::create('/', server: ['HTTP_CF_IPCOUNTRY' => 'EG']);

        expect(app(GeoIpResolver::class)->countryFor($request))->toBeNull();
    });

    it('reads a two-letter country from the trusted header and ignores unknown or malformed values', function (?string $header, ?string $expected): void {
        $request = HttpRequest::create('/', server: $header === null ? [] : ['HTTP_CF_IPCOUNTRY' => $header]);

        expect((new HeaderGeoIpResolver('CF-IPCountry'))->countryFor($request))->toBe($expected);
    })->with([
        'country' => ['eg', 'EG'],
        'unknown' => ['XX', null],
        'tor' => ['T1', null],
        'malformed' => ['EGY', null],
        'absent' => [null, null],
    ]);
});
