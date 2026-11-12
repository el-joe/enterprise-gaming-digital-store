<?php

/**
 * M18 canonical, robots and hreflang rules (SEO_STRATEGY.md sections 2 and 6): query
 * parameters, filters, sort and pagination; noindex for search, multi-filter and empty listings;
 * trailing slash and letter case normalised with a 301; no country segment in any URL.
 */

use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Seo\Models\SeoTemplate;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    storefrontWithoutBuild();
    config(['storefront.per_page' => 1]);
});

/**
 * @return array{canonical: string|null, robots: string|null, hreflang: array<string, string>}
 */
function headTags(TestResponse $response): array
{
    $html = $response->getContent();
    preg_match('~<link rel="canonical" href="([^"]+)">~', $html, $canonical);
    preg_match('~<meta name="robots" content="([^"]+)">~', $html, $robots);
    preg_match_all('~<link rel="alternate" hreflang="([^"]+)" href="([^"]+)">~', $html, $alternates, PREG_SET_ORDER);

    return [
        'canonical' => isset($canonical[1]) ? html_entity_decode($canonical[1]) : null,
        'robots' => $robots[1] ?? null,
        'hreflang' => collect($alternates)->mapWithKeys(static fn (array $match): array => [$match[1] => html_entity_decode($match[2])])->all(),
    ];
}

it('canonicalizes product query strings to the clean product URL with matching hreflang', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');

    $tags = headTags($this->get('/products/'.$product->slug.'?region=us&utm_source=mail&currency=EUR')->assertOk());

    expect($tags)->toBe([
        'canonical' => url('/products/'.$product->slug),
        'robots' => 'index,follow',
        'hreflang' => ['en' => url('/products/'.$product->slug), 'ar' => url('/ar/products/'.$product->slug), 'x-default' => url('/products/'.$product->slug)],
    ]);

    expect(headTags($this->get('/ar/products/'.$product->slug)->assertOk())['canonical'])->toBe(url('/ar/products/'.$product->slug));
});

it('canonicalizes one filter or a sort to the unfiltered category and keeps it indexable', function (string $query): void {
    storefrontProduct('Steam Wallet $20', '20');

    $tags = headTags($this->get('/c/gift-cards?'.$query)->assertOk());

    expect($tags['canonical'])->toBe(url('/c/gift-cards'))
        ->and($tags['robots'])->toBe('index,follow')
        ->and($tags['hreflang'])->toBe(['en' => url('/c/gift-cards'), 'ar' => url('/ar/c/gift-cards'), 'x-default' => url('/c/gift-cards')]);
})->with(['sort=price_desc', 'platform=steam', 'delivery_type=instant_code']);

it('marks two or more filters noindex,follow without canonical or hreflang', function (): void {
    storefrontProduct('Steam Wallet $20', '20');

    $tags = headTags($this->get('/c/gift-cards?platform=steam&sort=price_desc')->assertOk());

    expect($tags)->toBe(['canonical' => null, 'robots' => 'noindex,follow', 'hreflang' => []]);
});

it('keeps paginated listings self-canonical with the page in hreflang and a "Page N" title', function (): void {
    storefrontProduct('Steam Wallet $20', '20');
    storefrontProduct('Steam Wallet $50', '50');

    $response = $this->get('/c/gift-cards?page=2&utm_campaign=x')->assertOk()->assertSee('<title>Gift cards — Page 2 |', false);

    expect(headTags($response))->toBe([
        'canonical' => url('/c/gift-cards').'?page=2',
        'robots' => 'index,follow',
        'hreflang' => ['en' => url('/c/gift-cards').'?page=2', 'ar' => url('/ar/c/gift-cards').'?page=2', 'x-default' => url('/c/gift-cards').'?page=2'],
    ]);

    expect(headTags($this->get('/b/steam?page=2')->assertOk())['canonical'])->toBe(url('/b/steam').'?page=2');
});

