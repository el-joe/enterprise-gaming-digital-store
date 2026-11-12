<?php

/**
 * M12 storefront pages (Blade): home, platforms, category listing, product, search, content
 * pages and errors, in English (LTR) and Arabic (RTL), with server-rendered money and a strict
 * Content Security Policy.
 */

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\SearchDocument;
use App\Domains\Catalog\Models\SearchQuery;
use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\Page;
use Database\Seeders\HomepageSectionSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

describe('home', function (): void {
    it('renders the sections with server prices, the hero carousel controls and a strict CSP', function (): void {
        $this->seed(HomepageSectionSeeder::class);
        storefrontProduct('Steam Wallet $20', '20');

        $response = $this->get('/')->assertOk()
            ->assertHeader('Content-Language', 'en')
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('Best sellers')
            ->assertSee('Steam Wallet $20')
            ->assertSee(storefrontPrice('20.00'))
            ->assertSee('data-hero-toggle', false)
            ->assertSee('data-hero-dot="2"', false)
            ->assertSee('Secure delivery')
            ->assertSee('rel="alternate" hreflang="ar" href="'.url('/ar').'"', false);

        assertCspSafeHtml($response);
        expect($response->headers->get('Content-Security-Policy'))->toContain("script-src 'self' 'nonce-")->not->toContain('unsafe-inline');
    });

    it('renders right to left in Arabic with translated copy and catalog names', function (): void {
        $this->seed(HomepageSectionSeeder::class);
        $product = storefrontProduct('Steam Wallet $20', '20');
        ContentTranslation::factory()->create(['translatable_type' => 'product', 'translatable_id' => $product->id, 'locale' => 'ar', 'field' => 'name', 'value' => 'محفظة ستيم 20 دولار']);

        $this->get('/ar')->assertOk()
            ->assertHeader('Content-Language', 'ar')
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('كل لعبة. خزنة واحدة.')
            ->assertSee('محفظة ستيم 20 دولار')
            ->assertSee(storefrontPrice('20.00'))
            ->assertSee('href="'.url('/ar/products/'.$product->slug).'"', false);
    });

    it('shows prices in the display currency chosen with the currency cookie', function (): void {
        $this->seed(HomepageSectionSeeder::class);
        givenRate('EGP', '50');
        storefrontProduct('Steam Wallet $20', '20');

        $this->withUnencryptedCookie('currency', 'EGP')->get('/')->assertOk()
            ->assertSee('EGP')
            ->assertSee('EGP · ', false)
            ->assertDontSee(storefrontPrice('20.00'));
    });

    it('confirms an email verification landing', function (string $status, string $text): void {
        $this->get('/?email_verification='.$status)->assertOk()->assertSee($text);
    })->with([
        ['verified', 'Your email address is verified.'],
        ['invalid', 'This verification link is invalid'],
    ]);
});

describe('brands (v1 platforms)', function (): void {
    it('sends the v1 platform list to all products with a 301', function (): void {
        storefrontProduct();
        GamingPlatform::factory()->create(['name' => 'Retired Console', 'slug' => 'retired', 'is_active' => false]);

        $this->get('/gaming')->assertMovedPermanently()->assertRedirect(url('/products'));

        $this->get('/products')->assertOk()
            ->assertSee('All products')
            ->assertSee('Steam')
            ->assertSee('1 product')
            ->assertDontSee('Retired Console');
    });

    it('shows a brand with its products, in both languages', function (string $prefix, string $text): void {
        storefrontProduct('Steam Wallet $20', '20');

        $this->get($prefix.'/gaming/steam')->assertMovedPermanently()->assertRedirect(url($prefix.'/b/steam'));
        $this->get($prefix.'/b/steam')->assertOk()
            ->assertSee($text)
            ->assertSee('Steam Wallet $20')
            ->assertSee(storefrontPrice('20.00'));
    })->with([
        'en' => ['', 'Steam gift cards and top-ups'],
        'ar' => ['/ar', 'بطاقات هدايا وشحن Steam'],
    ]);

    it('is 404 with the storefront error page for an unknown brand', function (): void {
        $this->get('/b/nope')->assertNotFound()->assertSee('This vault is empty.');
    });
});

