<?php

/**
 * M-A4 (gap G7): product detail, platforms, categories, countries and home for the web
 * (`/api/v1/storefront`) and the app (`/api/v1/mobile/storefront`), localized, with absolute
 * media URLs and short public caching.
 */

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Services\DeliveryFieldValidator;
use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use App\Domains\Cms\Models\Media;
use App\Domains\Currency\Models\Currency;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Shared\Models\Country;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Hard query budgets per endpoint with a cold cache (API_DOCUMENTATION.md 2c). */
const DISCOVERY_QUERY_BUDGET = ['detail' => 15, 'platforms' => 6, 'categories' => 6, 'countries' => 3, 'home' => 30];

/** Both bases serve the same controllers. */
const DISCOVERY_BASES = ['web' => 'api/v1/storefront', 'app' => 'api/v1/mobile/storefront'];

function discoveryTranslation(Model $model, string $field, string $value): void
{
    ContentTranslation::factory()->create(['translatable_type' => $model->getMorphClass(), 'translatable_id' => $model->getKey(), 'locale' => 'ar', 'field' => $field, 'value' => $value]);
}

/**
 * An active product with one priced, in-stock variant.
 *
 * @param  array<string, mixed>  $product
 */
function discoveryProduct(string $name, string $price = '10', array $product = []): Product
{
    CurrencyFactory::ensure('USD');

    $model = Product::factory()->create(['status' => ProductStatus::Active, 'name' => $name, ...$product]);
    ProductVariant::factory()->for($model)->create(['availability' => ProductVariantAvailability::InStock])->forceFill(['price_base' => $price])->save();

    return $model;
}

function discoveryMedia(string $path = 'media/2026/11/hero.webp'): Media
{
    return Media::factory()->create(['path' => $path, 'width' => 1200, 'height' => 600, 'conversions' => ['webp' => $path, 'avif' => str_replace('.webp', '.avif', $path)]]);
}

/**
 * Count the queries one request runs with a cold cache and a fresh request scope (the
 * translator is scoped to a request and controllers hold it; a test does not end the request
 * scope or drop resolved controllers between calls).
 *
 * @param  array<string, string>  $headers
 */
function discoveryQueries(string $path, array $headers = []): int
{
    Cache::flush();
    app()->forgetScopedInstances();
    foreach (app('router')->getRoutes() as $route) {
        $route->flushController();
    }
    DB::flushQueryLog();
    DB::enableQueryLog();
    statelessCall('GET', $path, headers: $headers)->assertOk();
    DB::disableQueryLog();

    return count(DB::getQueryLog());
}