it('marks an empty (thin) listing noindex', function (): void {
    storefrontProduct('Steam Wallet $20', '20');

    expect(headTags($this->get('/c/gift-cards?page=9')->assertOk())['robots'])->toBe('noindex,follow');
});

it('keeps search noindex,follow with no canonical or hreflang', function (): void {
    expect(headTags($this->get('/search?q=steam')->assertOk()))->toBe(['canonical' => null, 'robots' => 'noindex,follow', 'hreflang' => []]);
});

it('never puts the display currency in the URL', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    givenRate('EGP', '50');

    $tags = headTags($this->withUnencryptedCookie('currency', 'EGP')->get('/products/'.$product->slug)->assertOk());

    expect($tags['canonical'])->toBe(url('/products/'.$product->slug))
        ->and(implode(' ', $tags['hreflang']))->not->toContain('EGP');
});

it('applies an admin robots value, canonical override and title, then templates', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    SeoMetadata::factory()->create(['seoable_type' => 'product', 'seoable_id' => $product->id, 'locale' => 'en', 'title' => 'Buy Steam $20 fast', 'robots' => 'noindex,follow', 'canonical_override' => null]);

    $response = $this->get('/products/'.$product->slug)->assertOk()->assertSee('<title>Buy Steam $20 fast | ', false);
    expect(headTags($response)['robots'])->toBe('noindex,follow');

    // Arabic has no row: the Arabic template applies, with its tokens.
    SeoTemplate::factory()->create(['page_type' => 'product', 'locale' => 'ar', 'title_template' => '{name} من {site}', 'description_template' => 'اشتر {name} من {site}']);
    $this->get('/ar/products/'.$product->slug)->assertOk()->assertSee('<title>Steam Wallet $20 من '.config('brand.name'), false);

    SeoMetadata::factory()->create(['seoable_type' => 'category', 'seoable_id' => $product->primary_category_id, 'locale' => 'en', 'canonical_override' => 'https://boxcards.shop/c/gift-cards', 'robots' => null, 'title' => null]);
    $tags = headTags($this->get('/c/gift-cards')->assertOk());
    expect($tags['canonical'])->toBe('https://boxcards.shop/c/gift-cards')
        ->and($tags['hreflang'])->toBe([]);
});

it('removes a trailing slash with a 301, keeping the query string', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');

    // The test client trims trailing slashes, so these requests go to the kernel as sent.
    $send = fn (string $uri): TestResponse => TestResponse::fromBaseResponse(app(HttpKernel::class)->handle(Request::create(url('/').$uri)));

    $send('/gaming/')->assertMovedPermanently()->assertRedirect(url('/gaming'));
    $send('/products/'.$product->slug.'/?region=us')->assertMovedPermanently()->assertRedirect(url('/products/'.$product->slug).'?region=us');
    $send('/ar/')->assertMovedPermanently()->assertRedirect(url('/ar'));
    $send('/')->assertOk();
    $send('/api/v1/nope/')->assertNotFound();
});

it('sends upper-case storefront paths to their lower-case URL with a 301', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');

    $this->get('/Gift-Cards')->assertMovedPermanently()->assertRedirect(url('/gift-cards'));
    $this->get('/C/Gift-Cards')->assertMovedPermanently()->assertRedirect(url('/c/gift-cards'));
    $this->get('/PRODUCTS/'.strtoupper($product->slug))->assertMovedPermanently()->assertRedirect(url('/products/'.$product->slug));
    $this->get('/AR/Gaming')->assertMovedPermanently()->assertRedirect(url('/ar/gaming'));
});

it('never normalises JSON or admin paths', function (): void {
    $this->get('/api/V1/NOPE')->assertNotFound();
    $this->get('/admin/Login')->assertNotFound();
});

it('has no country segment: a country code path is an ordinary 404', function (string $path): void {
    $this->get($path)->assertNotFound();
})->with(['/eg', '/eg/gift-cards', '/us/products/steam-wallet', '/sa/ar']);
