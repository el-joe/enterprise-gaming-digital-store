<?php

/**
 * M18 sitemaps and robots.txt (SEO_STRATEGY.md sections 5 and 6): an index of child sitemaps
 * per type and language, hreflang alternates (en, ar, x-default) and lastmod on every URL,
 * chunking, exclusions, the regeneration command and its schedule.
 */

use App\Console\TaskSchedule;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Page;
use App\Domains\Seo\Models\SeoMetadata;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

/**
 * @return list<string> the `<loc>` values of an XML sitemap response
 */
function sitemapLocations(string $xml): array
{
    $document = simplexml_load_string($xml);
    expect($document)->not->toBeFalse();

    return array_map('strval', $document->xpath('//*[local-name()="loc"]'));
}

/**
 * @return array<string, array{lastmod: string|null, alternates: array<string, string>}> loc => details
 */
function sitemapUrls(string $xml): array
{
    $document = simplexml_load_string($xml);
    $document->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $document->registerXPathNamespace('x', 'http://www.w3.org/1999/xhtml');
    $urls = [];

    foreach ($document->xpath('//s:url') as $url) {
        $url->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $url->registerXPathNamespace('x', 'http://www.w3.org/1999/xhtml');
        $alternates = [];

        foreach ($url->xpath('x:link') as $link) {
            $alternates[(string) $link['hreflang']] = (string) $link['href'];
        }

        $lastmod = $url->xpath('s:lastmod');
        $urls[(string) $url->xpath('s:loc')[0]] = ['lastmod' => $lastmod === [] ? null : (string) $lastmod[0], 'alternates' => $alternates];
    }

    return $urls;
}

it('serves an index of child sitemaps per type and language, generated on first request', function (): void {
    storefrontProduct('Steam Wallet $20', '20');

    $response = $this->get('/sitemap.xml')->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertHeader('X-Robots-Tag', 'noindex');

    $children = sitemapLocations($response->getContent());

    expect($children)->toContain(
        url('/sitemaps/static-en-1.xml'), url('/sitemaps/static-ar-1.xml'),
        url('/sitemaps/products-en-1.xml'), url('/sitemaps/products-ar-1.xml'),
        url('/sitemaps/categories-en-1.xml'), url('/sitemaps/platforms-ar-1.xml'),
        url('/sitemaps/pages-en-1.xml'),
    );

    foreach ($children as $child) {
        $this->get(parse_url($child, PHP_URL_PATH))->assertOk();
    }
});

it('lists each product with hreflang alternates for en, ar and x-default and its lastmod', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    $product->forceFill(['updated_at' => '2026-10-01 12:00:00'])->saveQuietly();
    $this->artisan('seo:generate-sitemaps')->assertSuccessful();

    $english = sitemapUrls($this->get('/sitemaps/products-en-1.xml')->assertOk()->getContent());
    $arabic = sitemapUrls($this->get('/sitemaps/products-ar-1.xml')->assertOk()->getContent());

    $alternates = ['en' => url('/products/'.$product->slug), 'ar' => url('/ar/products/'.$product->slug), 'x-default' => url('/products/'.$product->slug)];

    expect($english[url('/products/'.$product->slug)])->toBe(['lastmod' => $product->fresh()->updated_at->toAtomString(), 'alternates' => $alternates])
        ->and($arabic[url('/ar/products/'.$product->slug)]['alternates'])->toBe($alternates);
});