describe('product detail', function (): void {
    it('returns the product page on both bases with absolute images, fields, rating and SEO', function (string $base): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Orbit', 'slug' => 'orbit']);
        $category = Category::factory()->create(['name' => 'Gift Cards', 'slug' => 'gift-cards']);
        $region = Region::factory()->create(['code' => 'US', 'name' => 'United States']);
        $product = discoveryProduct('Orbit Card', '10', [
            'slug' => 'orbit-card',
            'gaming_platform_id' => $platform->id,
            'primary_category_id' => $category->id,
            'short_description' => 'Top up Orbit',
            'description' => '<p>Load your <strong>wallet</strong>.</p>',
            'instructions' => 'Open Orbit and enter the code.',
            'terms' => 'Non-refundable once revealed.',
            'rating_avg' => '4.50',
            'rating_count' => 8,
        ]);
        $product->regions()->attach($region->id);
        $product->productVariants()->first()->update(['region_id' => $region->id]);
        ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => discoveryMedia('media/2026/11/b.webp')->id, 'sort_order' => 2]);
        ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => discoveryMedia('media/2026/11/a.webp')->id, 'sort_order' => 1, 'alt' => 'Front']);
        SeoMetadata::factory()->create(['seoable_type' => 'product', 'seoable_id' => $product->id, 'locale' => 'en', 'title' => 'Buy Orbit Card', 'description' => 'Instant delivery']);

        $response = statelessCall('GET', "{$base}/catalog/products/orbit-card")
            ->assertOk()
            ->assertJsonPath('meta.display_currency', 'USD')
            ->assertJsonPath('data.product.name', 'Orbit Card')
            ->assertJsonPath('data.product.slug', 'orbit-card')
            ->assertJsonPath('data.product.platform', ['name' => 'Orbit', 'slug' => 'orbit', 'logo' => null])
            ->assertJsonPath('data.product.category', ['name' => 'Gift Cards', 'slug' => 'gift-cards'])
            ->assertJsonPath('data.product.description', '<p>Load your <strong>wallet</strong>.</p>')
            ->assertJsonPath('data.product.instructions', 'Open Orbit and enter the code.')
            ->assertJsonPath('data.product.terms', 'Non-refundable once revealed.')
            ->assertJsonPath('data.product.price_from.amount', '10.00')
            ->assertJsonPath('data.product.rating', ['average' => '4.50', 'count' => 8])
            ->assertJsonPath('data.product.availability', 'in_stock')
            ->assertJsonPath('data.product.regions', [['code' => 'US', 'name' => 'United States']])
            ->assertJsonPath('data.product.variants_summary', ['count' => 1, 'regions' => [['code' => 'US', 'name' => 'United States']], 'has_custom_amount' => false])
            ->assertJsonPath('data.product.seo.title', 'Buy Orbit Card')
            ->assertJsonPath('data.product.images.0.path', 'media/2026/11/a.webp')
            ->assertJsonPath('data.product.images.0.alt', 'Front')
            ->assertJsonPath('data.product.images.0.width', 1200)
            ->assertJsonCount(2, 'data.product.images');

        $appUrl = rtrim((string) config('app.url'), '/');
        expect($response->json('data.product.images.0.url'))->toBe("{$appUrl}/storage/media/2026/11/a.webp")
            ->and($response->json('data.product.images.0.formats.avif'))->toBe("{$appUrl}/storage/media/2026/11/a.avif")
            ->and($response->json('data.product.image.url'))->toBe("{$appUrl}/storage/media/2026/11/a.webp");

        expect($response->getContent())->not->toContain('manual_cost_base')->not->toContain('price_base')->not->toContain('margin')->not->toContain('supplier');
    })->with(DISCOVERY_BASES);

    it('localizes text and field labels in Arabic, sanitizes translated HTML and falls back to English', function (): void {
        $product = discoveryProduct('Orbit Card', '10', ['slug' => 'orbit-card', 'description' => '<p>English</p>', 'terms' => 'English terms']);
        $definition = DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID']);
        ProductDeliveryField::factory()->create(['product_id' => $product->id, 'delivery_field_definition_id' => $definition->id, 'is_required' => true]);
        discoveryTranslation($product, 'name', 'بطاقة أوربت');
        discoveryTranslation($product, 'description', '<p>وصف</p><script>alert(1)</script>');
        discoveryTranslation($definition, 'label', 'معرف اللاعب');

        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card', headers: ['Accept-Language' => 'ar'])
            ->assertOk()
            ->assertHeader('Content-Language', 'ar')
            ->assertJsonPath('data.product.name', 'بطاقة أوربت')
            ->assertJsonPath('data.product.description', '<p>وصف</p>')
            ->assertJsonPath('data.product.terms', 'English terms')
            ->assertJsonPath('data.product.seo.title', 'بطاقة أوربت')
            ->assertJsonPath('data.product.delivery_fields.0.label', 'معرف اللاعب');

        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card')
            ->assertOk()->assertJsonPath('data.product.name', 'Orbit Card')->assertJsonPath('data.product.delivery_fields.0.label', 'Player ID');
    });

    it('returns 404 for a draft, inactive, deleted or unknown product', function (array $state): void {
        $product = discoveryProduct('Hidden', '10', ['slug' => 'hidden-card']);
        $product->forceFill($state)->save();

        if ($state === []) {
            $product->delete();
        }

        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/hidden-card')->assertNotFound()->assertJsonPath('code', 'not_found');
        statelessCall('GET', 'api/v1/storefront/catalog/products/no-such-card')->assertNotFound();
    })->with([
        'draft' => [['status' => ProductStatus::Draft]],
        'inactive' => [['status' => ProductStatus::Inactive]],
        'deleted' => [[]],
    ]);

    it('prices the product in the X-Currency currency as the listing does', function (): void {
        givenRate('EGP', '50');
        Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
        discoveryProduct('Orbit Card', '10', ['slug' => 'orbit-card']);

        $detail = statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card', headers: ['X-Currency' => 'EGP'])->assertOk();
        $listing = statelessCall('GET', 'api/v1/mobile/storefront/catalog/products', headers: ['X-Currency' => 'EGP'])->assertOk();

        expect($detail->json('data.product.price_from'))->toBe($listing->json('data.0.price_from'))
            ->and($detail->json('data.product.price_from.currency'))->toBe('EGP');
    });

    it('sends short public cache headers and answers 304 to a matching ETag', function (): void {
        discoveryProduct('Orbit Card', '10', ['slug' => 'orbit-card']);

        $response = statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card')->assertOk();

        expect($response->headers->get('Cache-Control'))->toContain('public')->toContain('max-age=60')
            ->and($response->headers->get('Vary'))->toContain('Accept-Language')->toContain('X-Currency')
            ->and($response->headers->get('ETag'))->not->toBeNull();

        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card', headers: ['If-None-Match' => $response->headers->get('ETag')])->assertStatus(304);
        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/missing')->assertNotFound()->assertHeaderMissing('ETag');
    });

    it('stays within its query budget however many images and fields the product has', function (): void {
        $product = discoveryProduct('Orbit Card', '10', ['slug' => 'orbit-card']);
        $seed = function () use ($product): void {
            ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => discoveryMedia('media/'.fake()->uuid().'.webp')->id]);
            ProductDeliveryField::factory()->create(['product_id' => $product->id]);
        };

        $seed();
        $few = discoveryQueries('api/v1/mobile/storefront/catalog/products/orbit-card', ['Accept-Language' => 'ar']);
        foreach (range(1, 6) as $unused) {
            $seed();
        }
        $many = discoveryQueries('api/v1/mobile/storefront/catalog/products/orbit-card', ['Accept-Language' => 'ar']);

        expect($many)->toBe($few)->toBeLessThanOrEqual(DISCOVERY_QUERY_BUDGET['detail']);
    });
});

