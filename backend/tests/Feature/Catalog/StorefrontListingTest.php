<?php

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Currency\Models\Currency;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Cache;

/** Upper bound on queries for one listing page with a cold cache, as documented in the API docs. */
const MAX_LISTING_QUERIES = 10;

function listedProduct(string $price = '10', array $product = [], array $variant = []): Product
{
    CurrencyFactory::ensure('USD');

    $model = Product::factory()->create(['status' => ProductStatus::Active, ...$product]);
    $created = ProductVariant::factory()->for($model)->create($variant);
    $created->forceFill(['price_base' => $price])->save();

    return $model;
}

function listingNames(array $query = []): array
{
    return collect(test()->getJson('/api/v1/storefront/catalog/products?'.http_build_query($query))->assertOk()->json('data'))->pluck('name')->all();
}

it('lists only active products with a priced active variant', function (): void {
    listedProduct(product: ['name' => 'Visible']);
    listedProduct(product: ['name' => 'Draft', 'status' => ProductStatus::Draft]);
    listedProduct(product: ['name' => 'Inactive variant'], variant: ['is_active' => false]);
    listedProduct(product: ['name' => 'No price'])->productVariants()->update(['price_base' => null]);

    expect(listingNames())->toBe(['Visible']);
});

it('filters by platform, category, secondary category, region and delivery type', function (): void {
    $platform = GamingPlatform::factory()->create(['slug' => 'orbit']);
    $category = Category::factory()->create(['slug' => 'gift-cards']);
    $secondary = Category::factory()->create(['slug' => 'wallets']);
    $region = Region::factory()->create(['code' => 'MENA']);

    listedProduct(product: ['name' => 'P', 'gaming_platform_id' => $platform->id]);
    listedProduct(product: ['name' => 'C', 'primary_category_id' => $category->id]);
    listedProduct(product: ['name' => 'S'])->categories()->attach($secondary);
    listedProduct(product: ['name' => 'R'], variant: ['region_id' => $region->id]);
    listedProduct(product: ['name' => 'D', 'delivery_type' => DeliveryType::DirectTopup]);

    expect(listingNames(['platform' => 'orbit']))->toBe(['P'])
        ->and(listingNames(['category' => 'gift-cards']))->toBe(['C'])
        ->and(listingNames(['category' => 'wallets']))->toBe(['S'])
        ->and(listingNames(['region' => 'MENA']))->toBe(['R'])
        ->and(listingNames(['delivery_type' => 'direct_topup']))->toBe(['D']);
});

it('filters by price and sorts', function (): void {
    listedProduct('5', ['name' => 'Cheap', 'sales_count' => 1]);
    listedProduct('50', ['name' => 'Dear', 'sales_count' => 9]);

    expect(listingNames(['min_price' => '10']))->toBe(['Dear'])
        ->and(listingNames(['max_price' => '10']))->toBe(['Cheap'])
        ->and(listingNames(['sort' => 'price_asc']))->toBe(['Cheap', 'Dear'])
        ->and(listingNames(['sort' => 'price_desc']))->toBe(['Dear', 'Cheap'])
        ->and(listingNames(['sort' => 'popular']))->toBe(['Dear', 'Cheap']);
});

it('rejects an unknown sort and a float price', function (): void {
    $this->getJson('/api/v1/storefront/catalog/products?sort=evil')->assertUnprocessable();
    $this->getJson('/api/v1/storefront/catalog/products?min_price=abc')->assertUnprocessable();
});

it('prices the listing in the display currency, flagging a stale rate', function (): void {
    listedProduct('10');
    givenRate('EGP', '48.5', ['fetched_at' => now()->subDays(3)]);
    Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
    Cache::flush();

    $this->withCredentials()->withUnencryptedCookie('currency', 'EGP')
        ->getJson('/api/v1/storefront/catalog/products')
        ->assertOk()
        ->assertJsonPath('meta.display_currency', 'EGP')
        ->assertJsonPath('data.0.price_from.amount', '485.00')
        ->assertJsonPath('data.0.price_from.currency', 'EGP')
        ->assertJsonPath('data.0.price_may_vary', true);
});

it('interprets filter prices in the display currency', function (): void {
    listedProduct('10', ['name' => 'Ten']);
    givenRate('EGP', '50');
    Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
    Cache::flush();

    $names = fn (string $min) => collect($this->withCredentials()->withUnencryptedCookie('currency', 'EGP')->getJson("/api/v1/storefront/catalog/products?min_price={$min}")->json('data'))->pluck('name')->all();

    expect($names('400'))->toBe(['Ten'])->and($names('600'))->toBe([]);
});

it('runs a bounded query count that does not grow with rows', function (): void {
    $seed = function (int $count): void {
        foreach (range(1, $count) as $unused) {
            $product = listedProduct();
            ProductImage::factory()->create(['product_id' => $product->id]);
        }
    };

    $count = function (): int {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->withCredentials()->withUnencryptedCookie('currency', 'EGP')->getJson('/api/v1/storefront/catalog/products?per_page=100&min_price=1&sort=price_asc')->assertOk();

        return count(DB::getQueryLog());
    };

    $seed(2);
    givenRate('EGP', '50');
    Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
    $few = $count();

    $seed(30);
    $many = $count();

    expect($many)->toBe($few)->toBeLessThanOrEqual(MAX_LISTING_QUERIES);
});
