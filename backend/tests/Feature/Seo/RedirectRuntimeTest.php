<?php

/**
 * M18 redirects runtime (SEO_STRATEGY.md section 7): admin redirects (301, 302) and 410 after a
 * route miss, cached and invalidated on change, loops and chains handled, hits counted;
 * removed products are 410; slug changes create 301s; misses are logged and get the designed
 * 404 page.
 */

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\SearchQuery;
use App\Domains\Seo\Models\NotFoundLog;
use App\Domains\Seo\Models\Redirect;
use App\Domains\Seo\Services\RedirectService;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

it('sends an old path to its target with 301 or 302 and counts the hit', function (int $status): void {
    $redirect = seoRedirect('/old-steam-cards', '/gaming/steam', $status);

    $this->get('/old-steam-cards')->assertStatus($status)->assertRedirect(url('/gaming/steam'));
    $this->get('/Old-Steam-Cards')->assertStatus($status)->assertRedirect(url('/gaming/steam'));

    expect($redirect->refresh()->hits)->toBe(2)
        ->and(NotFoundLog::query()->count())->toBe(0);
})->with([301, 302]);

it('keeps the query string when the target has none', function (): void {
    seoRedirect('/promo', '/gift-cards');
    seoRedirect('/promo-sorted', '/gift-cards?sort=price_asc');

    $this->get('/promo?utm_source=mail')->assertRedirect(url('/gift-cards').'?utm_source=mail');
    $this->get('/promo-sorted?utm_source=mail')->assertRedirect(url('/gift-cards').'?sort=price_asc');
});

it('answers 410 Gone with the designed page for a removed URL, in the language of the path', function (): void {
    seoRedirect('/discontinued-card', null, 410);
    seoRedirect('/ar/discontinued-card', null, 410);

    $this->get('/discontinued-card')->assertStatus(410)->assertSee('This page was removed.')->assertSee('noindex,follow');
    $this->get('/ar/discontinued-card')->assertStatus(410)->assertSee('تمت إزالة هذه الصفحة.')->assertSee('dir="rtl"', false);
});

it('ignores inactive redirects and never redirects a page that exists', function (): void {
    storefrontProduct();
    seoRedirect('/sleeping', '/gaming', active: false);
    seoRedirect('/gift-cards', '/gaming');

    $this->get('/sleeping')->assertNotFound();
    $this->get('/gift-cards')->assertOk();
});

it('applies a change to a cached redirect at once', function (): void {
    $redirect = seoRedirect('/moving', '/gaming');
    $this->get('/moving')->assertRedirect(url('/gaming'));

    $redirect->update(['target_path' => '/gift-cards']);
    $this->get('/moving')->assertRedirect(url('/gift-cards'));

    $redirect->delete();
    $this->get('/moving')->assertNotFound();
});

it('follows a chain that slipped through to its final target in one redirect', function (): void {
    seoRedirect('/a', '/b');
    seoRedirect('/b', '/c', 302);
    seoRedirect('/c', '/gaming');

    $this->get('/a')->assertMovedPermanently()->assertRedirect(url('/gaming'));
});

it('never sends a loop, or a chain longer than the hop limit, to the browser', function (): void {
    seoRedirect('/loop-a', '/loop-b');
    seoRedirect('/loop-b', '/loop-a');
    $this->get('/loop-a')->assertNotFound();

    foreach (range(1, 7) as $hop) {
        seoRedirect("/hop-{$hop}", '/hop-'.($hop + 1));
    }

    $this->get('/hop-1')->assertNotFound();
});

it('makes a chain that ends in a removed URL gone', function (): void {
    seoRedirect('/x-old', '/x-removed');
    seoRedirect('/x-removed', null, 410);

    $this->get('/x-old')->assertStatus(410);
});

it('collapses chains when an admin saves a redirect', function (): void {
    actingAsStaffWith(['seo.redirects']);
    $first = seoRedirect('/first', '/second');

    $this->postJson('/admin/api/redirects', ['source_path' => '/second', 'target_path' => '/gaming', 'status_code' => 301, 'is_active' => true])->assertCreated();

    expect($first->refresh()->target_path)->toBe('/gaming');
});

it('answers 410 for a permanently removed product and 404 for an unknown one', function (): void {
    $product = storefrontProduct('Old Card', '10');
    $product->delete();

    $this->get('/products/'.$product->slug)->assertStatus(410);
    $this->get('/ar/products/'.$product->slug)->assertStatus(410);
    $this->get('/products/never-existed')->assertNotFound();
});

it('creates 301s in every language when a slug changes, without loops when it changes back', function (): void {
    $platform = GamingPlatform::factory()->create(['slug' => 'playstation', 'name' => 'PlayStation']);

    $platform->update(['slug' => 'psn']);

    $this->assertDatabaseHas('redirects', ['source_hash' => RedirectService::hash('/gaming/playstation'), 'target_path' => '/gaming/psn', 'status_code' => 301]);
    $this->assertDatabaseHas('redirects', ['source_hash' => RedirectService::hash('/ar/gaming/playstation'), 'target_path' => '/ar/gaming/psn']);
    $this->get('/gaming/playstation')->assertMovedPermanently()->assertRedirect(url('/gaming/psn'));

    $platform->update(['slug' => 'playstation-store']);
    expect(Redirect::query()->where('source_hash', RedirectService::hash('/gaming/playstation'))->value('target_path'))->toBe('/gaming/playstation-store');

    $platform->update(['slug' => 'playstation']);
    expect(Redirect::query()->where('source_hash', RedirectService::hash('/gaming/playstation'))->exists())->toBeFalse()
        ->and(Redirect::query()->where('source_hash', RedirectService::hash('/gaming/psn'))->value('target_path'))->toBe('/gaming/playstation');
});

it('logs storefront misses with a hit count, without the query string, and shows popular searches', function (): void {
    SearchQuery::factory()->create(['locale' => 'en', 'term' => 'steam wallet', 'hits' => 9, 'results_count' => 3]);

    $this->get('/no-such-page?token=secret')->assertNotFound()->assertSee('This vault is empty.')
        ->assertSee('Popular searches')->assertSee('steam wallet');
    $this->get('/no-such-page')->assertNotFound();

    $log = NotFoundLog::query()->sole();
    expect($log->path)->toBe('/no-such-page')
        ->and($log->hits)->toBe(2);
});

it('does not log JSON misses or file probes', function (): void {
    $this->getJson('/api/v1/nope')->assertNotFound();
    $this->get('/wp-login.php')->assertNotFound();

    expect(NotFoundLog::query()->count())->toBe(0);
});
