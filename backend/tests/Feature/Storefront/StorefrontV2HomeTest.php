<?php

/**
 * Storefront v2 home (Figma "Home v2"): hero, category tiles, brand logos, best sellers, new
 * arrivals, the other managed sections, then one rail per category with "Show on home page" by
 * home position and of its item limit. Every product, tile and logo section has "See all" with a
 * computed target, on the page and in the home JSON.
 */

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Orders\Models\OrderItem;
use Database\Seeders\HomepageSectionSeeder;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

/**
 * The v2 home: the seeded default sections plus an on-sale rail, two categories with rails.
 */
function sv2Home(): void
{
    test()->seed(HomepageSectionSeeder::class);
    HomepageSection::query()->whereNotIn('key', ['hero', 'category_tiles', 'featured_platforms', 'best_sellers', 'recently_added'])->update(['is_active' => false]);
    HomepageSection::factory()->create(['key' => 'weekend_deals', 'title' => 'Weekend deals', 'source' => HomepageSectionSource::Auto, 'sort_order' => 60, 'settings' => ['type' => 'product_rail', 'rule' => 'on_sale', 'limit' => 4]]);

    $steam = storefrontProduct('Steam Wallet $20', '20', ['sales_count' => 5]);
    $shopping = Category::factory()->create(['name' => 'Shopping', 'slug' => 'shopping', 'show_on_home' => true, 'home_position' => 2, 'home_limit' => 4]);
    $entertainment = Category::factory()->create(['name' => 'Entertainment', 'slug' => 'entertainment', 'show_on_home' => true, 'home_position' => 1]);
    Category::query()->where('slug', 'gift-cards')->update(['show_on_home' => false]);

    foreach (range(1, 6) as $index) {
        storefrontProduct('Shop card '.$index, (string) (10 + $index), ['primary_category_id' => $shopping->id, 'sales_count' => $index]);
    }

    storefrontProduct('Movie pass', '9', ['primary_category_id' => $entertainment->id], ['compare_at_base' => '12']);
}

/**
 * Section headings in page order.
 *
 * @return list<string>
 */
function sv2Headings(string $html): array
{
    preg_match_all('/<h2\s+id="section-[^"]+"[^>]*>([^<]+)<\/h2>/', $html, $matches);

    return array_map('html_entity_decode', $matches[1]);
}

it('renders hero, tiles, logos, best sellers, new arrivals, managed sections, then category rails by home position', function (): void {
    sv2Home();

    $html = $this->get('/')->assertOk()->assertSee('data-hero-toggle', false)->getContent();

    expect(sv2Headings($html))->toBe(['Shop by category', 'Top brands', 'Best sellers', 'New arrivals', 'Weekend deals', 'Entertainment', 'Shopping'])
        ->and(strpos($html, 'data-hero-track'))->toBeLessThan(strpos($html, 'id="section-category_tiles"'));
});

it('gives every section a computed See all target', function (): void {
    sv2Home();

    $response = $this->get('/')->assertOk();

    foreach ([
        'category_tiles' => url('/products'),
        'featured_platforms' => url('/products'),
        'best_sellers' => url('/products').'?sort=best_selling',
        'recently_added' => url('/products').'?sort=newest',
        'weekend_deals' => url('/products').'?on_sale=1',
        'category_shopping' => url('/c/shopping'),
        'category_entertainment' => url('/c/entertainment'),
    ] as $key => $href) {
        expect($response->getContent())->toMatch('~id="section-'.preg_quote($key, '~').'"[^>]*>[^<]*</h2>\s*</div>\s*<a href="'.preg_quote($href, '~').'"~');
    }
});

it('limits a category rail to its item limit and shows only on-sale products in the sale rail', function (): void {
    sv2Home();

    $sections = collect(statelessCall('GET', 'api/v1/storefront/home')->assertOk()->json('data.sections'))->keyBy('key');

    expect($sections['category_shopping']['items'])->toHaveCount(4)
        ->and(array_column(array_column($sections['category_shopping']['items'], 'data'), 'name'))->toBe(['Shop card 6', 'Shop card 5', 'Shop card 4', 'Shop card 3'])
        ->and(array_column(array_column($sections['weekend_deals']['items'], 'data'), 'name'))->toBe(['Movie pass']);
});

