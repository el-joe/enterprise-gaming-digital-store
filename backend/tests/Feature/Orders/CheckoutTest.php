<?php

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\IdempotencyKey;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\Services\UnavailablePaymentCurrencyResolver;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Carbon\CarbonInterface;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;

function cartFor(?User $user, ProductVariant $variant, int $quantity = 1, array $deliveryValues = []): TestResponse
{
    $request = $user === null ? test() : test()->actingAs($user);

    return $request->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => $quantity, 'delivery_values' => $deliveryValues])->assertCreated();
}

/**
 * @param  array<string, mixed>  $body
 */
function placeOrder(?User $user, array $body, string $key = 'order-key-0001'): TestResponse
{
    $request = $user === null ? test() : test()->actingAs($user);

    return $request->postJson('/api/v1/checkout/place-order', $body, ['Idempotency-Key' => $key]);
}

function applyCoupon(User $user, string $code, CouponTerms $terms): Coupon
{
    $coupon = Coupon::factory()->create(['code' => $code]);
    fakeCoupons([$code => ['coupon' => $coupon, 'terms' => $terms]]);
    test()->actingAs($user)->postJson('/api/v1/storefront/cart/coupon', ['code' => $code])->assertOk();

    return $coupon;
}

function egpRate(string $rate = '50', ?CarbonInterface $fetchedAt = null): ExchangeRateHistory
{
    CurrencyFactory::ensure('EGP');
    $history = ExchangeRateHistory::factory()->create(['quote_currency' => 'EGP', 'provider_rate' => $rate, 'effective_rate' => $rate, 'source' => 'test_provider']);
    givenRate('EGP', $rate, ['fetched_at' => $fetchedAt ?? now()->subMinutes(5)]);

    return $history;
}

it('places a pending_payment order with items, units and an event, and converts the cart', function (): void {
    fakePayments();
    $user = User::factory()->create();
    $variant = sellableVariant('10');
    cartFor($user, $variant, 2);

    $response = placeOrder($user, placeOrderBody('20.00'))
        ->assertCreated()
        ->assertJsonPath('data.order.status', 'pending_payment')
        ->assertJsonPath('data.order.display_total.amount', '20.00')
        ->assertJsonPath('data.order.payment_total.amount', '20.00')
        ->assertJsonPath('meta.duplicate', false);

    $order = Order::query()->sole();
    expect($order->public_id)->toBe($response->json('data.order.public_id'))
        ->and($order->user_id)->toBe($user->id)
        ->and($order->contact_email)->toBe($user->email)
        ->and($order->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->expires_at->greaterThan(now()->addMinutes(29)))->toBeTrue()
        ->and(Cart::query()->sole()->status)->toBe(CartStatus::Converted);

    $item = OrderItem::query()->sole();
    expect($item->quantity)->toBe(2)
        ->and($item->unit_price_base)->toBe('10.000000')
        ->and($item->line_total_base)->toBe('20.000000')
        ->and($item->sku_snapshot)->toBe($variant->sku)
        ->and($item->estimated_unit_cost_base)->toBe('10.000000')
        ->and(OrderItemUnit::query()->where('order_item_id', $item->id)->pluck('status')->all())->toBe([OrderItemUnitStatus::Pending, OrderItemUnitStatus::Pending])
        ->and(array_column($item->pricing_breakdown['units'], 'unit_public_id'))->toEqualCanonicalizing(OrderItemUnit::query()->pluck('public_id')->all());

    $event = OrderEvent::query()->sole();
    expect($event->type)->toBe('order.placed')
        ->and($event->from_status)->toBeNull()
        ->and($event->to_status)->toBe(OrderStatus::PendingPayment)
        ->and($event->actor_type)->toBe(OrderEventActorType::Customer)
        ->and($event->metadata['terms_accepted'])->toBeTrue();

    $this->actingAs($user)->getJson("/api/v1/checkout/orders/{$order->public_id}/status")
        ->assertOk()
        ->assertJsonPath('data', ['public_id' => $order->public_id, 'status' => 'pending_payment', 'payment_status' => 'unpaid', 'is_final' => false]);
});

