<?php

/**
 * Storefront v2 facets and multi-value listing in the JSON API (web `/api/v1/storefront` and the
 * app `/api/v1/mobile/storefront`, same controllers): per facet value, the products the listing
 * shows with the other facets applied; the price bounds in the display currency; `hide` for the
 * facets a category or brand page pins.
 */

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use Database\Factories\Currency\CurrencyFactory;

dataset('storefront apis', ['web' => 'api/v1/storefront', 'app' => 'api/v1/mobile/storefront']);

/**
 * @param  array<string, mixed>  $variant
 */
function facetApiProduct(string $name, string $price, string $category, string $brand, string $region, array $product = [], array $variant = []): Product
{
    CurrencyFactory::ensure('USD');

    $model = Product::factory()->create([
        'status' => ProductStatus::Active,
        'name' => $name,
        'primary_category_id' => (Category::query()->firstWhere('slug', $category) ?? Category::factory()->create(['slug' => $category, 'name' => ucfirst($category)]))->id,
        'gaming_platform_id' => (GamingPlatform::query()->firstWhere('slug', $brand) ?? GamingPlatform::factory()->create(['slug' => $brand, 'name' => ucfirst($brand)]))->id,
        ...$product,
    ]);

    ProductVariant::factory()->for($model)->create([
        'region_id' => (Region::query()->firstWhere('code', $region) ?? Region::factory()->create(['code' => $region, 'name' => $region]))->id,
        'availability' => ProductVariantAvailability::InStock,
        ...$variant,
    ])->forceFill(['price_base' => $price])->save();

    return $model;
}

function facetApiCatalog(): void
{
    facetApiProduct('Steam 10', '10', 'gaming', 'steam', 'US', ['sales_count' => 9]);
    facetApiProduct('Steam 50', '50', 'gaming', 'steam', 'EU', ['sales_count' => 3]);
    facetApiProduct('Xbox 25', '25', 'gaming', 'xbox', 'US', ['delivery_type' => DeliveryType::DirectTopup, 'sales_count' => 5]);
    facetApiProduct('Amazon 100', '100', 'shopping', 'amazon', 'US', ['sales_count' => 1], ['availability' => ProductVariantAvailability::OutOfStock]);
}

/**
 * @return array<string, mixed> facet key => facet
 */
function facetsOf(string $base, string $query = '', array $headers = []): array
{
    return collect(statelessCall('GET', "{$base}/catalog/facets".($query === '' ? '' : '?'.$query), headers: $headers)->assertOk()->json('data.facets'))->keyBy('key')->all();
}

/**
 * @return array<string, int>
 */
function optionCounts(array $facet): array
{
    $counts = array_column($facet['options'], 'count', 'value');
    ksort($counts);

    return $counts;
}

it('counts every facet value with the other facets applied', function (string $base): void {
    facetApiCatalog();

    $all = facetsOf($base);
    expect(array_keys($all))->toBe(['category', 'platform', 'region', 'price', 'delivery_type', 'in_stock'])
        ->and(optionCounts($all['category']))->toBe(['gaming' => 3, 'shopping' => 1])
        ->and(optionCounts($all['platform']))->toBe(['amazon' => 1, 'steam' => 2, 'xbox' => 1])
        ->and(optionCounts($all['region']))->toBe(['EU' => 1, 'US' => 3])
        ->and(optionCounts($all['delivery_type']))->toBe(['direct_topup' => 1, 'instant_code' => 3])
        ->and($all['in_stock'])->toMatchArray(['count' => 3, 'selected' => false])
        ->and($all['price'])->toMatchArray(['currency' => 'USD', 'min' => '10', 'max' => '100']);

    $steam = facetsOf($base, 'platform=steam&region=US');
    expect(optionCounts($steam['platform']))->toBe(['amazon' => 1, 'steam' => 1, 'xbox' => 1])
        ->and(optionCounts($steam['region']))->toBe(['EU' => 1, 'US' => 1])
        ->and(optionCounts($steam['category']))->toBe(['gaming' => 1])
        ->and(collect($steam['platform']['options'])->firstWhere('value', 'steam')['selected'])->toBeTrue()
        ->and(statelessCall('GET', "{$base}/catalog/facets?platform=steam&region=US")->json('data.total'))->toBe(1)
        ->and(statelessCall('GET', "{$base}/catalog/facets?platform=steam&region=US")->json('data.selected'))->toBe(2);
})->with('storefront apis');

