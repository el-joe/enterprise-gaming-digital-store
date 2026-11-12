<?php

/**
 * Storefront v2 header, mega menu, footer and old URLs (docs/STOREFRONT_IA.md, Figma "Header
 * v2" and "Mega menu"): the v1 links are gone, the featured row lists the categories with
 * "Featured in header" by header order (at most 8), the mega menu lists categories with counts and
 * their top brands, and `/gaming`, `/gaming/{slug}` and `/{category}` move with a 301.
 */

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

/**
 * Featured link labels in the header row, in order.
 *
 * @return list<string>
 */
function sv2Featured(string $html): array
{
    preg_match_all('/<a href="[^"]+" class="[^"]*" data-featured-category\s*(?:aria-current="page"\s*)?>([^<]+)<\/a>/', $html, $matches);

    return $matches[1];
}

describe('header', function (): void {
    it('drops the v1 links and shows All categories, search, language and currency, wishlist, bag and account', function (): void {
        storefrontProduct();

        $response = $this->get('/')->assertOk()
            ->assertSee('All categories')
            ->assertSee('data-mega-toggle', false)
            ->assertSee('aria-controls="mega-menu"', false)
            ->assertSee('id="site-search"', false)
            ->assertSee('EN · USD')
            ->assertSee('href="'.url('/login').'"', false)
            ->assertSee('data-bag-count', false)
            ->assertSee('data-sheet-open', false);

        $header = explode('</header>', $response->getContent())[0];

        expect($header)->not->toContain('>Gift Cards<')
            ->not->toContain('>Top-ups<')
            ->not->toContain('>Subscriptions<')
            ->not->toContain('>Platforms<')
            ->not->toContain('/gaming');
    });

    it('lists the featured categories by header order, at most 8, only active ones', function (): void {
        foreach (range(1, 10) as $index) {
            Category::factory()->create(['name' => 'Featured '.$index, 'slug' => 'featured-'.$index, 'featured_in_header' => true, 'header_order' => 20 - $index]);
        }
        Category::factory()->create(['name' => 'Not featured', 'slug' => 'not-featured']);
        Category::factory()->create(['name' => 'Inactive featured', 'slug' => 'inactive-featured', 'featured_in_header' => true, 'header_order' => 0, 'is_active' => false]);

        $featured = sv2Featured($this->get('/')->assertOk()->getContent());

        expect($featured)->toBe(['Featured 10', 'Featured 9', 'Featured 8', 'Featured 7', 'Featured 6', 'Featured 5', 'Featured 4', 'Featured 3']);
    });

    it('marks the current featured category and translates names in Arabic', function (): void {
        $gaming = Category::factory()->create(['name' => 'Gaming', 'slug' => 'gaming', 'featured_in_header' => true]);
        ContentTranslation::factory()->create(['translatable_type' => 'category', 'translatable_id' => $gaming->id, 'locale' => 'ar', 'field' => 'name', 'value' => 'الألعاب']);

        expect($this->get('/c/gaming')->assertOk()->getContent())->toMatch('/data-featured-category\s+aria-current="page"/');
        expect(sv2Featured($this->get('/ar')->assertOk()->getContent()))->toBe(['الألعاب']);
    });

    it('fills the mega menu with categories, counts, top brands and See all links', function (): void {
        storefrontProduct('Steam Wallet $20', '20');
        storefrontProduct('Steam Wallet $50', '50');
        $xbox = GamingPlatform::factory()->create(['name' => 'Xbox', 'slug' => 'xbox']);
        storefrontProduct('Xbox Live', '15', ['gaming_platform_id' => $xbox->id]);

        $response = $this->get('/')->assertOk()
            ->assertSee('data-mega-item="all"', false)
            ->assertSee('data-mega-item="gift-cards"', false)
            ->assertSee('Top brands in Gift cards')
            ->assertSee('See all in Gift cards')
            ->assertSee('3 products · 2 brands')
            ->assertSee('href="'.url('/c/gift-cards').'"', false)
            ->assertSee('href="'.url('/b/steam').'"', false)
            ->assertSee('href="'.url('/c/gift-cards').'?delivery_type=instant_code"', false)
            ->assertSee('Popular filters');

        $html = $response->getContent();
        expect(strpos($html, 'data-mega-item="all"'))->toBeLessThan(strpos($html, 'data-mega-item="gift-cards"'));

        // The mobile sheet holds the categories accordion and the language and currency switches.
        $response->assertSee('id="mobile-menu"', false)->assertSee('aria-modal="true"', false)->assertSee('See all in Gift cards (3)')->assertSee('Language &amp; currency', false);
    });
});

describe('footer', function (): void {
    it('links all products, the categories and the top brands instead of the v1 links', function (): void {
        storefrontProduct();

        $footer = explode('<footer', $this->get('/')->assertOk()->getContent())[1];

        expect($footer)->toContain('href="'.url('/products').'"')
            ->toContain('href="'.url('/c/gift-cards').'"')
            ->toContain('href="'.url('/b/steam').'"')
            ->not->toContain('/gaming')
            ->not->toContain('game-top-ups')
            ->not->toContain('All platforms');
    });
});

describe('old URLs', function (): void {
    it('moves the v1 catalog URLs with a 301 in every language, keeping the query string', function (string $from, string $to): void {
        storefrontProduct();

        $this->get($from)->assertMovedPermanently()->assertRedirect(url($to));
    })->with([
        'platforms' => ['/gaming', '/products'],
        'platform' => ['/gaming/steam', '/b/steam'],
        'platform with a query' => ['/gaming/steam?sort=newest', '/b/steam?sort=newest'],
        'category' => ['/gift-cards', '/c/gift-cards'],
        'category with a query' => ['/gift-cards?platform=steam', '/c/gift-cards?platform=steam'],
        'arabic platforms' => ['/ar/gaming', '/ar/products'],
        'arabic platform' => ['/ar/gaming/steam', '/ar/b/steam'],
        'arabic category' => ['/ar/gift-cards', '/ar/c/gift-cards'],
    ]);

    it('keeps an unknown top-level slug a 404 and never shadows reserved routes', function (): void {
        Category::factory()->create(['slug' => 'retired', 'is_active' => false]);

        $this->get('/retired')->assertNotFound();
        $this->get('/nothing-here')->assertNotFound()->assertSee('This vault is empty.');
        $this->get('/products')->assertOk();
        $this->get('/search')->assertOk();
    });

    it('reserves the new c and b prefixes for categories', function (): void {
        $this->actingAs(adminWith(['catalog.manage']))
            ->postJson('/admin/api/categories', ['name' => 'C', 'slug' => 'c', 'is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('slug');
    });
});