describe('snapshots', function (): void {
    it('populates every currency snapshot field once, from the rate in force', function (): void {
        $history = egpRate('50');
        $gateway = fakePayments();
        $user = User::factory()->create();
        $this->withUnencryptedCookie('currency', 'EGP')->withCredentials();
        cartFor($user, sellableVariant('10'), 2);

        placeOrder($user, placeOrderBody('1000.00', ['currency' => 'EGP']))->assertCreated();

        $order = Order::query()->sole();
        $snapshot = $order->only(['base_currency', 'subtotal_base', 'discount_base', 'tax_base', 'total_base', 'display_currency', 'display_rate', 'display_total', 'payment_currency', 'payment_rate', 'payment_total', 'payment_gateway_id', 'rate_source', 'rate_fetched_at', 'exchange_rate_history_id']);

        expect(array_filter($snapshot, static fn (mixed $value): bool => $value === null))->toBe([])
            ->and($snapshot)->toMatchArray([
                'base_currency' => 'USD', 'subtotal_base' => '20.000000', 'discount_base' => '0.000000', 'tax_base' => '0.000000', 'total_base' => '20.000000',
                'display_currency' => 'EGP', 'display_rate' => '50.000000000000', 'display_total' => '1000.000000',
                'payment_currency' => 'EGP', 'payment_rate' => '50.000000000000', 'payment_total' => '1000.000000',
                'rate_source' => 'test_provider', 'exchange_rate_history_id' => $history->id,
            ])
            ->and($gateway->queries[0]->displayCurrency)->toBe('EGP')
            ->and($gateway->queries[0]->totalBase->amount)->toBe('20.000000');
    });

    it('charges a different payment currency from the base total with a fresh rate', function (): void {
        $history = egpRate('50');
        fakePayments('EGP');
        $user = User::factory()->create();
        cartFor($user, sellableVariant('10.333'), 1);

        placeOrder($user, placeOrderBody('10.33'))->assertCreated()
            ->assertJsonPath('data.order.display_total.amount', '10.33')
            ->assertJsonPath('data.order.payment_total', ['amount' => '516.65', 'currency' => 'EGP', 'formatted' => 'EGP516.65']);

        expect(Order::query()->sole())
            ->display_currency->toBe('USD')
            ->display_total->toBe('10.330000')
            ->payment_currency->toBe('EGP')
            ->payment_rate->toBe('50.000000000000')
            ->payment_total->toBe('516.650000')
            ->exchange_rate_history_id->toBe($history->id);
    });

    it('never changes a placed order when rates, prices and settings change later', function (): void {
        egpRate('50');
        fakePayments();
        $user = User::factory()->create();
        $variant = sellableVariant('10');
        $this->withUnencryptedCookie('currency', 'EGP')->withCredentials();
        cartFor($user, $variant, 1);
        placeOrder($user, placeOrderBody('500.00', ['currency' => 'EGP']))->assertCreated();
        $before = [DB::table('orders')->first(), DB::table('order_items')->first(), DB::table('order_item_units')->get()];

        ExchangeRate::query()->where('quote_currency', 'EGP')->update(['provider_rate' => '61', 'effective_rate' => '61', 'fetched_at' => now()]);
        app(ExchangeRateRegistry::class)->forget();
        PricingRule::factory()->create(['percent_markup' => '40', 'is_active' => true]);
        $variant->forceFill(['manual_cost_base' => '12', 'pricing_dirty_at' => now()])->save();
        Artisan::call('pricing:recalculate-dirty');

        expect([DB::table('orders')->first(), DB::table('order_items')->first(), DB::table('order_item_units')->get()])->toEqual($before)
            ->and($variant->fresh()->price_base)->toBe('16.800000');
    });
});

