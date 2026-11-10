<?php

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\SearchQuery;
use App\Domains\Catalog\Services\SearchIndexer;
use Database\Factories\Currency\CurrencyFactory;

afterEach(function (): void {
    if (DB::transactionLevel() === 0) {
        foreach (['search_documents', 'search_queries', 'product_attribute_values', 'attribute_definitions', 'product_variants', 'products', 'gaming_platforms', 'categories', 'currencies'] as $table) {
            DB::table($table)->delete();
        }

        reopenTestTransaction();
    }
});

function suggestionTitles(string $term): array
{
    return collect(test()->getJson('/api/v1/storefront/search/suggest?q='.urlencode($term))->assertOk()->json('data.suggestions'))->pluck('title')->all();
}

function indexedCatalog(): Product
{
    CurrencyFactory::ensure('USD');
    $platform = GamingPlatform::factory()->create(['name' => 'Orbit Network', 'slug' => 'orbit-network']);
    $category = Category::factory()->create(['name' => 'Gift Cards']);
    $product = Product::factory()->create([
        'name' => 'Starfall Credits',
        'sku' => 'SFC-100',
        'status' => ProductStatus::Active,
        'gaming_platform_id' => $platform->id,
        'primary_category_id' => $category->id,
    ]);
    ProductVariant::factory()->for($product)->create(['sku' => 'SFC-100-EU-25', 'title' => '25 credits']);
    ProductAttributeValue::factory()->create(['product_id' => $product->id, 'value' => 'Galaxy Warriors']);

    app(SearchIndexer::class)->rebuild();

    return $product;
}

describe('with committed data, because InnoDB fulltext only reads committed rows', function (): void {
    beforeEach(fn () => commitTestTransaction());

    it('finds a product by a word in its name', function (): void {
        indexedCatalog();

        expect(suggestionTitles('credits'))->toBe(['Starfall Credits']);
    });

    it('finds a product by its platform', function (): void {
        indexedCatalog();

        expect(suggestionTitles('network'))->toContain('Starfall Credits', 'Orbit Network');
    });

    it('finds a product by its game attribute', function (): void {
        indexedCatalog();

        expect(suggestionTitles('warriors'))->toBe(['Starfall Credits']);
    });

    it('survives a term that is only boolean operators', function (): void {
        indexedCatalog();

        expect(suggestionTitles('+-*()'))->toBe([]);
    });
});

it('finds by title prefix, product sku and variant sku', function (): void {
    indexedCatalog();

    expect(suggestionTitles('Starf'))->toContain('Starfall Credits')
        ->and(suggestionTitles('SFC-100'))->toBe(['Starfall Credits'])
        ->and(suggestionTitles('SFC-100-EU'))->toBe(['Starfall Credits']);
});

it('escapes LIKE wildcards', function (): void {
    indexedCatalog();

    expect(suggestionTitles('%%%'))->toBe([]);
});

it('hides inactive products', function (): void {
    $product = indexedCatalog();
    $product->forceFill(['status' => ProductStatus::Inactive])->save();
    app(SearchIndexer::class)->indexProduct($product->refresh());

    expect(suggestionTitles('Starf'))->toBe([]);
});

it('keeps a search document for each launch locale', function (): void {
    $product = indexedCatalog();

    expect(DB::table('search_documents')->where('searchable_type', 'product')->where('searchable_id', $product->id)->pluck('locale')->sort()->values()->all())
        ->toBe(['ar', 'en']);
});

it('rejects too short a term', function (): void {
    $this->getJson('/api/v1/storefront/search/suggest?q=a')->assertUnprocessable();
});

it('records a search only when committed and counts repeats', function (): void {
    indexedCatalog();

    $this->getJson('/api/v1/storefront/search/suggest?q=Starf')->assertOk();
    expect(SearchQuery::query()->count())->toBe(0);

    $this->getJson('/api/v1/storefront/search/suggest?q=Starf&commit=1')->assertOk();
    $this->getJson('/api/v1/storefront/search/suggest?q=STARF&commit=1')->assertOk();

    expect(SearchQuery::query()->sole()->hits)->toBe(2);
});

it('returns popular terms that found something', function (): void {
    SearchQuery::factory()->create(['term' => 'steam', 'locale' => 'en', 'hits' => 5, 'results_count' => 3]);
    SearchQuery::factory()->create(['term' => 'nothing', 'locale' => 'en', 'hits' => 50, 'results_count' => 0]);

    $this->getJson('/api/v1/storefront/search/popular')->assertOk()->assertJsonPath('data.terms', ['steam']);
});

it('throttles the search endpoints', function (): void {
    $route = app('router')->getRoutes()->getByName('api.v1.storefront.search.suggest');

    expect($route->gatherMiddleware())->toContain('throttle:search');
});