it('returns the same order, types and See all targets in the home JSON for the web and the app', function (string $path): void {
    sv2Home();
    $base = rtrim((string) config('app.url'), '/');

    $sections = statelessCall('GET', $path)->assertOk()->json('data.sections');

    expect(array_column($sections, 'key'))->toBe(['hero', 'category_tiles', 'featured_platforms', 'best_sellers', 'recently_added', 'weekend_deals', 'category_entertainment', 'category_shopping'])
        ->and(array_column($sections, 'type'))->toBe(['hero_banners', 'category_tiles', 'brand_logos', 'product_rail', 'product_rail', 'product_rail', 'product_rail', 'product_rail'])
        ->and($sections[0]['see_all'])->toBeNull()
        ->and($sections[1]['see_all'])->toBe(['type' => 'listing', 'value' => '', 'url' => $base.'/products'])
        ->and($sections[3]['see_all'])->toBe(['type' => 'listing', 'value' => 'sort=best_selling', 'url' => $base.'/products?sort=best_selling'])
        ->and($sections[4]['see_all'])->toBe(['type' => 'listing', 'value' => 'sort=newest', 'url' => $base.'/products?sort=newest'])
        ->and($sections[5]['see_all'])->toBe(['type' => 'listing', 'value' => 'on_sale=1', 'url' => $base.'/products?on_sale=1'])
        ->and($sections[6])->toMatchArray(['source' => 'category', 'category' => 'entertainment', 'title' => 'Entertainment', 'see_all' => ['type' => 'category', 'value' => 'entertainment', 'url' => $base.'/c/entertainment']])
        ->and(collect($sections[1]['items'])->firstWhere('data.slug', 'gift-cards')['data']['product_count'])->toBe(1)
        ->and(collect($sections[1]['items'])->firstWhere('data.slug', 'shopping')['data']['product_count'])->toBe(6);
})->with(['web' => 'api/v1/storefront/home', 'app' => 'api/v1/mobile/storefront/home']);

it('links category and brand rules to their pages and ranks best sellers of a period by paid units', function (): void {
    storefrontProduct('Old favourite', '10', ['sales_count' => 99]);
    $recent = storefrontProduct('Hot this week', '10', ['sales_count' => 1]);
    paidOrderFor(ProductVariant::query()->where('product_id', $recent->id)->sole(), quantity: 3);
    $xbox = GamingPlatform::factory()->create(['slug' => 'xbox', 'name' => 'Xbox']);

    HomepageSection::factory()->create(['key' => 'week', 'title' => 'This week', 'source' => HomepageSectionSource::Auto, 'sort_order' => 1, 'settings' => ['type' => 'product_rail', 'rule' => 'best_sellers', 'period' => '7d']]);
    HomepageSection::factory()->create(['key' => 'by_category', 'title' => 'Gift cards', 'source' => HomepageSectionSource::Auto, 'sort_order' => 2, 'settings' => ['type' => 'product_rail', 'rule' => 'category', 'category' => 'gift-cards']]);
    HomepageSection::factory()->create(['key' => 'by_brand', 'title' => 'Xbox', 'source' => HomepageSectionSource::Auto, 'sort_order' => 3, 'settings' => ['type' => 'product_rail', 'rule' => 'brand', 'platform' => $xbox->slug]]);

    $sections = collect(statelessCall('GET', 'api/v1/storefront/home')->assertOk()->json('data.sections'))->keyBy('key');

    expect(array_column(array_column($sections['week']['items'], 'data'), 'name'))->toBe(['Hot this week', 'Old favourite'])
        ->and($sections['week']['see_all']['value'])->toBe('sort=best_selling')
        ->and($sections['by_category']['see_all'])->toMatchArray(['type' => 'category', 'value' => 'gift-cards'])
        ->and($sections['by_brand']['see_all'])->toMatchArray(['type' => 'platform', 'value' => 'xbox']);

    expect((int) OrderItem::query()->where('product_id', $recent->id)->sum('quantity'))->toBe(3);
});

it('renders the home in Arabic with translated rail titles and Arabic See all links', function (): void {
    sv2Home();
    $shopping = Category::query()->firstWhere('slug', 'shopping');
    ContentTranslation::factory()->create(['translatable_type' => 'category', 'translatable_id' => $shopping->id, 'locale' => 'ar', 'field' => 'name', 'value' => 'التسوق']);

    $response = $this->get('/ar')->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('التسوق')
        ->assertSee('تسوق حسب الفئة')
        ->assertSee('href="'.url('/ar/c/shopping').'"', false)
        ->assertSee('href="'.url('/ar/products').'?sort=newest"', false)
        ->assertSee('عرض الكل');

    assertCspSafeHtml($response);
});

it('fills category tiles and brand logos from the catalog when they have no manual items', function (): void {
    sv2Home();

    $this->get('/')->assertOk()
        ->assertSee('id="categories"', false)
        ->assertSee('href="'.url('/c/shopping').'"', false)
        ->assertSee('6 products')
        ->assertSee('href="'.url('/b/steam').'"', false);

    expect(Product::query()->count())->toBe(8);
});