describe('stale rates over HTTP', function (): void {
    beforeEach(function (): void {
        egpRate('50', now()->subDays(2));
        fakePayments();
        $this->user = User::factory()->create();
        $this->withUnencryptedCookie('currency', 'EGP')->withCredentials();
        cartFor($this->user, sellableVariant('10'));
    });

    it('still shows the cart with a may-vary flag but not as ready', function (): void {
        $this->actingAs($this->user)->getJson('/api/v1/storefront/cart')
            ->assertOk()
            ->assertJsonPath('data.cart.currency', 'EGP')
            ->assertJsonPath('data.cart.price_may_vary', true)
            ->assertJsonPath('data.cart.checkout_ready', false);
    });

    it('blocks checkout validation and placement in that currency', function (): void {
        $this->actingAs($this->user)->postJson('/api/v1/checkout/validate', ['country' => 'US'])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'rate_stale');

        placeOrder($this->user, placeOrderBody('500.00', ['currency' => 'EGP']))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'rate_stale');

        expect(Order::query()->exists())->toBeFalse()
            ->and(IdempotencyKey::query()->exists())->toBeFalse()
            ->and(Cart::query()->sole()->status)->toBe(CartStatus::Active);
    });

    it('allows checkout once the customer switches to the base currency', function (): void {
        $this->withUnencryptedCookie('currency', 'USD');

        $this->actingAs($this->user)->postJson('/api/v1/checkout/validate', ['country' => 'US'])
            ->assertOk()
            ->assertJsonPath('data.cart.totals.total.amount', '10.00');

        placeOrder($this->user, placeOrderBody('10.00'))->assertCreated();
    });

    it('blocks a payment currency whose rate is stale even when display is fresh', function (): void {
        fakePayments('EGP');
        $this->withUnencryptedCookie('currency', 'USD');

        placeOrder($this->user, placeOrderBody('10.00'))->assertUnprocessable()->assertJsonPath('code', 'rate_stale');

        expect(Order::query()->exists())->toBeFalse();
    });
});

describe('refusals that change nothing', function (): void {
    it('refuses placement until M10 binds a payment currency resolver', function (): void {
        $user = User::factory()->create();
        cartFor($user, sellableVariant('10'));

        placeOrder($user, placeOrderBody('10.00'))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'gateway_not_eligible')
            ->assertJsonPath('message', UnavailablePaymentCurrencyResolver::MESSAGE);

        expect(Order::query()->exists())->toBeFalse()->and(IdempotencyKey::query()->exists())->toBeFalse();
    });

    it('refuses when the total or currency differs from what the customer saw', function (array $overrides): void {
        fakePayments();
        $user = User::factory()->create();
        cartFor($user, sellableVariant('10'));

        placeOrder($user, placeOrderBody('10.00', $overrides))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'price_changed')
            ->assertJsonPath('errors.expected_total.0', 'The order total is now 10.00 USD. Please review it before paying.');

        expect(Order::query()->exists())->toBeFalse();
    })->with([
        'total' => [['expected_total' => '9.99']],
        'currency' => [['currency' => 'EUR']],
    ]);

    it('validates the request', function (array $body, array $errors): void {
        fakePayments();
        cartFor(null, sellableVariant('10'));

        $this->postJson('/api/v1/checkout/place-order', $body, ['Idempotency-Key' => 'k'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    })->with([
        'guest without email, bad key, no terms' => [placeOrderBody('10.00', ['consents' => ['terms' => false]]), ['email', 'idempotency_key', 'consents.terms']],
        'money as a JSON number' => [placeOrderBody('10.00', ['expected_total' => 10, 'email' => 'a@example.com']), ['expected_total']],
        'unknown country' => [placeOrderBody('10.00', ['country' => 'ZZ', 'email' => 'a@example.com']), ['country']],
    ]);

    it('refuses an empty cart', function (): void {
        fakePayments();
        CountryFactory::ensure('US');

        placeOrder(User::factory()->create(), placeOrderBody('0'))->assertUnprocessable()->assertJsonPath('code', 'cart_empty');
    });

    it('rolls back everything when the transaction fails late, and the same key can retry', function (): void {
        fakePayments();
        PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
        $coupon = Coupon::factory()->create(['code' => 'ONE']);
        $coupons = fakeCoupons(['ONE' => ['coupon' => $coupon, 'terms' => new CouponTerms(CouponType::Fixed, '1')]]);
        $user = User::factory()->create();
        cartFor($user, sellableVariant('10'));
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/coupon', ['code' => 'ONE'])->assertOk();
        $coupons->failOnReserve = true;

        placeOrder($user, placeOrderBody('14.00'))->assertServerError()->assertJsonPath('code', 'server_error');

        foreach (['orders', 'order_items', 'order_item_units', 'order_events', 'order_item_delivery_values', 'idempotency_keys'] as $table) {
            expect(DB::table($table)->count())->toBe(0, $table);
        }
        expect(Cart::query()->sole()->status)->toBe(CartStatus::Active);

        $coupons->failOnReserve = false;
        placeOrder($user, placeOrderBody('14.00'))->assertCreated();

        expect(Order::query()->count())->toBe(1)
            ->and($coupons->reserved)->toBe([['coupon_id' => $coupon->id, 'order_id' => Order::query()->sole()->id, 'discount_base' => '1.000000']]);
    });
});