describe('delivery field definitions', function (): void {
    beforeEach(function (): void {
        $this->variant = sellableVariant('10', [], ['slug' => 'topup-card']);
        $fields = [
            [['code' => 'player_id', 'label' => 'Player ID', 'input_type' => DeliveryFieldDefinitionInputType::Text, 'validation_regex' => '[0-9]{6,10}'], true, 1],
            [['code' => 'server', 'label' => 'Server', 'input_type' => DeliveryFieldDefinitionInputType::Select, 'options' => [['value' => 'eu', 'label' => 'Europe'], ['value' => 'asia', 'label' => 'Asia']]], true, 2],
            [['code' => 'zone_id', 'label' => 'Zone', 'input_type' => DeliveryFieldDefinitionInputType::Number], false, 3],
            [['code' => 'contact', 'label' => 'Contact email', 'input_type' => DeliveryFieldDefinitionInputType::Email], false, 4],
        ];

        foreach ($fields as [$definition, $required, $sort]) {
            ProductDeliveryField::factory()->create([
                'product_id' => $this->variant->product_id,
                'delivery_field_definition_id' => DeliveryFieldDefinition::factory()->create($definition)->id,
                'is_required' => $required,
                'sort_order' => $sort,
            ]);
        }
    });

    it('publishes exactly the specs the cart validator enforces', function (): void {
        $published = statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/topup-card')->assertOk()->json('data.product.delivery_fields');

        $specs = app(DeliveryFieldValidator::class)->specsFor(Product::query()->findOrFail($this->variant->product_id));

        expect($published)->toBe(array_map(static fn ($spec): array => [
            'key' => $spec->code,
            'label' => $spec->label,
            'help_text' => $spec->definition->help_text,
            'type' => $spec->type->value,
            'required' => $spec->required,
            'max_length' => $spec->maxLength,
            'pattern' => $spec->pattern,
            'options' => $spec->options === [] ? null : $spec->options,
        ], $specs))
            ->and(array_column($published, 'key'))->toBe(['player_id', 'server', 'zone_id', 'contact'])
            ->and($published[1]['options'])->toBe([['value' => 'eu', 'label' => 'Europe'], ['value' => 'asia', 'label' => 'Asia']]);
    });

    it('accepts values built from the published definitions and refuses values that break them', function (): void {
        $fields = collect(statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/topup-card')->json('data.product.delivery_fields'))->keyBy('key');

        $valid = [
            'player_id' => '1234567',
            'server' => $fields['server']['options'][0]['value'],
            'zone_id' => '42',
            'contact' => 'player@example.com',
        ];

        expect(preg_match('/^(?:'.$fields['player_id']['pattern'].')$/u', $valid['player_id']))->toBe(1);

        statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1, 'delivery_values' => $valid])
            ->assertCreated()
            ->assertJsonPath('data.cart.items.0.delivery_values', $valid);

        $invalid = [
            'player_id' => '12ab',
            'server' => 'mars',
            'zone_id' => '4.2',
            'contact' => 'not-an-email',
            'nickname' => 'x',
        ];

        statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1, 'delivery_values' => $invalid])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'delivery_values.player_id', 'delivery_values.server', 'delivery_values.zone_id', 'delivery_values.contact', 'delivery_values.nickname',
            ]);

        statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1, 'delivery_values' => ['zone_id' => '1']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['delivery_values.player_id', 'delivery_values.server'])
            ->assertJsonMissingValidationErrors(['delivery_values.zone_id', 'delivery_values.contact']);
    });
});