describe('category listing', function (): void {
    it('lists the category products with filters, sort and a self canonical', function (): void {
        storefrontProduct('Steam Wallet $20', '20');
        storefrontProduct('Steam Wallet $50', '50');

        $response = $this->get('/c/gift-cards?sort=price_desc')->assertOk()
            ->assertSee('Gift cards')
            ->assertSee('2 products')
            ->assertSee('<option value="price_desc" selected', false)
            ->assertSee('<link rel="canonical" href="'.url('/c/gift-cards').'">', false)
            ->assertSee('index,follow');

        expect(strpos($response->getContent(), 'Steam Wallet $50'))->toBeLessThan(strpos($response->getContent(), 'Steam Wallet $20'));
        assertCspSafeHtml($response);
    });

    it('filters by price in the display currency and marks multi-filter pages noindex', function (): void {
        storefrontProduct('Steam Wallet $20', '20');
        storefrontProduct('Steam Wallet $50', '50');

        $this->get('/c/gift-cards?min_price=30&platform=steam')->assertOk()
            ->assertSee('Steam Wallet $50')
            ->assertDontSee('Steam Wallet $20')
            ->assertSee('noindex,follow');
    });

    it('treats empty filters as none and sends an invalid filter back to the plain page', function (): void {
        storefrontProduct();

        $this->get('/c/gift-cards?platform=&delivery_type=&sort=')->assertOk()->assertSee('1 product');
        $this->get('/c/gift-cards?sort=cheapest')->assertRedirect(url('/c/gift-cards'));
    });

    it('shows the empty state when nothing matches', function (): void {
        storefrontProduct();

        $this->get('/c/gift-cards?min_price=500')->assertOk()->assertSee('No products match these filters')
            ->assertSee('Clear all filters')->assertSee('Browse all categories');
    });

    it('is 404 for an unknown or inactive category, and never shadows reserved routes', function (): void {
        $this->get('/c/no-such-category')->assertNotFound();
        $this->get('/no-such-category')->assertNotFound();
        $this->get('/login')->assertOk()->assertSee('Welcome back');
        $this->get('/ar/c/gift-cards')->assertNotFound()->assertSee('هذه الخزنة فارغة.');
    });
});

describe('product page', function (): void {
    it('renders variants with server prices, delivery fields, reviews and related products', function (): void {
        $product = storefrontProduct('Steam Wallet $20', '20', ['description' => '<p>Add funds <script>alert(1)</script></p>', 'instructions' => "Sign in\nRedeem"]);
        storefrontProduct('Steam Wallet $50', '50');
        $definition = DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID', 'input_type' => DeliveryFieldDefinitionInputType::Number]);
        ProductDeliveryField::factory()->create(['product_id' => $product->id, 'delivery_field_definition_id' => $definition->id, 'is_required' => true]);
        $variant = storefrontVariant($product);

        $response = $this->get('/products/'.$product->slug)->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Steam Wallet $20')
            ->assertSee('data-variant-option="'.$variant->public_id.'"', false)
            ->assertSee('data-price="'.storefrontPrice('20.00').'"', false)
            ->assertSee('name="delivery_values[player_id]"', false)
            ->assertSee('data-error-for="delivery_values.player_id"', false)
            ->assertSee('How to redeem')
            ->assertSee('You may also like')
            ->assertSee('Steam Wallet $50')
            ->assertSee('No reviews yet')
            ->assertDontSee('<script>alert(1)</script>', false);

        assertCspSafeHtml($response);
    });

    it('renders the Arabic page right to left with prices left to right', function (): void {
        $product = storefrontProduct('Steam Wallet $20', '20');

        $this->get('/ar/products/'.$product->slug)->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('أضف إلى الحقيبة')
            ->assertSee('dir="ltr" data-variant-price', false);
    });

    it('is 404 for a draft product', function (): void {
        $product = storefrontProduct('Draft card', '10', ['status' => ProductStatus::Draft]);

        $this->get('/products/'.$product->slug)->assertNotFound();
    });
});

