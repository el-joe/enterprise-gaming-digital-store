<?php

/**
 * M18 structured data (SEO_STRATEGY.md section 4): JSON-LD built on the server, one nonced
 * `application/ld+json` script per page, validated here by parsing it and checking the
 * properties Google requires, in English and Arabic.
 */

use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use App\Domains\Settings\Enums\SettingType;
use Database\Seeders\HomepageSectionSeeder;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

it('describes the organization and the site search on every page, per language', function (): void {
    $graph = jsonLdGraph($this->get('/gaming')->assertOk());

    $organization = jsonLdNode($graph, 'Organization');
    expect($organization)->toMatchArray(['name' => config('brand.name'), 'url' => url('/')])
        ->and($organization['logo'])->toBe(asset('images/brand/icon-512.png'));

    $website = jsonLdNode($graph, 'WebSite');
    expect($website['url'])->toBe(url('/'))
        ->and($website['inLanguage'])->toBe('en')
        ->and($website['potentialAction']['@type'])->toBe('SearchAction')
        ->and($website['potentialAction']['target']['urlTemplate'])->toBe(url('/search').'?q={search_term_string}')
        ->and($website['potentialAction']['query-input'])->toBe('required name=search_term_string');

    $arabic = jsonLdNode(jsonLdGraph($this->get('/ar/gaming')->assertOk()), 'WebSite');
    expect($arabic['url'])->toBe(url('/ar'))
        ->and($arabic['inLanguage'])->toBe('ar')
        ->and($arabic['potentialAction']['target']['urlTemplate'])->toBe(url('/ar/search').'?q={search_term_string}');
});

it('marks up a product with one priced variant as a Product with an Offer at the rendered price', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');

    $response = $this->get('/products/'.$product->slug)->assertOk();
    $graph = jsonLdGraph($response);
    $node = jsonLdNode($graph, 'Product');

    expect($node)->toMatchArray(['name' => 'Steam Wallet $20', 'url' => url('/products/'.$product->slug), 'sku' => $product->sku, 'inLanguage' => 'en'])
        ->and($node['offers']['@type'])->toBe('Offer')
        ->and($node['offers']['price'])->toBe('20.00')
        ->and($node['offers']['priceCurrency'])->toBe('USD')
        ->and($node['offers']['availability'])->toBe('https://schema.org/InStock')
        ->and($node['offers']['priceValidUntil'])->toMatch('/^\d{4}-\d{2}-\d{2}$/')
        ->and($node)->not->toHaveKey('aggregateRating');

    // The Offer price is the price the page shows.
    $response->assertSee(storefrontPrice('20.00'));

    $breadcrumbs = jsonLdNode($graph, 'BreadcrumbList');
    expect(array_column($breadcrumbs['itemListElement'], 'position'))->toBe([1, 2, 3])
        ->and($breadcrumbs['itemListElement'][0]['item'])->toBe(url('/'))
        ->and($breadcrumbs['itemListElement'][1]['item'])->toBe(url('/gift-cards'))
        ->and($breadcrumbs['itemListElement'][2])->toMatchArray(['name' => 'Steam Wallet $20', 'item' => url('/products/'.$product->slug)]);
});

it('uses an AggregateOffer with the low and high price for several variants', function (): void {
    $product = storefrontProduct('Steam Wallet', '10');
    ProductVariant::factory()->for($product)->create(['title' => '$50', 'manual_cost_base' => '50', 'availability' => ProductVariantAvailability::OutOfStock])
        ->forceFill(['price_base' => '50'])->save();

    $offers = jsonLdNode(jsonLdGraph($this->get('/products/'.$product->slug)->assertOk()), 'Product')['offers'];

    expect($offers)->toMatchArray(['@type' => 'AggregateOffer', 'priceCurrency' => 'USD', 'lowPrice' => '10.00', 'highPrice' => '50.00', 'offerCount' => 2, 'availability' => 'https://schema.org/InStock'])
        ->and(array_column($offers['offers'], 'price'))->toBe(['10.00', '50.00'])
        ->and(collect($offers['offers'])->pluck('availability')->all())->toContain('https://schema.org/OutOfStock');
});

it('prices the offer in the display currency the page uses', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    givenRate('EGP', '50');

    $response = $this->withUnencryptedCookie('currency', 'EGP')->get('/products/'.$product->slug)->assertOk();
    $offer = jsonLdNode(jsonLdGraph($response), 'Product')['offers'];

    expect($offer['priceCurrency'])->toBe('EGP')
        ->and($response->getContent())->toContain(e(storefrontPrice($offer['price'], 'EGP')));
});

it('adds AggregateRating only when approved reviews exist', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    $product->forceFill(['rating_avg' => '4.50', 'rating_count' => 2])->save();

    $rating = jsonLdNode(jsonLdGraph($this->get('/products/'.$product->slug)->assertOk()), 'Product')['aggregateRating'];

    expect($rating)->toMatchArray(['@type' => 'AggregateRating', 'ratingValue' => '4.50', 'reviewCount' => 2, 'bestRating' => '5', 'worstRating' => '1']);
});