describe('absolute image URLs on the listing', function (): void {
    it('keeps the stored path and adds an absolute URL from the disk or the configured CDN', function (): void {
        $product = discoveryProduct('Orbit Card');
        ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => discoveryMedia('media/2026/11/card.webp')->id]);

        statelessCall('GET', 'api/v1/storefront/catalog/products')
            ->assertOk()
            ->assertJsonPath('data.0.image.path', 'media/2026/11/card.webp')
            ->assertJsonPath('data.0.image.url', rtrim((string) config('app.url'), '/').'/storage/media/2026/11/card.webp');

        config(['catalog.media.base_url' => 'https://cdn.boxcards.shop/']);

        statelessCall('GET', 'api/v1/mobile/storefront/catalog/products')
            ->assertOk()
            ->assertJsonPath('data.0.image.url', 'https://cdn.boxcards.shop/media/2026/11/card.webp')
            ->assertJsonPath('data.0.image.formats.avif', 'https://cdn.boxcards.shop/media/2026/11/card.avif');
    });
});

describe('platforms and categories', function (): void {
    it('lists active platforms in order with listed-product counts and only uploaded images', function (string $base): void {
        $logo = discoveryMedia('media/orbit-logo.webp');
        $orbit = GamingPlatform::factory()->create(['name' => 'Orbit', 'slug' => 'orbit', 'sort_order' => 2, 'logo_media_id' => $logo->id]);
        $nova = GamingPlatform::factory()->create(['name' => 'Nova', 'slug' => 'nova', 'sort_order' => 1]);
        GamingPlatform::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'is_active' => false]);
        discoveryProduct('A', '10', ['gaming_platform_id' => $orbit->id]);
        discoveryProduct('B', '10', ['gaming_platform_id' => $orbit->id]);
        discoveryProduct('Draft', '10', ['gaming_platform_id' => $orbit->id, 'status' => ProductStatus::Draft]);
        Product::factory()->create(['status' => ProductStatus::Active, 'gaming_platform_id' => $orbit->id]);

        $response = statelessCall('GET', "{$base}/platforms")
            ->assertOk()
            ->assertJsonPath('data.platforms.*.slug', ['nova', 'orbit'])
            ->assertJsonPath('data.platforms.0.product_count', 0)
            ->assertJsonPath('data.platforms.0.logo', null)
            ->assertJsonPath('data.platforms.1.product_count', 2)
            ->assertJsonPath('data.platforms.1.logo.path', 'media/orbit-logo.webp');

        expect($response->json('data.platforms.1.logo.url'))->toStartWith('http');
        expect($nova->exists)->toBeTrue();
    })->with(DISCOVERY_BASES);

    it('lists active categories with parents first, hides children of inactive parents and counts secondary products', function (): void {
        $giftCards = Category::factory()->create(['name' => 'Gift Cards', 'slug' => 'gift-cards', 'sort_order' => 1]);
        $steam = Category::factory()->create(['name' => 'Steam', 'slug' => 'steam', 'parent_id' => $giftCards->id]);
        $retired = Category::factory()->create(['slug' => 'retired', 'is_active' => false]);
        Category::factory()->create(['slug' => 'orphan', 'parent_id' => $retired->id]);
        Category::factory()->create(['slug' => 'off', 'is_active' => false]);
        discoveryProduct('Primary', '10', ['primary_category_id' => $giftCards->id]);
        discoveryProduct('Secondary', '10', ['primary_category_id' => $steam->id])->categories()->attach($giftCards->id);

        statelessCall('GET', 'api/v1/mobile/storefront/categories')
            ->assertOk()
            ->assertJsonPath('data.categories.*.slug', ['gift-cards', 'steam'])
            ->assertJsonPath('data.categories.0.product_count', 2)
            ->assertJsonPath('data.categories.0.parent', null)
            ->assertJsonPath('data.categories.1.parent', 'gift-cards')
            ->assertJsonPath('data.categories.1.product_count', 1);
    });

    it('localizes platform and category names in Arabic', function (): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Orbit']);
        $category = Category::factory()->create(['name' => 'Gift Cards']);
        discoveryTranslation($platform, 'name', 'أوربت');
        discoveryTranslation($category, 'name', 'بطاقات الهدايا');

        statelessCall('GET', 'api/v1/mobile/storefront/platforms', headers: ['Accept-Language' => 'ar'])->assertOk()->assertJsonPath('data.platforms.0.name', 'أوربت');
        statelessCall('GET', 'api/v1/mobile/storefront/categories', headers: ['Accept-Language' => 'ar'])->assertOk()->assertJsonPath('data.categories.0.name', 'بطاقات الهدايا');
        statelessCall('GET', 'api/v1/mobile/storefront/platforms')->assertOk()->assertJsonPath('data.platforms.0.name', 'Orbit')
            ->assertHeader('Cache-Control', 'max-age=300, public');
    });

    it('stays within its query budget whatever the number of platforms and categories', function (): void {
        $seed = function (int $count): void {
            foreach (range(1, $count) as $unused) {
                $platform = GamingPlatform::factory()->create(['logo_media_id' => discoveryMedia('media/'.fake()->uuid().'.webp')->id]);
                discoveryProduct(fake()->words(2, true), '10', ['gaming_platform_id' => $platform->id, 'primary_category_id' => Category::factory()->create()->id]);
            }
        };

        $seed(2);
        $few = [discoveryQueries('api/v1/mobile/storefront/platforms', ['Accept-Language' => 'ar']), discoveryQueries('api/v1/mobile/storefront/categories', ['Accept-Language' => 'ar'])];
        $seed(10);
        $many = [discoveryQueries('api/v1/mobile/storefront/platforms', ['Accept-Language' => 'ar']), discoveryQueries('api/v1/mobile/storefront/categories', ['Accept-Language' => 'ar'])];

        expect($many)->toBe($few)
            ->and($many[0])->toBeLessThanOrEqual(DISCOVERY_QUERY_BUDGET['platforms'])
            ->and($many[1])->toBeLessThanOrEqual(DISCOVERY_QUERY_BUDGET['categories']);
    });
});