describe('search page', function (): void {
    it('finds products by title prefix, counts the term and is noindex', function (): void {
        $product = storefrontProduct('Steam Wallet $20', '20');
        SearchDocument::factory()->create(['searchable_type' => 'product', 'searchable_id' => $product->id, 'title' => 'Steam Wallet $20', 'locale' => 'en']);

        $this->get('/search?q=steam')->assertOk()
            ->assertSee('noindex,follow')
            ->assertSee('1 product for “steam”', false)
            ->assertSee('Steam Wallet $20');

        expect(SearchQuery::query()->where('term', 'steam')->value('hits'))->toBe(1);
    });

    it('shows the empty search page without a term and an empty state without results', function (): void {
        $this->get('/search')->assertOk()->assertSee('Search')->assertDontSee('No results');
        $this->get('/ar/search?q=zzzz')->assertOk()->assertSee('لا توجد نتائج');
    });
});

describe('content pages', function (): void {
    it('serves the built-in legal pages in both languages', function (): void {
        $this->get('/pages/refund-policy')->assertOk()->assertSee('Refund policy')->assertSee('Orders that could not be delivered are refunded.');
        $this->get('/ar/pages/terms')->assertOk()->assertSee('شروط البيع');
    });

    it('prefers a published CMS page and sanitizes its body', function (): void {
        Page::factory()->create(['slug' => 'terms', 'title' => 'Our terms', 'body' => '<p>Signed terms</p>'])->forceFill(['status' => PageStatus::Published, 'body' => '<p>Signed terms</p><script>x()</script>'])->save();

        $this->get('/pages/terms')->assertOk()->assertSee('Our terms')->assertSee('Signed terms')->assertDontSee('<script>x()</script>', false);
    });

    it('is 404 for an unknown or draft page', function (): void {
        Page::factory()->create(['slug' => 'draft-page'])->forceFill(['status' => PageStatus::Draft])->save();

        $this->get('/pages/unknown')->assertNotFound();
        $this->get('/pages/draft-page')->assertNotFound();
    });
});

describe('errors and switchers', function (): void {
    it('serves the English home at / and the Arabic home at /ar without any country redirect', function (): void {
        $this->get('/')->assertOk()->assertHeader('Content-Language', 'en');
        $this->get('/ar')->assertOk()->assertHeader('Content-Language', 'ar');
        $this->get('/eg')->assertNotFound()->assertSee('This vault is empty.');
        $this->withHeaders(['Accept-Language' => 'ar-EG,ar;q=0.9', 'CF-IPCountry' => 'EG'])->get('/')->assertOk()->assertHeader('Content-Language', 'en');
    });

    it('builds every banner image the pages reference through Vite', function (): void {
        $manifest = public_path('build/manifest.json');

        if (! is_file($manifest)) {
            $this->markTestSkipped('No Vite build; run npm run build.');
        }

        $entries = array_keys(json_decode((string) file_get_contents($manifest), true));
        $art = glob(resource_path('assets/images/{hero,category,platform,promo}/*.{webp,avif}'), GLOB_BRACE);

        expect($art)->not->toBeEmpty();

        foreach ($art as $file) {
            expect($entries)->toContain('resources/assets/images/'.basename(dirname($file)).'/'.basename($file));
        }
    });

    it('renders the 404 page for unknown paths, in the language of the path', function (): void {
        $this->get('/does/not/exist')->assertNotFound()->assertSee('This vault is empty.')->assertSee('dir="ltr"', false);
        $this->get('/ar/does/not/exist')->assertNotFound()->assertSee('هذه الخزنة فارغة.')->assertSee('dir="rtl"', false);
    });

    it('renders the 500 page without the storefront chrome', function (): void {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/__m12-failure', static fn () => throw new RuntimeException('boom'));

        $this->get('/__m12-failure')->assertStatus(500)->assertSee('Something went wrong on our side.')->assertDontSee('boom');
    });

    it('links each page to the same page in the other language', function (): void {
        storefrontProduct();

        $this->get('/b/steam?sort=newest')->assertOk()->assertSee('href="'.url('/ar/b/steam').'?sort=newest"', false);
        $this->get('/ar/products')->assertOk()->assertSee('href="'.url('/products').'"', false);
    });

    it('keeps the JSON API envelope for API paths', function (): void {
        $this->getJson('/api/v1/storefront/catalog/products/unknown')->assertNotFound()->assertJsonPath('code', 'not_found');
    });
});
