<?php

/**
 * Product card face value and "Add to bag" (Storefront v2, owner request): the card shows the
 * gift card's face value in its own currency; its bag button adds a single purchasable variant
 * at once, or opens the chooser fed by `/catalog/products/{slug}/quick-add`; the bag keeps many
 * products at once and every cart response carries the lines the header count is built from.
 */

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Services\ExchangeRateWriter;
use App\Domains\Identity\Models\User;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    storefrontWithoutBuild();
    CurrencyFactory::ensure('USD');
    CurrencyFactory::ensure('EUR');
    Currency::query()->where('code', 'USD')->update(['symbol' => '$']);
    Currency::query()->where('code', 'EUR')->update(['symbol' => '€']);
    Cache::forget(ExchangeRateWriter::CACHE_KEY);
});

/**
 * A second variant on a product.
 *
 * @param  array<string, mixed>  $attributes
 */
function cardVariant(int $productId, string $price, array $attributes = []): ProductVariant
{
    $variant = ProductVariant::factory()->create(['product_id' => $productId, 'availability' => ProductVariantAvailability::InStock, 'manual_cost_base' => $price, ...$attributes]);
    $variant->forceFill(['price_base' => $price])->save();

    return $variant;
}

describe('face value', function (): void {
    it('shows the face value in the card currency, not the converted selling price', function (): void {
        $adidas = storefrontProduct('Adidas DE', '27.40', variant: ['denomination_value' => '25', 'denomination_currency' => 'EUR']);
        storefrontProduct('Steam US', '20.50', variant: ['denomination_value' => '20', 'denomination_currency' => 'USD']);
        cardVariant($adidas->id, '108', ['denomination_value' => '100', 'denomination_currency' => 'EUR']);

        $response = $this->get('/products')->assertOk();
        $html = $response->getContent();

        expect($html)->toMatch('/data-face-value>\s*€25–100\s*</')
            ->toMatch('/data-face-value>\s*\$20\s*</')
            ->toContain(storefrontPrice('20.50'));

        $cards = collect(statelessCall('GET', 'api/v1/storefront/catalog/products')->json('data'))->keyBy('name');
        expect($cards['Adidas DE']['face_value'])->toMatchArray(['label' => '€25–100', 'currency' => 'EUR', 'min' => '25', 'max' => '100', 'is_range' => true])
            ->and($cards['Steam US']['face_value'])->toMatchArray(['label' => '$20', 'currency' => 'USD', 'is_range' => false]);
    });

    it('shows "From" the lowest amount across currencies and min–max for a custom amount', function (): void {
        $mixed = storefrontProduct('Mixed', '10', variant: ['denomination_value' => '10', 'denomination_currency' => 'EUR']);
        cardVariant($mixed->id, '12', ['denomination_value' => '15', 'denomination_currency' => 'USD']);
        storefrontProduct('Custom', '5', variant: ['is_custom_amount' => true, 'custom_min' => '5', 'custom_max' => '200', 'denomination_currency' => 'USD']);

        $cards = collect(statelessCall('GET', 'api/v1/storefront/catalog/products')->json('data'))->keyBy('name');

        expect($cards['Mixed']['face_value']['label'])->toBe('From €10')
            ->and($cards['Custom']['face_value'])->toMatchArray(['label' => '$5–200', 'is_custom' => true]);
    });

    it('keeps the selling price on the product page and the bag, with the face value as secondary text', function (): void {
        $product = storefrontProduct('Steam US', '20.50', variant: ['title' => 'US account', 'denomination_value' => '20', 'denomination_currency' => 'USD']);
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($product)->public_id, 'quantity' => 1])->assertCreated()
            ->assertJsonPath('data.cart.items.0.variant.face_value', '$20');

        $this->get('/products/'.$product->slug)->assertOk()->assertSee(storefrontPrice('20.50'))->assertSee('data-face-value>$20<', false);
        $this->get('/cart')->assertOk()->assertSee(storefrontPrice('20.50'))->assertSee('Face value: $20');
    });
});