it('excludes drafts, inactive and noindex pages and never lists private pages', function (): void {
    $listed = storefrontProduct('Listed card', '20');
    $draft = storefrontProduct('Draft card', '20', ['status' => ProductStatus::Draft]);
    $hiddenInArabic = storefrontProduct('Hidden in Arabic', '20');
    SeoMetadata::factory()->create(['seoable_type' => 'product', 'seoable_id' => $hiddenInArabic->id, 'locale' => 'ar', 'robots' => 'noindex,follow']);
    Category::factory()->create(['slug' => 'empty-category', 'is_active' => true]);
    Category::factory()->create(['slug' => 'inactive-category', 'is_active' => false]);
    Page::factory()->create(['slug' => 'shipping', 'status' => PageStatus::Published]);
    Page::factory()->create(['slug' => 'secret-draft', 'status' => PageStatus::Draft]);
    BlogPost::factory()->create(['slug' => 'live-post', 'status' => BlogPostStatus::Published, 'published_at' => now()->subDay()]);
    BlogPost::factory()->create(['slug' => 'draft-post', 'status' => BlogPostStatus::Draft]);

    $this->artisan('seo:generate-sitemaps')->assertSuccessful();

    $all = collect(sitemapLocations($this->get('/sitemap.xml')->getContent()))
        ->flatMap(fn (string $child): array => sitemapLocations($this->get(parse_url($child, PHP_URL_PATH))->getContent()))
        ->all();

    expect($all)->toContain(url('/'), url('/ar'), url('/products'), url('/blog'), url('/c/gift-cards'), url('/b/steam'),
        url('/products/'.$listed->slug), url('/products/'.$hiddenInArabic->slug), url('/pages/shipping'), url('/pages/terms'),
        url('/blog/live-post'), url('/ar/blog/live-post'))
        ->and($all)->not->toContain(url('/products/'.$draft->slug), url('/ar/products/'.$hiddenInArabic->slug),
            url('/c/empty-category'), url('/c/inactive-category'), url('/pages/secret-draft'), url('/blog/draft-post'),
            url('/search'), url('/cart'), url('/checkout'), url('/account'), url('/login'));

    // A URL noindex in one language lists no alternate for that language.
    $english = sitemapUrls($this->get('/sitemaps/products-en-1.xml')->getContent());
    expect($english[url('/products/'.$hiddenInArabic->slug)]['alternates'])->toBe([]);
});

it('splits a type into chunks of the configured size', function (): void {
    config(['seo.sitemaps.chunk_size' => 2]);
    foreach (range(1, 5) as $index) {
        storefrontProduct("Card {$index}", (string) (10 + $index));
    }

    $this->artisan('seo:generate-sitemaps')->assertSuccessful();
    $children = sitemapLocations($this->get('/sitemap.xml')->getContent());

    expect($children)->toContain(url('/sitemaps/products-en-1.xml'), url('/sitemaps/products-en-2.xml'), url('/sitemaps/products-en-3.xml'))
        ->and($children)->not->toContain(url('/sitemaps/products-en-4.xml'))
        ->and(sitemapLocations($this->get('/sitemaps/products-en-3.xml')->getContent()))->toHaveCount(1);
});

it('replaces the previous set on regeneration and 404s unknown files', function (): void {
    config(['seo.sitemaps.chunk_size' => 1]);
    $first = storefrontProduct('Card one', '10');
    storefrontProduct('Card two', '12');
    $this->artisan('seo:generate-sitemaps')->assertSuccessful();
    $this->get('/sitemaps/products-en-2.xml')->assertOk();

    $first->forceFill(['status' => ProductStatus::Inactive])->save();
    $this->artisan('seo:generate-sitemaps')->assertSuccessful();

    $this->get('/sitemaps/products-en-2.xml')->assertNotFound();
    $this->get('/sitemaps/../../.env')->assertNotFound();
    $this->get('/sitemaps/products-en-99.xml')->assertNotFound();
});

it('regenerates hourly from the scheduler', function (): void {
    $schedule = app(Schedule::class);
    TaskSchedule::register($schedule);

    $commands = collect($schedule->events())->map(fn ($event): string => (string) $event->command);

    expect($commands->contains(fn (string $command): bool => str_contains($command, 'seo:generate-sitemaps')))->toBeTrue()
        ->and($commands->contains(fn (string $command): bool => str_contains($command, 'analytics:dispatch-pending')))->toBeTrue()
        ->and($commands->contains(fn (string $command): bool => str_contains($command, 'seo:fetch-search-console')))->toBeTrue();
});

it('serves robots.txt that references the index and disallows private areas in every language', function (): void {
    $robots = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

    expect($robots)->toContain('User-agent: *')
        ->toContain('Sitemap: '.url('/sitemap.xml'))
        ->toContain("Disallow: /admin\n")->toContain("Disallow: /api\n")
        ->toContain("Disallow: /checkout\n")->toContain("Disallow: /ar/checkout\n")
        ->toContain("Disallow: /cart\n")->toContain("Disallow: /ar/cart\n")
        ->toContain("Disallow: /account\n")->toContain("Disallow: /ar/account\n")
        ->toContain("Disallow: /search\n")
        ->not->toContain('Disallow: /build')
        ->not->toContain("Disallow: /\n");
    expect(file_exists(public_path('robots.txt')))->toBeFalse();

    config(['seo.robots.block_all' => true]);
    expect($this->get('/robots.txt')->getContent())->toBe("User-agent: *\nDisallow: /\n");
});
