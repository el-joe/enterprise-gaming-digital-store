<?php

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Enums\ProductCountryRestrictionMode;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\ProductCountryRestriction;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Services\UnavailableCheckoutCoupons;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Testing\TestResponse;

const CART = '/api/v1/storefront/cart';

function playerIdVariant(bool $required = true): ProductVariant
{
    $variant = sellableVariant('10');
    $definition = DeliveryFieldDefinition::factory()->create([
        'code' => 'player_id',
        'label' => 'Player ID',
        'input_type' => DeliveryFieldDefinitionInputType::Number,
        'validation_regex' => '\d{6,12}',
    ]);
    ProductDeliveryField::factory()->create(['product_id' => $variant->product_id, 'delivery_field_definition_id' => $definition->id, 'is_required' => $required]);

    return $variant;
}

function cartTokenFrom(TestResponse $response): string
{
    return $response->getCookie('cart_token')->getValue();
}

it('lets a guest fill a cart identified by an http-only cookie', function (): void {
    $variant = sellableVariant('10');

    $response = $this->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 2])
        ->assertCreated()
        ->assertJsonPath('data.cart.items.0.quantity', 2)
        ->assertJsonPath('data.cart.items.0.unit_price.amount', '10.00')
        ->assertJsonPath('data.cart.totals.total', ['amount' => '20.00', 'currency' => 'USD', 'formatted' => 'USD20.00'])
        ->assertJsonPath('data.cart.checkout_ready', true)
        ->assertJsonMissingPath('data.cart.items.0.cost');

    $cookie = $response->getCookie('cart_token');
    expect($cookie->isHttpOnly())->toBeTrue()
        ->and(Cart::query()->sole()->session_token)->toBe(hash('sha256', $cookie->getValue()));

    $this->withCredentials()->withCookie('cart_token', cartTokenFrom($response))
        ->getJson(CART)
        ->assertOk()
        ->assertJsonPath('data.cart.items.0.variant.public_id', $variant->public_id);
});

it('returns no cart to a visitor without one', function (): void {
    $this->getJson(CART)->assertOk()->assertJsonPath('data.cart', null);
});

it('merges a repeated add into one line and enforces the per-product limit', function (): void {
    $variant = sellableVariant('10', product: ['max_quantity_per_order' => 3]);
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 2])->assertCreated();
    $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated()
        ->assertJsonCount(1, 'data.cart.items')
        ->assertJsonPath('data.cart.items.0.quantity', 3);

    $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'validation_failed')
        ->assertJsonValidationErrors('quantity');
});

it('updates and removes only items in the owner\'s own cart', function (): void {
    $variant = sellableVariant('10');
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $this->actingAs($intruder)->postJson(CART.'/items', ['variant_id' => sellableVariant('5')->public_id, 'quantity' => 1])->assertCreated();
    $itemId = $this->actingAs($owner)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->json('data.cart.items.0.id');

    $this->actingAs($intruder)->patchJson(CART."/items/{$itemId}", ['quantity' => 5])->assertNotFound()->assertJsonPath('code', 'not_found');
    $this->actingAs($intruder)->deleteJson(CART."/items/{$itemId}")->assertNotFound();
    $this->postJson(CART.'/items', [])->assertUnprocessable();

    $this->actingAs($owner)->patchJson(CART."/items/{$itemId}", ['quantity' => 4])->assertOk()->assertJsonPath('data.cart.items.0.quantity', 4);
    $this->actingAs($owner)->deleteJson(CART."/items/{$itemId}")->assertOk()->assertJsonCount(0, 'data.cart.items');

    expect(CartItem::query()->count())->toBe(1);
});

it('refuses products that cannot be sold', function (array $variant, array $product, string $message): void {
    $sellable = sellableVariant('10', $variant, $product);

    $this->postJson(CART.'/items', ['variant_id' => $sellable->public_id, 'quantity' => 1])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'item_unavailable')
        ->assertJsonPath('errors.variant_id.0', $message);
})->with([
    'draft product' => [[], ['status' => ProductStatus::Draft], 'This product is not available.'],
    'inactive variant' => [['is_active' => false], [], 'This product is not available.'],
    'out of stock' => [['availability' => ProductVariantAvailability::OutOfStock], [], 'This product is out of stock.'],
    'custom amount' => [['is_custom_amount' => true], [], 'Custom amounts cannot be bought yet.'],
]);

it('refuses a custom amount in the request', function (): void {
    $this->postJson(CART.'/items', ['variant_id' => sellableVariant()->public_id, 'quantity' => 1, 'custom_amount' => '5'])
        ->assertJsonValidationErrors(['custom_amount' => 'Custom amounts cannot be bought yet.']);
});