describe('card bag button', function (): void {
    it('adds a single-variant product at once from the card', function (): void {
        $product = storefrontProduct('Steam Wallet $20', '20');
        $variant = storefrontVariant($product);

        $this->get('/products')->assertOk()
            ->assertSee('data-quick-add data-slug="'.$product->slug.'" data-variant="'.$variant->public_id.'"', false)
            ->assertSee('aria-label="Add Steam Wallet $20 to bag"', false)
            ->assertSee('data-quick-add-dialog', false);

        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])->assertCreated()
            ->assertJsonPath('data.cart.items.0.product.slug', $product->slug)
            ->assertJsonPath('data.cart.items.0.quantity', 1);
    });

    it('opens the chooser for several variants, listing unavailable ones as not purchasable', function (string $base): void {
        $product = storefrontProduct('Steam Wallet', '20', variant: ['title' => '$20', 'denomination_value' => '20', 'denomination_currency' => 'USD', 'region_id' => Region::factory()->create(['code' => 'US', 'name' => 'United States'])->id]);
        cardVariant($product->id, '50', ['title' => '$50', 'denomination_value' => '50', 'denomination_currency' => 'USD', 'availability' => ProductVariantAvailability::OutOfStock]);
        cardVariant($product->id, '30', ['title' => 'Custom', 'is_custom_amount' => true, 'custom_min' => '5', 'custom_max' => '100', 'denomination_currency' => 'USD']);

        $card = collect(statelessCall('GET', "{$base}/catalog/products")->json('data'))->firstWhere('slug', $product->slug);
        expect($card['quick_add'])->toBe(['variant_count' => 1, 'variant_id' => ProductVariant::query()->where('product_id', $product->id)->where('title', '$20')->value('public_id'), 'requires_fields' => false]);

        cardVariant($product->id, '10', ['title' => '$10', 'denomination_value' => '10', 'denomination_currency' => 'USD', 'sort_order' => -1]);
        $card = collect(statelessCall('GET', "{$base}/catalog/products")->json('data'))->firstWhere('slug', $product->slug);
        expect($card['quick_add'])->toMatchArray(['variant_count' => 2, 'variant_id' => null]);

        $data = statelessCall('GET', "{$base}/catalog/products/{$product->slug}/quick-add")->assertOk()->json('data');

        expect($data['product'])->toMatchArray(['name' => 'Steam Wallet', 'slug' => $product->slug, 'max_quantity' => 10])
            ->and(array_column($data['variants'], 'title'))->toBe(['$10', '$20', '$50', 'Custom'])
            ->and(array_column($data['variants'], 'purchasable'))->toBe([true, true, false, false])
            ->and(array_column($data['variants'], 'face_value'))->toBe(['$10', '$20', '$50', '$5–100'])
            ->and($data['variants'][1])->toMatchArray(['region' => 'US', 'region_name' => 'United States', 'availability' => 'in_stock'])
            ->and($data['variants'][1]['price']['formatted'])->toBe(storefrontPrice('20.00'))
            ->and($data['delivery_fields'])->toBe([]);

        $this->get('/products')->assertOk()->assertSee('data-variant="" data-name="Steam Wallet"', false)->assertSee('aria-haspopup="dialog"', false);
    })->with(['web' => 'api/v1/storefront', 'app' => 'api/v1/mobile/storefront']);

    it('lists the delivery fields in the chooser and shows the server field errors', function (): void {
        $product = storefrontProduct('Game top-up', '10');
        $definition = DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID', 'input_type' => DeliveryFieldDefinitionInputType::Text]);
        ProductDeliveryField::factory()->create(['product_id' => $product->id, 'delivery_field_definition_id' => $definition->id, 'is_required' => true]);

        $card = collect(statelessCall('GET', 'api/v1/storefront/catalog/products')->json('data'))->firstWhere('slug', $product->slug);
        expect($card['quick_add'])->toBe(['variant_count' => 1, 'variant_id' => null, 'requires_fields' => true]);

        $this->getJson("/api/v1/storefront/catalog/products/{$product->slug}/quick-add")->assertOk()
            ->assertJsonPath('data.delivery_fields.0.key', 'player_id')
            ->assertJsonPath('data.delivery_fields.0.required', true);

        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($product)->public_id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('delivery_values.player_id');

        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($product)->public_id, 'quantity' => 1, 'delivery_values' => ['player_id' => 'P-1234']])->assertCreated();
    });

    it('is 404 for the chooser of a product that is not listed', function (): void {
        $this->getJson('/api/v1/storefront/catalog/products/no-such-product/quick-add')->assertNotFound();
    });

    it('renders the chooser dialog with its Arabic labels', function (): void {
        storefrontProduct();

        $this->get('/ar/products')->assertOk()->assertSee('data-quick-add-dialog', false)->assertSee('اختر خيارًا')->assertSee('أضف إلى الحقيبة');
    });
});

describe('a bag of many products', function (): void {
    it('keeps product A, product B and A’s other variant as three lines and counts them for the header', function (): void {
        $this->actingAs(User::factory()->create());
        $a = storefrontProduct('Product A', '10');
        $b = storefrontProduct('Product B', '20');
        $aOther = cardVariant($a->id, '30', ['title' => 'A large']);

        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($a)->public_id, 'quantity' => 1])->assertCreated()
            ->assertJsonCount(1, 'data.cart.items');
        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $b->productVariants()->sole()->public_id, 'quantity' => 2])->assertCreated()
            ->assertJsonCount(2, 'data.cart.items');
        $cart = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $aOther->public_id, 'quantity' => 1])->assertCreated()
            ->assertJsonCount(3, 'data.cart.items')
            ->json('data.cart');

        expect(array_column(array_column($cart['items'], 'product'), 'name'))->toBe(['Product A', 'Product B', 'Product A'])
            ->and(array_sum(array_column($cart['items'], 'quantity')))->toBe(4);

        // Adding A's first variant again raises its line instead of replacing the bag.
        $again = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($a)->public_id, 'quantity' => 1])->assertCreated()->json('data.cart.items');
        expect($again)->toHaveCount(3)->and(array_sum(array_column($again, 'quantity')))->toBe(5);

        $this->get('/products')->assertOk()->assertSee('data-bag-count aria-hidden="true">5<', false);
        $this->getJson('/api/v1/storefront/cart')->assertOk()->assertJsonCount(3, 'data.cart.items');
    });

    it('wires the header count to every cart response in the card and product modules', function (): void {
        expect(file_get_contents(resource_path('js/modules/quick-add.js')))->toContain('updateBagCount(result.body?.data?.cart)')
            ->and(file_get_contents(resource_path('js/modules/product.js')))->toContain('updateBagCount(result.body?.data?.cart)')
            ->and(file_get_contents(resource_path('js/lib/bag.js')))->toContain("document.querySelectorAll('[data-bag-count]')");
    });
});
