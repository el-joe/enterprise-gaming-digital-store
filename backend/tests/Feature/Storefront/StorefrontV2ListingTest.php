<?php

/**
 * Storefront v2 listing (docs/STOREFRONT_IA.md, Figma "All products"): `/products`, `/c/{slug}`
 * and `/b/{slug}` with side facets and their counts (each counted with the other facets
 * applied), combinations, sort, a price range typed in another display currency, crawlable
 * pagination, canonical and noindex (SEO_STRATEGY.md section 2), hidden facets on category and
 * brand pages, and English and Arabic rendering.
 */

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

/**
 * A listed product in a category and brand, with one variant in a region.
 *
 * @param  array<string, mixed>  $product
 * @param  array<string, mixed>  $variant
 */
function sv2Product(string $name, string $price, string $category, string $brand, ?string $region = null, array $product = [], array $variant = []): Product
{
    CurrencyFactory::ensure('USD');

    $categoryModel = Category::query()->firstWhere('slug', $category) ?? Category::factory()->create(['slug' => $category, 'name' => ucfirst($category)]);
    $brandModel = GamingPlatform::query()->firstWhere('slug', $brand) ?? GamingPlatform::factory()->create(['slug' => $brand, 'name' => ucfirst($brand)]);
    $regionModel = $region === null ? null : (Region::query()->firstWhere('code', $region) ?? Region::factory()->create(['code' => $region, 'name' => 'Region '.$region]));

    $model = Product::factory()->create([
        'status' => ProductStatus::Active,
        'name' => $name,
        'gaming_platform_id' => $brandModel->id,
        'primary_category_id' => $categoryModel->id,
        ...$product,
    ]);

    ProductVariant::factory()->for($model)->create([
        'title' => $name.' variant',
        'region_id' => $regionModel?->id,
        'availability' => ProductVariantAvailability::InStock,
        ...$variant,
    ])->forceFill(['price_base' => $price])->save();

    return $model;
}

/**
 * The store of these tests: two categories, three brands, two regions, two delivery types.
 */
function sv2Catalog(): void
{
    sv2Product('Steam Wallet 10', '10', 'gaming', 'steam', 'US', ['sales_count' => 50, 'published_at' => now()->subDays(5)]);
    sv2Product('Steam Wallet 50', '50', 'gaming', 'steam', 'EU', ['sales_count' => 10, 'published_at' => now()->subDays(1)]);
    sv2Product('Xbox Live 25', '25', 'gaming', 'xbox', 'US', ['sales_count' => 30, 'delivery_type' => DeliveryType::DirectTopup, 'published_at' => now()->subDays(3)]);
    sv2Product('Amazon Card 100', '100', 'shopping', 'amazon', 'US', ['sales_count' => 5, 'published_at' => now()->subDays(10)], ['availability' => ProductVariantAvailability::OutOfStock]);
}

/**
 * Option counts of one facet in the sidebar, by value: `[value => count]`.
 *
 * @return array<string, int>
 */
function sv2FacetCounts(TestResponse $response, string $facet): array
{
    preg_match_all('/<input id="f-'.$facet.'-[^"]*" type="checkbox" name="'.$facet.'\[\]" value="([^"]+)"[^>]*>\s*<span[^>]*>[^<]*<\/span>\s*<span[^>]*>([\d,]+)<\/span>/', $response->getContent(), $matches, PREG_SET_ORDER);

    $counts = [];

    foreach ($matches as [, $value, $count]) {
        $counts[$value] = (int) str_replace(',', '', $count);
    }

    ksort($counts);

    return $counts;
}

/**
 * Product names in the order the grid shows them.
 *
 * @return list<string>
 */
function sv2Names(TestResponse $response): array
{
    preg_match_all('/<h3 class="line-clamp-2[^"]*">\s*<a [^>]*>([^<]+)<\/a>/', $response->getContent(), $matches);

    return array_map('html_entity_decode', $matches[1]);
}