describe('delivery fields', function (): void {
    it('requires and validates a Player ID when adding to the cart', function (array $values, string $error): void {
        $variant = playerIdVariant();

        $this->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1, 'delivery_values' => $values])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors($error);

        expect(CartItem::query()->exists())->toBeFalse();
    })->with([
        'missing' => [[], 'delivery_values.player_id'],
        'not a number' => [['player_id' => 'abc123'], 'delivery_values.player_id'],
        'too short for the pattern' => [['player_id' => '123'], 'delivery_values.player_id'],
        'unknown field' => [['player_id' => '1234567', 'server' => 'eu'], 'delivery_values.server'],
    ]);

    it('stores accepted values encrypted and shows them to the owner', function (): void {
        $variant = playerIdVariant();

        $this->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1, 'delivery_values' => ['player_id' => ' 1234567 ']])
            ->assertCreated()
            ->assertJsonPath('data.cart.items.0.delivery_values.player_id', '1234567');

        expect(DB::table('cart_items')->value('delivery_values'))->not->toContain('1234567');
    });

    it('keeps different Player IDs for the same variant on separate lines', function (): void {
        $variant = playerIdVariant();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1, 'delivery_values' => ['player_id' => '1234567']]);
        $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1, 'delivery_values' => ['player_id' => '7654321']])
            ->assertJsonCount(2, 'data.cart.items');
    });
});

describe('coupons', function (): void {
    it('refuses every code under the fail-closed coupon binding', function (): void {
        app()->bind(CheckoutCoupons::class, UnavailableCheckoutCoupons::class);
        $user = User::factory()->create();
        Coupon::factory()->create(['code' => 'SAVE10']);
        $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => sellableVariant()->public_id, 'quantity' => 1]);

        $this->actingAs($user)->postJson(CART.'/coupon', ['code' => 'SAVE10'])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'coupon_invalid')
            ->assertJsonPath('message', UnavailableCheckoutCoupons::MESSAGE);

        expect(Cart::query()->sole()->coupon_id)->toBeNull();
    });

    it('applies a code accepted by the coupon contract and removes it again', function (): void {
        $coupon = Coupon::factory()->create(['code' => 'FIVE']);
        fakeCoupons(['FIVE' => ['coupon' => $coupon, 'terms' => new CouponTerms(CouponType::Fixed, '5')]]);
        PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 2]);

        $this->actingAs($user)->postJson(CART.'/coupon', ['code' => 'five'])
            ->assertOk()
            ->assertJsonPath('data.cart.coupon', ['code' => 'FIVE', 'applied' => true, 'error' => null, 'limited_by_margin' => false])
            ->assertJsonPath('data.cart.totals.subtotal.amount', '30.00')
            ->assertJsonPath('data.cart.totals.discount.amount', '5.00')
            ->assertJsonPath('data.cart.totals.total.amount', '25.00');

        $this->actingAs($user)->deleteJson(CART.'/coupon')->assertOk()->assertJsonPath('data.cart.coupon', null)
            ->assertJsonPath('data.cart.totals.total.amount', '30.00');
    });
});

it('lets a customer who signs in keep the cart they filled as a guest', function (): void {
    $variant = sellableVariant('10');
    $token = cartTokenFrom($this->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1]));
    $user = User::factory()->create();

    $this->actingAs($user)->withCredentials()->withCookie('cart_token', $token)->getJson(CART)
        ->assertJsonPath('data.cart.items.0.variant.public_id', $variant->public_id);

    expect(Cart::query()->sole())->user_id->toBe($user->id)->session_token->toBeNull()->status->toBe(CartStatus::Active);
});

it('marks lines not sold in the chosen country and refuses suspended accounts', function (): void {
    $variant = sellableVariant('10');
    CountryFactory::ensure('EG');
    ProductCountryRestriction::factory()->create(['product_id' => $variant->product_id, 'country_code' => 'EG', 'mode' => ProductCountryRestrictionMode::Deny]);
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => $variant->public_id, 'quantity' => 1]);

    $this->actingAs($user)->postJson('/api/v1/checkout/validate', ['country' => 'EG'])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'item_unavailable');

    $this->actingAs(User::factory()->suspended()->create())->getJson(CART)->assertForbidden();
});

it('needs a CSRF token for cart writes outside tests', function (): void {
    $this->app['env'] = 'local';

    $this->postJson(CART.'/items', ['variant_id' => sellableVariant()->public_id, 'quantity' => 1])
        ->assertStatus(419)
        ->assertJsonPath('code', 'csrf_expired');
});

it('prices the cart in the same number of queries however many lines it has', function (): void {
    $user = User::factory()->create();
    $queriesFor = function (int $lines) use ($user): int {
        Cart::query()->delete();

        foreach (range(1, $lines) as $line) {
            $this->actingAs($user)->postJson(CART.'/items', ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 1])->assertCreated();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($user)->getJson(CART)->assertOk()->assertJsonCount($lines, 'data.cart.items');
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    };

    expect($queriesFor(2))->toBe($queriesFor(6));
});