describe('countries', function (): void {
    beforeEach(function (): void {
        Country::query()->create(['code' => 'EG', 'name' => 'Egypt', 'dial_code' => '+20', 'default_currency' => null, 'is_active' => true]);
        Country::query()->create(['code' => 'AE', 'name' => 'United Arab Emirates', 'is_active' => true]);
        Country::query()->create(['code' => 'KP', 'name' => 'North Korea', 'is_active' => false]);
    });

    it('lists the active countries, localized, on both bases', function (string $base): void {
        statelessCall('GET', "{$base}/countries")
            ->assertOk()
            ->assertJsonPath('data.countries.*.code', ['EG', 'AE'])
            ->assertJsonPath('data.countries.0', ['code' => 'EG', 'name' => 'Egypt', 'dial_code' => '+20', 'default_currency' => null]);

        $arabic = statelessCall('GET', "{$base}/countries", headers: ['Accept-Language' => 'ar'])->assertOk()->json('data.countries');

        expect(collect($arabic)->firstWhere('code', 'EG')['name'])->toBe('مصر')
            ->and(collect($arabic)->pluck('code')->sort()->values()->all())->toBe(['AE', 'EG']);
    })->with(DISCOVERY_BASES);

    it('lists exactly the countries checkout validation accepts', function (): void {
        $listed = statelessCall('GET', 'api/v1/mobile/storefront/countries')->json('data.countries.*.code');

        foreach (Country::query()->pluck('code') as $code) {
            $response = statelessCall('POST', 'api/v1/mobile/checkout/validate', ['country' => $code], ['X-Cart-Token' => str_repeat('a', 64)]);

            if (in_array($code, $listed, true)) {
                $response->assertUnprocessable()->assertJsonPath('code', 'cart_empty');
            } else {
                $response->assertUnprocessable()->assertJsonValidationErrors(['country']);
            }
        }

        expect($listed)->not->toContain('KP');
        expect(discoveryQueries('api/v1/mobile/storefront/countries'))->toBeLessThanOrEqual(DISCOVERY_QUERY_BUDGET['countries']);
    });
});