describe('idempotency', function (): void {
    beforeEach(function (): void {
        fakePayments();
        $this->user = User::factory()->create();
        cartFor($this->user, sellableVariant('10'));
    });

    it('replays the stored response for the same key and body', function (): void {
        $first = placeOrder($this->user, placeOrderBody('10.00'))->assertCreated();

        placeOrder($this->user, placeOrderBody('10.00'))
            ->assertCreated()
            ->assertHeader('Idempotency-Replayed', 'true')
            ->assertExactJson($first->json());

        expect(Order::query()->count())->toBe(1)->and(OrderEvent::query()->count())->toBe(1);
    });

    it('treats the body as the same regardless of key order', function (): void {
        placeOrder($this->user, placeOrderBody('10.00'))->assertCreated();

        placeOrder($this->user, array_reverse(placeOrderBody('10.00'), true))->assertCreated()->assertHeader('Idempotency-Replayed', 'true');
    });

    it('refuses the same key with a different body', function (): void {
        placeOrder($this->user, placeOrderBody('10.00'))->assertCreated();

        placeOrder($this->user, placeOrderBody('10.00', ['consents' => ['terms' => true, 'marketing' => true]]))
            ->assertConflict()
            ->assertJsonPath('code', 'idempotency_mismatch');

        expect(Order::query()->count())->toBe(1);
    });

    it('scopes keys to their owner', function (): void {
        placeOrder($this->user, placeOrderBody('10.00'))->assertCreated();
        $other = User::factory()->create();
        cartFor($other, sellableVariant('10'));

        placeOrder($other, placeOrderBody('10.00'))->assertCreated()->assertHeaderMissing('Idempotency-Replayed');

        expect(Order::query()->count())->toBe(2);
    });
});