/**
 * @return array{canonical: ?string, robots: ?string}
 */
function sv2Head(TestResponse $response): array
{
    $html = $response->getContent();
    preg_match('/<link rel="canonical" href="([^"]+)">/', $html, $canonical);
    preg_match('/<meta name="robots" content="([^"]+)">/', $html, $robots);

    return ['canonical' => isset($canonical[1]) ? html_entity_decode($canonical[1]) : null, 'robots' => $robots[1] ?? null];
}

describe('all products', function (): void {
    it('lists every listed product with a count per value of each facet and a strict CSP', function (): void {
        sv2Catalog();

        $response = $this->get('/products')->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('All products')
            ->assertSee('4 results')
            ->assertSee('data-facet="category"', false)
            ->assertSee('data-facet="platform"', false)
            ->assertSee('data-facet="region"', false)
            ->assertSee('data-facet="price"', false)
            ->assertSee('data-facet="delivery_type"', false)
            ->assertSee('data-facet="in_stock"', false);

        expect(sv2FacetCounts($response, 'category'))->toBe(['gaming' => 3, 'shopping' => 1])
            ->and(sv2FacetCounts($response, 'platform'))->toBe(['amazon' => 1, 'steam' => 2, 'xbox' => 1])
            ->and(sv2FacetCounts($response, 'region'))->toBe(['EU' => 1, 'US' => 3])
            ->and(sv2FacetCounts($response, 'delivery_type'))->toBe(['direct_topup' => 1, 'instant_code' => 3])
            ->and(sv2Names($response))->toBe(['Steam Wallet 10', 'Xbox Live 25', 'Steam Wallet 50', 'Amazon Card 100']);

        expect(strpos($response->getContent(), 'data-facet="category"'))->toBeLessThan(strpos($response->getContent(), 'data-facet="platform"'))
            ->and(strpos($response->getContent(), 'data-facet="region"'))->toBeLessThan(strpos($response->getContent(), 'data-facet="price"'))
            ->and(strpos($response->getContent(), 'data-facet="price"'))->toBeLessThan(strpos($response->getContent(), 'data-facet="delivery_type"'));

        assertCspSafeHtml($response);
    });

    it('counts each facet with the other facets applied and combines values inside and across facets', function (): void {
        sv2Catalog();

        $brand = $this->get('/products?platform=steam')->assertOk()->assertSee('2 results');
        expect(sv2Names($brand))->toBe(['Steam Wallet 10', 'Steam Wallet 50'])
            // The brand facet ignores its own selection; the others count inside Steam.
            ->and(sv2FacetCounts($brand, 'platform'))->toBe(['amazon' => 1, 'steam' => 2, 'xbox' => 1])
            ->and(sv2FacetCounts($brand, 'category'))->toBe(['gaming' => 2])
            ->and(sv2FacetCounts($brand, 'region'))->toBe(['EU' => 1, 'US' => 1]);

        // OR inside a facet (comma list or repeated values), AND across facets.
        $both = $this->get('/products?platform=steam,xbox&region=US')->assertOk()->assertSee('2 results');
        expect(sv2Names($both))->toBe(['Steam Wallet 10', 'Xbox Live 25'])
            ->and(sv2FacetCounts($both, 'region'))->toBe(['EU' => 1, 'US' => 2])
            ->and(sv2FacetCounts($both, 'delivery_type'))->toBe(['direct_topup' => 1, 'instant_code' => 1]);

        expect(sv2Names($this->get('/products?platform[]=steam&platform[]=amazon&delivery_type=instant_code')->assertOk()))
            ->toBe(['Steam Wallet 10', 'Steam Wallet 50', 'Amazon Card 100']);

        $stock = $this->get('/products?in_stock=1')->assertOk()->assertSee('3 results')->assertDontSee('Amazon Card 100');
        expect(sv2FacetCounts($stock, 'category'))->toBe(['gaming' => 3]);
    });

    it('shows applied chips that each remove one value, and Clear all', function (): void {
        sv2Catalog();

        $this->get('/products?platform=steam,xbox&in_stock=1')->assertOk()
            ->assertSee('data-filter-chip', false)
            ->assertSee('href="'.url('/products').'?platform=xbox&amp;in_stock=1"', false)
            ->assertSee('href="'.url('/products').'?platform=steam&amp;in_stock=1"', false)
            ->assertSee('href="'.url('/products').'?platform=steam,xbox"', false)
            ->assertSee('Clear all');
    });

    it('sorts by best selling (default), newest and price both ways', function (string $sort, array $names): void {
        sv2Catalog();

        expect(sv2Names($this->get('/products'.($sort === '' ? '' : '?sort='.$sort))->assertOk()))->toBe($names);
    })->with([
        'best selling (default)' => ['', ['Steam Wallet 10', 'Xbox Live 25', 'Steam Wallet 50', 'Amazon Card 100']],
        'best selling' => ['best_selling', ['Steam Wallet 10', 'Xbox Live 25', 'Steam Wallet 50', 'Amazon Card 100']],
        'newest' => ['newest', ['Steam Wallet 50', 'Xbox Live 25', 'Steam Wallet 10', 'Amazon Card 100']],
        'price low to high' => ['price_asc', ['Steam Wallet 10', 'Xbox Live 25', 'Steam Wallet 50', 'Amazon Card 100']],
        'price high to low' => ['price_desc', ['Amazon Card 100', 'Steam Wallet 50', 'Xbox Live 25', 'Steam Wallet 10']],
    ]);

    it('filters a price range typed in the display currency, converted to base at the current rate', function (): void {
        sv2Catalog();
        givenRate('EGP', '50');

        // 1,000–1,500 EGP is 20–30 USD: only the 25 USD card.
        $response = $this->withUnencryptedCookie('currency', 'EGP')->get('/products?min_price=1000&max_price=1500')->assertOk()
            ->assertSee('1 result')
            ->assertSee('Prices in EGP, your display currency')
            ->assertSee('value="1000"', false);

        expect(sv2Names($response))->toBe(['Xbox Live 25']);

        // The slider bounds are whole EGP amounts around the matching variants (10 to 100 USD).
        expect($response->getContent())->toContain('data-min="500" data-max="5000"');
    });

    it('paginates with crawlable page links that keep the filters', function (): void {
        config(['storefront.per_page' => 2]);
        sv2Catalog();

        $first = $this->get('/products?platform=steam,xbox')->assertOk()
            ->assertSee('href="'.url('/products').'?platform=steam,xbox&amp;page=2"', false)
            ->assertSee('rel="next"', false)
            ->assertSee('Showing 1–2 of 3');
        expect(sv2Names($first))->toHaveCount(2);

        $second = $this->get('/products?platform=steam,xbox&page=2')->assertOk()->assertSee('Showing 3–3 of 3')->assertSee('rel="prev"', false);
        expect(sv2Names($second))->toBe(['Steam Wallet 50']);

        expect(sv2Head($this->get('/products?page=2')->assertOk()))->toBe(['canonical' => url('/products').'?page=2', 'robots' => 'index,follow']);
    });

    it('canonicalizes one filter or a sort to the plain page and marks two or more noindex,follow', function (): void {
        sv2Catalog();

        expect(sv2Head($this->get('/products')->assertOk()))->toBe(['canonical' => url('/products'), 'robots' => 'index,follow'])
            ->and(sv2Head($this->get('/products?platform=steam')->assertOk()))->toBe(['canonical' => url('/products'), 'robots' => 'index,follow'])
            ->and(sv2Head($this->get('/products?sort=newest')->assertOk()))->toBe(['canonical' => url('/products'), 'robots' => 'index,follow'])
            ->and(sv2Head($this->get('/products?platform=steam,xbox')->assertOk()))->toBe(['canonical' => null, 'robots' => 'noindex,follow'])
            ->and(sv2Head($this->get('/products?platform=steam&in_stock=1')->assertOk()))->toBe(['canonical' => null, 'robots' => 'noindex,follow']);
    });

    it('shows the empty state with Clear all filters and Browse all categories', function (): void {
        sv2Catalog();

        $response = $this->get('/products?min_price=900')->assertOk()
            ->assertSee('No products match these filters')
            ->assertSee('Clear all filters')
            ->assertSee('Browse all categories')
            ->assertSee('href="'.url('/').'#categories"', false);

        expect(sv2Head($response)['robots'])->toBe('noindex,follow');
    });

    it('renders the skeleton grid template and the mobile Filters, Sort and Apply controls', function (): void {
        sv2Catalog();

        $this->get('/products')->assertOk()
            ->assertSee('<template data-skeleton>', false)
            ->assertSee('data-filter-open', false)
            ->assertSee('Apply (4 results)')
            ->assertSee('data-facets-url="'.url('/api/v1/storefront/catalog/facets').'"', false);
    });

    it('rejects an unknown sort or delivery type back to the plain page', function (): void {
        $this->get('/products?sort=cheapest')->assertRedirect(url('/products'));
        $this->get('/products?delivery_type=teleport')->assertRedirect(url('/products'));
    });
});