it('marks up the Arabic product page with Arabic names and Arabic URLs', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');
    ContentTranslation::factory()->create(['translatable_type' => 'product', 'translatable_id' => $product->id, 'locale' => 'ar', 'field' => 'name', 'value' => 'محفظة ستيم 20 دولار']);

    $graph = jsonLdGraph($this->get('/ar/products/'.$product->slug)->assertOk());

    expect(jsonLdNode($graph, 'Product'))->toMatchArray(['name' => 'محفظة ستيم 20 دولار', 'url' => url('/ar/products/'.$product->slug), 'inLanguage' => 'ar'])
        ->and(jsonLdNode($graph, 'BreadcrumbList')['itemListElement'][0]['item'])->toBe(url('/ar'));
});

it('never lets catalog text close the JSON-LD script', function (): void {
    $product = storefrontProduct('Card </script><script>alert(1)</script>', '20');

    $response = $this->get('/products/'.$product->slug)->assertOk();

    expect($response->getContent())->not->toContain('</script><script>alert(1)')
        ->and(jsonLdNode(jsonLdGraph($response), 'Product')['name'])->toBe('Card </script><script>alert(1)</script>');
    assertCspSafeHtml($response);
});

it('marks up a category as a CollectionPage listing its products, with breadcrumbs', function (): void {
    $product = storefrontProduct('Steam Wallet $20', '20');

    $graph = jsonLdGraph($this->get('/gift-cards')->assertOk());
    $collection = jsonLdNode($graph, 'CollectionPage');

    expect($collection['url'])->toBe(url('/gift-cards'))
        ->and($collection['mainEntity']['@type'])->toBe('ItemList')
        ->and($collection['mainEntity']['itemListElement'][0])->toMatchArray(['position' => 1, 'name' => 'Steam Wallet $20', 'url' => url('/products/'.$product->slug)])
        ->and(jsonLdNode($graph, 'BreadcrumbList')['itemListElement'])->toHaveCount(2);

    expect(jsonLdNode(jsonLdGraph($this->get('/gaming/steam')->assertOk()), 'CollectionPage')['url'])->toBe(url('/gaming/steam'));
});

it('marks up a blog post as a BlogPosting', function (): void {
    $post = BlogPost::factory()->create(['title' => 'How to redeem a Steam card', 'slug' => 'redeem-steam', 'excerpt' => 'Step by step.', 'body' => '<p>Open Steam.</p>', 'status' => BlogPostStatus::Published, 'published_at' => now()->subDay()]);

    $response = $this->get('/blog/redeem-steam')->assertOk()->assertSee('How to redeem a Steam card')->assertSee('Open Steam.');
    $article = jsonLdNode(jsonLdGraph($response), 'BlogPosting');

    expect($article)->toMatchArray(['headline' => 'How to redeem a Steam card', 'url' => url('/blog/redeem-steam'), 'description' => 'Step by step.', 'inLanguage' => 'en'])
        ->and($article['datePublished'])->toBe($post->published_at->toIso8601String())
        ->and($article['author']['@type'])->toBe('Organization');

    expect(jsonLdNode(jsonLdGraph($this->get('/ar/blog/redeem-steam')->assertOk()), 'BlogPosting')['url'])->toBe(url('/ar/blog/redeem-steam'));
});

it('lists visible blog posts only and hides drafts and future posts', function (): void {
    BlogPost::factory()->create(['title' => 'Live post', 'status' => BlogPostStatus::Published, 'published_at' => now()->subDay()]);
    BlogPost::factory()->create(['title' => 'Draft post', 'slug' => 'draft-post', 'status' => BlogPostStatus::Draft]);
    BlogPost::factory()->create(['title' => 'Future post', 'slug' => 'future-post', 'status' => BlogPostStatus::Scheduled, 'published_at' => now()->addDay()]);

    $this->get('/blog')->assertOk()->assertSee('Live post')->assertDontSee('Draft post')->assertDontSee('Future post');
    $this->get('/blog/draft-post')->assertNotFound();
    $this->get('/blog/future-post')->assertNotFound();
});

it('adds FAQPage only while the FAQ schema setting is on', function (): void {
    $this->seed(HomepageSectionSeeder::class);
    $faq = Faq::factory()->create(['question' => 'How fast is delivery?', 'answer' => 'Usually within minutes.', 'is_active' => true]);
    HomepageSectionItem::factory()->create(['homepage_section_id' => HomepageSection::query()->where('key', 'faq')->value('id'), 'item_type' => 'faq', 'item_id' => $faq->id]);

    $this->get('/')->assertOk()->assertSee('How fast is delivery?');
    expect(jsonLdNode(jsonLdGraph($this->get('/')), 'FAQPage'))->toBeNull();

    analyticsSetting('faq_schema', true, SettingType::Bool);
    $node = jsonLdNode(jsonLdGraph($this->get('/')->assertOk()), 'FAQPage');

    expect($node['mainEntity'])->toBe([[
        '@type' => 'Question',
        'name' => 'How fast is delivery?',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Usually within minutes.'],
    ]]);
});