describe('duplicate placement', function (): void {
    beforeEach(function (): void {
        fakePayments();
        $this->user = User::factory()->create();
        $this->variant = sellableVariant('10');
    });

    it('does not place the same cart twice under a new key', function (): void {
        cartFor($this->user, $this->variant);
        placeOrder($this->user, placeOrderBody('10.00'), 'first-key-01')->assertCreated();

        placeOrder($this->user, placeOrderBody('10.00'), 'second-key-02')->assertUnprocessable()->assertJsonPath('code', 'cart_empty');

        expect(Order::query()->count())->toBe(1);
    });

    it('returns the open order when the same contents are placed again from a new cart', function (): void {
        cartFor($this->user, $this->variant);
        $first = placeOrder($this->user, placeOrderBody('10.00'), 'first-key-01')->json('data.order.public_id');
        cartFor($this->user, $this->variant);

        placeOrder($this->user, placeOrderBody('10.00'), 'second-key-02')
            ->assertOk()
            ->assertJsonPath('meta.duplicate', true)
            ->assertJsonPath('data.order', ['public_id' => $first, 'status' => 'pending_payment']);

        expect(Order::query()->count())->toBe(1)
            ->and(Cart::query()->where('status', CartStatus::Active)->count())->toBe(1);
    });

    it('allows the same contents again once the earlier order is closed', function (): void {
        cartFor($this->user, $this->variant);
        placeOrder($this->user, placeOrderBody('10.00'), 'first-key-01')->assertCreated();
        app(OrderStateMachine::class)->transition(Order::query()->sole(), OrderStatus::Cancelled, OrderActor::customer($this->user));
        cartFor($this->user, $this->variant);

        placeOrder($this->user, placeOrderBody('10.00'), 'second-key-02')->assertCreated();

        expect(Order::query()->count())->toBe(2);
    });

    it('treats two guest carts with the same email and contents as one placement', function (): void {
        $body = placeOrderBody('10.00', ['email' => 'Guest@Example.com']);
        $tokenA = cartFor(null, $this->variant)->getCookie('cart_token')->getValue();
        $this->withCredentials()->withCookie('cart_token', $tokenA);
        $first = placeOrder(null, $body, 'guest-key-01')->assertCreated()->json('data.order.public_id');

        $this->withCookie('cart_token', str_repeat('b', 64));
        cartFor(null, $this->variant);

        placeOrder(null, $body, 'guest-key-02')->assertOk()->assertJsonPath('data.order.public_id', $first);

        expect(Order::query()->sole()->guest_email)->toBe('guest@example.com');
    });
});

describe('guest orders', function (): void {
    it('places a guest order and shows its status only to the session that placed it', function (): void {
        fakePayments();
        $token = cartFor(null, sellableVariant('5'))->getCookie('cart_token')->getValue();
        $this->withCredentials()->withCookie('cart_token', $token);

        $publicId = placeOrder(null, placeOrderBody('5.00', ['email' => 'buyer@example.com']))->assertCreated()->json('data.order.public_id');

        expect(Order::query()->where('public_id', $publicId)->sole())
            ->user_id->toBeNull()
            ->guest_email->toBe('buyer@example.com')
            ->guest_email_verified_at->toBeNull();

        $this->getJson("/api/v1/checkout/orders/{$publicId}/status")->assertOk()->assertJsonPath('data.status', 'pending_payment');

        $this->flushSession();
        $this->getJson("/api/v1/checkout/orders/{$publicId}/status")->assertNotFound();
        $this->actingAs(User::factory()->create())->getJson("/api/v1/checkout/orders/{$publicId}/status")->assertNotFound();
    });
});