describe('category and brand pages', function (): void {
    it('hides the Category facet on a category page and shows its header with top brands', function (): void {
        sv2Catalog();
        Category::query()->where('slug', 'gaming')->update(['description' => 'Wallet codes for every console.']);

        $response = $this->get('/c/gaming')->assertOk()
            ->assertSee('Wallet codes for every console.')
            ->assertSee('3 results')
            ->assertDontSee('data-facet="category"', false)
            ->assertSee('data-facet="platform"', false)
            ->assertSee('href="'.url('/b/steam').'"', false);

        expect(sv2FacetCounts($response, 'platform'))->toBe(['steam' => 2, 'xbox' => 1])
            ->and(sv2Names($response))->not->toContain('Amazon Card 100');
    });

    it('ignores a category in the query on a category page', function (): void {
        sv2Catalog();

        expect(sv2Names($this->get('/c/gaming?category=shopping')->assertOk()))->toBe(['Steam Wallet 10', 'Xbox Live 25', 'Steam Wallet 50']);
    });

    it('hides the Category and Brand facets on a brand page and shows the logo slot', function (): void {
        sv2Catalog();

        $response = $this->get('/b/steam')->assertOk()
            ->assertSee('2 results')
            ->assertSee('data-brand-logo', false)
            ->assertDontSee('data-facet="category"', false)
            ->assertDontSee('data-facet="platform"', false)
            ->assertSee('data-facet="region"', false);

        expect(sv2FacetCounts($response, 'region'))->toBe(['EU' => 1, 'US' => 1]);
        expect(sv2Head($this->get('/b/steam?region=US')->assertOk()))->toBe(['canonical' => url('/b/steam'), 'robots' => 'index,follow']);
    });
});

describe('languages', function (): void {
    it('renders the listing right to left in Arabic with translated facet names', function (): void {
        sv2Catalog();
        $gaming = Category::query()->firstWhere('slug', 'gaming');
        ContentTranslation::factory()->create(['translatable_type' => 'category', 'translatable_id' => $gaming->id, 'locale' => 'ar', 'field' => 'name', 'value' => 'الألعاب']);

        $response = $this->get('/ar/products')->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('كل المنتجات')
            ->assertSee('الفلاتر')
            ->assertSee('الألعاب')
            ->assertSee('العلامة التجارية')
            ->assertSee('ترتيب حسب')
            ->assertSee('rel="alternate" hreflang="en" href="'.url('/products').'"', false);

        assertCspSafeHtml($response);
        $this->get('/ar/c/gaming')->assertOk()->assertSee('الألعاب');
        $this->get('/ar/b/steam')->assertOk()->assertSee('dir="rtl"', false);
    });
});