describe('home', function (): void {
    it('returns active sections in order with manual items resolved and hidden items dropped', function (string $base): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Orbit', 'slug' => 'orbit']);
        $category = Category::factory()->create(['name' => 'Gift Cards', 'slug' => 'gift-cards']);
        $visible = discoveryProduct('Visible', '10', ['slug' => 'visible']);
        $draft = discoveryProduct('Draft', '10', ['slug' => 'draft', 'status' => ProductStatus::Draft]);
        $faq = Faq::factory()->create(['question' => 'How fast?', 'answer' => 'Instant.']);

        $featured = HomepageSection::factory()->create(['key' => 'special_offers', 'title' => 'Offers', 'sort_order' => 20]);
        foreach ([['product', $draft->id], ['product', $visible->id], ['gaming_platform', $platform->id], ['category', $category->id], ['faq', $faq->id]] as $position => [$type, $id]) {
            HomepageSectionItem::factory()->create(['homepage_section_id' => $featured->id, 'item_type' => $type, 'item_id' => $id, 'sort_order' => $position]);
        }
        HomepageSection::factory()->create(['key' => 'trust_indicators', 'sort_order' => 10]);
        HomepageSection::factory()->create(['key' => 'newsletter', 'sort_order' => 5, 'is_active' => false]);

        $response = statelessCall('GET', "{$base}/home")
            ->assertOk()
            ->assertJsonPath('data.sections.*.key', ['trust_indicators', 'special_offers'])
            ->assertJsonPath('data.sections.0.items', [])
            ->assertJsonPath('data.sections.1.title', 'Offers')
            ->assertJsonPath('data.sections.1.items.*.type', ['product', 'platform', 'category', 'faq'])
            ->assertJsonPath('data.sections.1.items.0.data.slug', 'visible')
            ->assertJsonPath('data.sections.1.items.0.data.link', ['type' => 'product', 'value' => 'visible', 'url' => rtrim((string) config('app.url'), '/').'/products/visible'])
            ->assertJsonPath('data.sections.1.items.1.data.link.type', 'platform')
            ->assertJsonPath('data.sections.1.items.2.data.link', ['type' => 'category', 'value' => 'gift-cards', 'url' => rtrim((string) config('app.url'), '/').'/gift-cards'])
            ->assertJsonPath('data.sections.1.items.3.data.question', 'How fast?');

        expect($response->headers->get('Cache-Control'))->toContain('public')->toContain('max-age=60');
    })->with(DISCOVERY_BASES);

    it('fills automatic sections from the listing rules', function (): void {
        discoveryProduct('Seller', '10', ['sales_count' => 50]);
        discoveryProduct('Quiet', '10', ['sales_count' => 1, 'is_trending' => true]);
        discoveryProduct('Unpriced', '10', ['sales_count' => 99])->productVariants()->update(['price_base' => null]);
        HomepageSection::factory()->create(['key' => 'best_sellers', 'source' => HomepageSectionSource::Auto, 'sort_order' => 1, 'settings' => ['limit' => 1]]);
        HomepageSection::factory()->create(['key' => 'trending_games', 'source' => HomepageSectionSource::Auto, 'sort_order' => 2]);
        HomepageSection::factory()->create(['key' => 'my_list', 'source' => HomepageSectionSource::Auto, 'sort_order' => 3, 'settings' => ['rule' => 'newest']]);

        statelessCall('GET', 'api/v1/mobile/storefront/home')
            ->assertOk()
            ->assertJsonPath('data.sections.0.rule', 'best_sellers')
            ->assertJsonPath('data.sections.0.items.*.data.name', ['Seller'])
            ->assertJsonPath('data.sections.1.rule', 'trending')
            ->assertJsonPath('data.sections.1.items.*.data.name', ['Quiet'])
            ->assertJsonPath('data.sections.2.rule', 'newest')
            ->assertJsonCount(2, 'data.sections.2.items');
    });

    it('shows featured approved reviews of active products only', function (): void {
        $product = discoveryProduct('Reviewed', '10', ['slug' => 'reviewed']);
        Review::factory()->create(['product_id' => $product->id, 'status' => ReviewStatus::Approved, 'is_featured' => true, 'title' => 'Great']);
        Review::factory()->create(['product_id' => $product->id, 'status' => ReviewStatus::Approved, 'is_featured' => false, 'title' => 'Plain']);
        Review::factory()->create(['product_id' => $product->id, 'status' => ReviewStatus::Pending, 'is_featured' => true, 'title' => 'Pending']);
        HomepageSection::factory()->create(['key' => 'customer_reviews', 'source' => HomepageSectionSource::Auto]);

        statelessCall('GET', 'api/v1/mobile/storefront/home')
            ->assertOk()
            ->assertJsonPath('data.sections.0.items.*.data.title', ['Great'])
            ->assertJsonPath('data.sections.0.items.0.data.product.slug', 'reviewed');
    });

    it('shows only active banners inside their schedule, with images and structured links', function (): void {
        $this->travelTo(now()->startOfMinute());
        $desktop = discoveryMedia('media/hero-desktop.webp');
        $mobile = discoveryMedia('media/hero-mobile.webp');
        Banner::factory()->create(['title' => 'Now', 'media_id' => $desktop->id, 'mobile_media_id' => $mobile->id, 'cta_url' => '/products/orbit-card', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'sort_order' => 2]);
        Banner::factory()->create(['title' => 'Open', 'cta_url' => 'https://partner.example.com/deal', 'sort_order' => 1]);
        Banner::factory()->create(['title' => 'Promo', 'placement' => BannerPlacement::HomePromo, 'cta_url' => '/search?q=steam']);
        Banner::factory()->create(['title' => 'Category page', 'placement' => BannerPlacement::Category]);
        Banner::factory()->create(['title' => 'Future', 'starts_at' => now()->addMinute()]);
        Banner::factory()->create(['title' => 'Ended', 'ends_at' => now()]);
        Banner::factory()->create(['title' => 'Off', 'is_active' => false]);
        Banner::factory()->create(['title' => 'Deleted'])->delete();

        $appUrl = rtrim((string) config('app.url'), '/');

        $response = statelessCall('GET', 'api/v1/mobile/storefront/home')
            ->assertOk()
            ->assertJsonPath('data.banners.hero.*.title', ['Open', 'Now'])
            ->assertJsonPath('data.banners.home_promo.*.title', ['Promo'])
            ->assertJsonPath('data.banners.hero.1.link', ['type' => 'product', 'value' => 'orbit-card', 'url' => "{$appUrl}/products/orbit-card"])
            ->assertJsonPath('data.banners.hero.0.link', ['type' => 'url', 'value' => 'https://partner.example.com/deal', 'url' => 'https://partner.example.com/deal'])
            ->assertJsonPath('data.banners.home_promo.0.link.type', 'search')
            ->assertJsonPath('data.banners.home_promo.0.link.value', 'steam')
            ->assertJsonPath('data.banners.hero.1.image.desktop.url', "{$appUrl}/storage/media/hero-desktop.webp")
            ->assertJsonPath('data.banners.hero.1.image.mobile.url', "{$appUrl}/storage/media/hero-mobile.webp")
            ->assertJsonPath('data.banners.hero.0.image.mobile', null);

        expect($response->getContent())->not->toContain('Future')->not->toContain('Ended')->not->toContain('Category page')->not->toContain('Deleted');
    });

    it('maps stored web links to app targets', function (?string $link, ?array $target): void {
        Banner::factory()->create(['cta_url' => $link]);
        $appUrl = rtrim((string) config('app.url'), '/');

        $resolved = statelessCall('GET', 'api/v1/mobile/storefront/home')->assertOk()->json('data.banners.hero.0.link');

        expect($resolved === null ? null : ['type' => $resolved['type'], 'value' => str_replace($appUrl, 'APP', $resolved['value'])])->toBe($target);
    })->with([
        'product' => ['/products/orbit-card', ['type' => 'product', 'value' => 'orbit-card']],
        'arabic prefix' => ['/ar/gaming/orbit', ['type' => 'platform', 'value' => 'orbit']],
        'category' => ['/gift-cards', ['type' => 'category', 'value' => 'gift-cards']],
        'search' => ['/search?q=steam+card', ['type' => 'search', 'value' => 'steam card']],
        'reserved path' => ['/blog/news', ['type' => 'url', 'value' => 'APP/blog/news']],
        // app.url is the setting session.app_url (owner decision 11); in tests its config default.
        'same host' => ['http://localhost/products/orbit-card', ['type' => 'product', 'value' => 'orbit-card']],
        'external' => ['https://example.com/products/x', ['type' => 'url', 'value' => 'https://example.com/products/x']],
        'none' => [null, null],
    ]);

    it('prices carousel products exactly as the listing in another currency', function (): void {
        givenRate('EGP', '48.5');
        Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
        $manual = discoveryProduct('Manual', '7.25', ['sales_count' => 1]);
        discoveryProduct('Auto', '12.40', ['sales_count' => 9]);
        $section = HomepageSection::factory()->create(['key' => 'special_offers', 'sort_order' => 1]);
        HomepageSectionItem::factory()->create(['homepage_section_id' => $section->id, 'item_type' => 'product', 'item_id' => $manual->id]);
        HomepageSection::factory()->create(['key' => 'best_sellers', 'source' => HomepageSectionSource::Auto, 'sort_order' => 2]);

        $home = statelessCall('GET', 'api/v1/mobile/storefront/home', headers: ['X-Currency' => 'EGP'])->assertOk()->assertJsonPath('meta.display_currency', 'EGP');
        $listing = collect(statelessCall('GET', 'api/v1/mobile/storefront/catalog/products', headers: ['X-Currency' => 'EGP'])->assertOk()->json('data'))->keyBy('slug');

        $carouselPrices = collect($home->json('data.sections'))->flatMap(fn (array $section): array => $section['items'])->mapWithKeys(fn (array $item): array => [$item['data']['slug'] => $item['data']['price_from']]);

        expect($carouselPrices)->toHaveCount(2);
        foreach ($carouselPrices as $slug => $price) {
            expect($price)->toBe($listing[$slug]['price_from'])->and($price['currency'])->toBe('EGP');
        }
    });

    it('localizes section, banner and product text in Arabic', function (): void {
        $product = discoveryProduct('Orbit Card');
        $section = HomepageSection::factory()->create(['key' => 'special_offers', 'title' => 'Offers']);
        HomepageSectionItem::factory()->create(['homepage_section_id' => $section->id, 'item_type' => 'product', 'item_id' => $product->id]);
        $banner = Banner::factory()->create(['title' => 'Sale', 'cta_label' => 'Shop']);
        discoveryTranslation($section, 'title', 'عروض');
        discoveryTranslation($banner, 'title', 'تخفيضات');
        discoveryTranslation($product, 'name', 'بطاقة أوربت');

        statelessCall('GET', 'api/v1/mobile/storefront/home', headers: ['Accept-Language' => 'ar'])
            ->assertOk()
            ->assertJsonPath('data.sections.0.title', 'عروض')
            ->assertJsonPath('data.sections.0.items.0.data.name', 'بطاقة أوربت')
            ->assertJsonPath('data.banners.hero.0.title', 'تخفيضات')
            ->assertJsonPath('data.banners.hero.0.cta_label', 'Shop');

        statelessCall('GET', 'api/v1/mobile/storefront/home')->assertOk()->assertJsonPath('data.sections.0.title', 'Offers');
    });

    it('stays within its query budget however many items the sections hold', function (): void {
        $manual = HomepageSection::factory()->create(['key' => 'special_offers', 'sort_order' => 1]);
        foreach (['best_sellers', 'recently_added', 'trending_games', 'recommended', 'customer_reviews', 'faq'] as $position => $key) {
            HomepageSection::factory()->create(['key' => $key, 'source' => HomepageSectionSource::Auto, 'sort_order' => $position + 2]);
        }

        $seed = function (int $count) use ($manual): void {
            foreach (range(1, $count) as $unused) {
                $product = discoveryProduct(fake()->words(2, true), '10', ['is_trending' => true, 'is_featured' => true, 'gaming_platform_id' => GamingPlatform::factory()->create()->id]);
                ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => discoveryMedia('media/'.fake()->uuid().'.webp')->id]);
                Review::factory()->create(['product_id' => $product->id, 'status' => ReviewStatus::Approved, 'is_featured' => true]);
                Faq::factory()->create(['show_on_homepage' => true]);
                Banner::factory()->create();
                foreach ([['product', $product->id], ['gaming_platform', $product->gaming_platform_id], ['category', $product->primary_category_id]] as [$type, $id]) {
                    HomepageSectionItem::factory()->create(['homepage_section_id' => $manual->id, 'item_type' => $type, 'item_id' => $id]);
                }
            }
        };

        givenRate('EGP', '50');
        Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
        $seed(2);
        $few = discoveryQueries('api/v1/mobile/storefront/home', ['Accept-Language' => 'ar', 'X-Currency' => 'EGP']);
        $seed(8);
        $many = discoveryQueries('api/v1/mobile/storefront/home', ['Accept-Language' => 'ar', 'X-Currency' => 'EGP']);

        expect($many)->toBe($few)->toBeLessThanOrEqual(DISCOVERY_QUERY_BUDGET['home']);
    });
});