it('lists several values per facet, in stock only, on sale and best selling', function (string $base): void {
    facetApiCatalog();
    facetApiProduct('Sale 20', '20', 'shopping', 'amazon', 'EU', ['sales_count' => 0], ['compare_at_base' => '25']);

    $names = fn (string $query): array => collect(statelessCall('GET', "{$base}/catalog/products?{$query}")->assertOk()->json('data'))->pluck('name')->all();

    expect($names('platform=steam,xbox&sort=best_selling'))->toBe(['Steam 10', 'Xbox 25', 'Steam 50'])
        ->and($names('platform[]=steam&platform[]=amazon&region=US'))->toBe(['Steam 10', 'Amazon 100'])
        ->and($names('category=shopping&in_stock=1'))->toBe(['Sale 20'])
        ->and($names('on_sale=1'))->toBe(['Sale 20'])
        ->and($names('delivery_type=instant_code,direct_topup&region=EU'))->toBe(['Steam 50', 'Sale 20']);

    statelessCall('GET', "{$base}/catalog/products?delivery_type=teleport")->assertUnprocessable()->assertJsonValidationErrors('delivery_type.0');
})->with('storefront apis');

it('converts the price bounds to the display currency and the typed range back to base', function (string $base): void {
    facetApiCatalog();
    givenRate('EGP', '50');

    $facets = facetsOf($base, 'min_price=1000&max_price=1500', ['X-Currency' => 'EGP']);

    expect($facets['price'])->toMatchArray(['currency' => 'EGP', 'min' => '500', 'max' => '5000', 'selected_min' => '1000', 'selected_max' => '1500'])
        ->and(statelessCall('GET', "{$base}/catalog/facets?min_price=1000&max_price=1500", headers: ['X-Currency' => 'EGP'])->json('data.total'))->toBe(1)
        ->and(collect(statelessCall('GET', "{$base}/catalog/products?min_price=1000&max_price=1500", headers: ['X-Currency' => 'EGP'])->json('data'))->pluck('name')->all())->toBe(['Xbox 25']);
})->with('storefront apis');

it('leaves out the facets a category or brand page pins', function (string $base): void {
    facetApiCatalog();

    expect(array_keys(facetsOf($base, 'category=gaming&hide=category')))->toBe(['platform', 'region', 'price', 'delivery_type', 'in_stock'])
        ->and(array_keys(facetsOf($base, 'platform=steam&hide=category,platform')))->toBe(['region', 'price', 'delivery_type', 'in_stock']);
})->with('storefront apis');

it('returns the navigation with categories, top brands, featured categories and links', function (string $base): void {
    facetApiCatalog();
    Category::query()->where('slug', 'shopping')->update(['featured_in_header' => true, 'header_order' => 1]);

    $data = statelessCall('GET', "{$base}/navigation")->assertOk()->json('data');
    $gaming = collect($data['categories'])->firstWhere('slug', 'gaming');

    expect($data['total'])->toBe(4)
        ->and($data['featured'])->toHaveCount(1)
        ->and($data['featured'][0]['slug'])->toBe('shopping')
        ->and($data['all_products']['type'])->toBe('listing')
        ->and($gaming['product_count'])->toBe(3)
        ->and(array_column($gaming['brands'], 'slug'))->toBe(['steam', 'xbox'])
        ->and($gaming['link']['url'])->toEndWith('/c/gaming')
        ->and($gaming['brands'][0]['link']['url'])->toEndWith('/b/steam');
})->with('storefront apis');