it('revalidates delivery values at checkout and stores them encrypted on the order', function (): void {
    fakePayments();
    $user = User::factory()->create();
    $variant = sellableVariant('10');
    $definition = DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID', 'input_type' => DeliveryFieldDefinitionInputType::Number]);
    $field = ProductDeliveryField::factory()->create(['product_id' => $variant->product_id, 'delivery_field_definition_id' => $definition->id, 'is_required' => true]);
    $itemId = cartFor($user, $variant, 1, ['player_id' => '12345'])->json('data.cart.items.0.id');

    $definition->forceFill(['validation_regex' => '\d{8}'])->save();

    $this->actingAs($user)->postJson('/api/v1/checkout/validate', ['country' => 'US'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors("items.{$itemId}.delivery_values.player_id");
    placeOrder($user, placeOrderBody('10.00'))->assertJsonValidationErrors("items.{$itemId}.delivery_values.player_id");

    $definition->forceFill(['validation_regex' => null])->save();
    placeOrder($user, placeOrderBody('10.00'), 'retry-key-01')->assertCreated();

    $value = OrderItemDeliveryValue::query()->sole();
    expect($value->field_code)->toBe('player_id')
        ->and($value->field_label)->toBe('Player ID')
        ->and($value->value)->toBe('12345')
        ->and(DB::table('order_item_delivery_values')->value('value'))->not->toContain('12345')
        ->and($field->exists)->toBeTrue();
});

describe('cart-level coupon allocation (ADR-025)', function (): void {
    beforeEach(function (): void {
        fakePayments();
        PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
        $this->user = User::factory()->create();
    });

    it('splits a fixed discount over units in whole cents that sum to the discount exactly', function (): void {
        cartFor($this->user, sellableVariant('10'), 3);
        applyCoupon($this->user, 'ONE', new CouponTerms(CouponType::Fixed, '1'));

        placeOrder($this->user, placeOrderBody('44.00'))->assertCreated();

        $order = Order::query()->sole();
        $item = OrderItem::query()->sole();
        expect($order)
            ->subtotal_base->toBe('45.000000')
            ->discount_base->toBe('1.000000')
            ->total_base->toBe('44.000000')
            ->display_total->toBe('44.000000')
            ->coupon_code->toBe('ONE')
            ->and(array_column($item->pricing_breakdown['units'], 'discount_base'))->toBe(['0.340000', '0.330000', '0.330000'])
            ->and(array_column($item->pricing_breakdown['units'], 'net_display'))->toBe(['14.660000', '14.670000', '14.670000'])
            ->and($item->discount_base)->toBe('1.000000');
    });

    it('limits a unit to its margin floor and moves the rest of the discount to units with room', function (): void {
        $roomy = sellableVariant('10');
        $tight = sellableVariant('10');
        PricingRule::factory()->create(['scope' => PricingRuleScope::Variant, 'scope_id' => $tight->id, 'percent_markup' => '50', 'min_margin_fixed' => '4.9', 'below_margin_policy' => 'block', 'is_active' => true]);
        cartFor($this->user, $roomy);
        cartFor($this->user, $tight);
        applyCoupon($this->user, 'FOUR', new CouponTerms(CouponType::Fixed, '4'));

        $this->actingAs($this->user)->getJson('/api/v1/storefront/cart')
            ->assertJsonPath('data.cart.totals.discount.amount', '4.00')
            ->assertJsonPath('data.cart.coupon.limited_by_margin', false);
        placeOrder($this->user, placeOrderBody('26.00'))->assertCreated();

        $units = OrderItem::query()->orderBy('id')->get()->map(fn (OrderItem $item): array => [$item->pricing_breakdown['units'][0]['net_base'], $item->pricing_breakdown['floor_base']])->all();
        expect($units)->toBe([['11.100000', '10.000000'], ['14.900000', '14.900000']]);
    });

    it('forfeits what no unit can absorb and says so', function (): void {
        cartFor($this->user, sellableVariant('10'));
        applyCoupon($this->user, 'TWENTY', new CouponTerms(CouponType::Fixed, '20'));

        $this->actingAs($this->user)->getJson('/api/v1/storefront/cart')
            ->assertJsonPath('data.cart.totals.total.amount', '10.00')
            ->assertJsonPath('data.cart.coupon.limited_by_margin', true);
    });

    it('lets a coupon marked allow_below_margin cross the floor', function (): void {
        cartFor($this->user, sellableVariant('10'));
        applyCoupon($this->user, 'DEEP', new CouponTerms(CouponType::Percent, '60', allowBelowMargin: true));

        $this->actingAs($this->user)->getJson('/api/v1/storefront/cart')->assertJsonPath('data.cart.totals.total.amount', '6.00');
    });

    it('refuses checkout when the applied coupon no longer validates', function (): void {
        cartFor($this->user, sellableVariant('10'));
        applyCoupon($this->user, 'GONE', new CouponTerms(CouponType::Fixed, '1'));
        fakeCoupons();

        placeOrder($this->user, placeOrderBody('14.00'))->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid');
    });
});

it('expires an unpaid placed order and releases nothing it did not reserve', function (): void {
    fakePayments();
    app(SettingsService::class)->set('orders', 'quote_ttl_minutes', '15', SettingType::Int);
    $user = User::factory()->create();
    cartFor($user, sellableVariant('10'));
    placeOrder($user, placeOrderBody('10.00'))->assertCreated();

    $this->travel(16)->minutes();
    $this->artisan('orders:expire-unpaid')->assertSuccessful();

    expect(Order::query()->sole()->status)->toBe(OrderStatus::Cancelled)
        ->and(Order::query()->sole()->placement_hash)->toBeNull();
});
