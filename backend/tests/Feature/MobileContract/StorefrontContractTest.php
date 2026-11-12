<?php

/**
 * Contract fixtures (M-A7, G11): storefront discovery under /api/v1/mobile/storefront (sections
 * 2a, 2b, 2c), in English and Arabic. Fixtures land in docs/api-fixtures/{home,catalog,product,search}.
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
use App\Domains\Catalog\Models\SearchQuery;
use App\Domains\Catalog\Services\SearchIndexer;
use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use App\Domains\Cms\Models\Media;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use App\Domains\Seo\Models\SeoMetadata;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    contractSetUp();
    Cache::flush();
});

function contractArabic(Model $model, string $field, string $value): void
{
    ContentTranslation::factory()->create(['translatable_type' => $model->getMorphClass(), 'translatable_id' => $model->getKey(), 'locale' => 'ar', 'field' => $field, 'value' => $value]);
}

function contractMedia(string $path): Media
{
    return Media::factory()->create(['path' => $path, 'width' => 1200, 'height' => 600, 'conversions' => ['webp' => $path]]);
}

/**
 * The catalog every storefront fixture reads: two platforms, a category tree, two products (one
 * with two regional variants, images, a delivery field, SEO, reviews and Arabic translations).
 *
 * @return array{product: Product, platform: GamingPlatform, category: Category, faq: Faq}
 */
function contractCatalog(): array
{
    CurrencyFactory::ensure('USD');
    CountryFactory::ensure('US');

    $platform = GamingPlatform::factory()->create(['name' => 'Orbit', 'slug' => 'orbit', 'description' => 'Orbit consoles', 'sort_order' => 1, 'is_featured' => true, 'logo_media_id' => contractMedia('media/2026/11/orbit-logo.webp')->id]);
    $nova = GamingPlatform::factory()->create(['name' => 'Nova', 'slug' => 'nova', 'sort_order' => 2]);
    $giftCards = Category::factory()->create(['name' => 'Gift Cards', 'slug' => 'gift-cards', 'description' => 'Store credit', 'sort_order' => 1]);
    $topUps = Category::factory()->create(['name' => 'Top-ups', 'slug' => 'top-ups', 'parent_id' => $giftCards->id, 'sort_order' => 2]);
    $us = Region::factory()->create(['code' => 'US', 'name' => 'United States']);
    $global = Region::factory()->create(['code' => 'GL', 'name' => 'Global']);

    $product = Product::factory()->create([
        'status' => ProductStatus::Active, 'name' => 'Orbit Card', 'slug' => 'orbit-card', 'sku' => 'ORB-CARD',
        'gaming_platform_id' => $platform->id, 'primary_category_id' => $giftCards->id,
        'short_description' => 'Top up Orbit', 'description' => '<p>Load your <strong>wallet</strong>.</p>',
        'instructions' => "Open Orbit.\nEnter the code.", 'terms' => 'Non-refundable once revealed.',
        'rating_avg' => '4.50', 'rating_count' => 2, 'is_featured' => true, 'sales_count' => 40,
        'delivery_time_minutes' => 5, 'max_quantity_per_order' => 10,
    ]);
    $product->regions()->attach([$us->id, $global->id]);
    ProductVariant::factory()->for($product)->create(['title' => '10 USD', 'sku' => 'ORB-10-US', 'region_id' => $us->id, 'denomination_value' => '10', 'denomination_currency' => 'USD', 'availability' => ProductVariantAvailability::InStock, 'manual_cost_base' => '10'])->forceFill(['price_base' => '10'])->save();
    ProductVariant::factory()->for($product)->create(['title' => '25 USD', 'sku' => 'ORB-25-GL', 'region_id' => $global->id, 'denomination_value' => '25', 'denomination_currency' => 'USD', 'availability' => ProductVariantAvailability::Limited, 'manual_cost_base' => '25.5'])->forceFill(['price_base' => '25.5'])->save();
    ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => contractMedia('media/2026/11/orbit-card.webp')->id, 'sort_order' => 1, 'alt' => 'Orbit Card front']);
    $definition = DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID', 'input_type' => DeliveryFieldDefinitionInputType::Text]);
    ProductDeliveryField::factory()->create(['product_id' => $product->id, 'delivery_field_definition_id' => $definition->id, 'is_required' => true]);
    SeoMetadata::factory()->create(['seoable_type' => 'product', 'seoable_id' => $product->id, 'locale' => 'en', 'title' => 'Buy Orbit Card', 'description' => 'Instant delivery']);

    $second = Product::factory()->create(['status' => ProductStatus::Active, 'name' => 'Nova Points', 'slug' => 'nova-points', 'sku' => 'NOVA-PTS', 'gaming_platform_id' => $nova->id, 'primary_category_id' => $topUps->id, 'short_description' => 'Points for Nova', 'sales_count' => 10]);
    ProductVariant::factory()->for($second)->create(['title' => '500 points', 'sku' => 'NOVA-500', 'availability' => ProductVariantAvailability::InStock, 'manual_cost_base' => '5'])->forceFill(['price_base' => '5'])->save();

    foreach ([[5, 'Instant', 'Code arrived in a minute.'], [4, 'Good', 'Worked fine.']] as $index => [$rating, $title, $body]) {
        test()->travel($index)->minutes();
        Review::factory()->create(['product_id' => $product->id, 'status' => ReviewStatus::Approved, 'rating' => $rating, 'title' => $title, 'body' => $body, 'is_featured' => true]);
    }
    test()->travelTo(CONTRACT_NOW);

    contractArabic($product, 'name', 'بطاقة أوربت');
    contractArabic($product, 'short_description', 'اشحن أوربت');
    contractArabic($product, 'description', '<p>اشحن <strong>محفظتك</strong>.</p>');
    contractArabic($platform, 'name', 'أوربت');
    contractArabic($giftCards, 'name', 'بطاقات الهدايا');
    contractArabic($definition, 'label', 'معرف اللاعب');
    contractArabic(ProductVariant::query()->where('sku', 'ORB-10-US')->sole(), 'title', '10 دولار');

    $faq = Faq::factory()->create(['question' => 'How fast is delivery?', 'answer' => 'Codes arrive instantly.', 'show_on_homepage' => true]);

    return ['product' => $product, 'platform' => $platform, 'category' => $giftCards, 'faq' => $faq];
}

function contractHome(): void
{
    ['product' => $product, 'platform' => $platform, 'category' => $category, 'faq' => $faq] = contractCatalog();

    $offers = HomepageSection::factory()->create(['key' => 'special_offers', 'title' => 'Offers', 'subtitle' => 'This week', 'sort_order' => 1]);
    foreach ([['product', $product->id], ['gaming_platform', $platform->id], ['category', $category->id], ['faq', $faq->id]] as $position => [$type, $id]) {
        HomepageSectionItem::factory()->create(['homepage_section_id' => $offers->id, 'item_type' => $type, 'item_id' => $id, 'sort_order' => $position]);
    }
    HomepageSection::factory()->create(['key' => 'best_sellers', 'title' => 'Best sellers', 'source' => HomepageSectionSource::Auto, 'sort_order' => 2, 'settings' => ['limit' => 2]]);
    HomepageSection::factory()->create(['key' => 'customer_reviews', 'title' => 'Reviews', 'source' => HomepageSectionSource::Auto, 'sort_order' => 3, 'settings' => ['limit' => 1]]);
    HomepageSection::factory()->create(['key' => 'trust_indicators', 'title' => 'Why us', 'sort_order' => 4]);
    // Storefront v2: automatic category tiles and a category rail after the managed sections.
    HomepageSection::factory()->create(['key' => 'category_tiles', 'title' => 'Shop by category', 'source' => HomepageSectionSource::Auto, 'sort_order' => 0, 'settings' => ['type' => 'category_tiles']]);
    $category->update(['show_on_home' => true, 'home_position' => 1, 'home_limit' => 4]);

    Banner::factory()->create(['title' => 'Orbit week', 'subtitle' => 'Save on Orbit', 'cta_label' => 'Shop', 'cta_url' => '/products/orbit-card', 'media_id' => contractMedia('media/2026/11/hero.webp')->id, 'mobile_media_id' => contractMedia('media/2026/11/hero-mobile.webp')->id, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'sort_order' => 1]);
    Banner::factory()->create(['title' => 'Search steam', 'placement' => BannerPlacement::HomePromo, 'cta_url' => '/search?q=steam', 'media_id' => contractMedia('media/2026/11/promo.webp')->id, 'sort_order' => 1]);
}

describe('home', function (): void {
    it('returns sections and banners in English and Arabic', function (): void {
        contractHome();

        contractFixture('home/home_en', mobileCall('GET', 'storefront/home')->assertOk());
        contractFixture('home/home_ar', statelessCall('GET', 'api/v1/mobile/storefront/home', headers: ['Accept-Language' => 'ar'])->assertOk()->assertHeader('Content-Language', 'ar'));
    });
});

describe('catalog', function (): void {
    beforeEach(fn () => contractCatalog());

    it('lists products, platforms, categories, countries and currencies', function (string $locale): void {
        $headers = ['Accept-Language' => $locale];

        contractFixture("catalog/products_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/products?sort=popular&per_page=20', headers: $headers)->assertOk());
        contractFixture("catalog/platforms_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/platforms', headers: $headers)->assertOk());
        contractFixture("catalog/categories_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/categories', headers: $headers)->assertOk());
        contractFixture("catalog/countries_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/countries', headers: $headers)->assertOk());
    })->with(['en', 'ar']);

    it('filters the listing and prices it in the X-Currency currency', function (): void {
        givenRate('EGP', '50');

        contractFixture('catalog/products_filtered_egp', statelessCall('GET', 'api/v1/mobile/storefront/catalog/products?platform=orbit&sort=price_asc', headers: ['X-Currency' => 'EGP'])->assertOk());
        contractFixture('catalog/currencies', mobileCall('GET', 'storefront/currencies')->assertOk());
        contractFixture('catalog/currency_invalid', statelessCall('GET', 'api/v1/mobile/storefront/catalog/products', headers: ['X-Currency' => 'XXX'])->assertUnprocessable());
        contractFixture('catalog/products_validation_failed', mobileCall('GET', 'storefront/catalog/products?sort=cheapest')->assertUnprocessable());
    });
});

describe('storefront v2', function (): void {
    it('returns the listing facets, the multi-value listing and the navigation', function (string $locale): void {
        ['category' => $category] = contractCatalog();
        $category->update(['featured_in_header' => true, 'header_order' => 1]);
        $headers = ['Accept-Language' => $locale];

        contractFixture("catalog/facets_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/facets?platform=orbit,nova&in_stock=1', headers: $headers)->assertOk());
        contractFixture("catalog/navigation_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/navigation', headers: $headers)->assertOk());
    })->with(['en', 'ar']);

    it('lists with several values per facet, best selling and the category page facets', function (): void {
        contractCatalog();

        contractFixture('catalog/products_multi', statelessCall('GET', 'api/v1/mobile/storefront/catalog/products?category=gift-cards,top-ups&region=US,GL&sort=best_selling')->assertOk());
        contractFixture('catalog/facets_category_page', statelessCall('GET', 'api/v1/mobile/storefront/catalog/facets?category=gift-cards&hide=category', headers: ['X-Currency' => 'USD'])->assertOk());
    });

    it('returns the card add-to-bag chooser', function (string $locale): void {
        contractCatalog();

        contractFixture("product/quick_add_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card/quick-add', headers: ['Accept-Language' => $locale])->assertOk());
    })->with(['en', 'ar']);
});

describe('product', function (): void {
    beforeEach(fn () => contractCatalog());

    it('returns the detail, variants and reviews in English and Arabic', function (string $locale): void {
        $headers = ['Accept-Language' => $locale];

        contractFixture("product/detail_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card', headers: $headers)->assertOk());
        contractFixture("product/variants_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card/variants?country=US', headers: $headers)->assertOk());
        contractFixture("product/reviews_{$locale}", statelessCall('GET', 'api/v1/mobile/storefront/catalog/products/orbit-card/reviews?per_page=10', headers: $headers)->assertOk());
    })->with(['en', 'ar']);

    it('answers 404 for an unknown product', function (): void {
        contractFixture('product/detail_not_found', mobileCall('GET', 'storefront/catalog/products/no-such-card')->assertNotFound());
    });
});

describe('search', function (): void {
    beforeEach(fn () => contractCatalog());

    it('suggests and lists popular terms', function (): void {
        app(SearchIndexer::class)->rebuild();
        SearchQuery::factory()->create(['term' => 'orbit', 'locale' => 'en', 'hits' => 12, 'results_count' => 1]);
        SearchQuery::factory()->create(['term' => 'nova points', 'locale' => 'en', 'hits' => 4, 'results_count' => 1]);

        contractFixture('search/suggest', mobileCall('GET', 'storefront/search/suggest?q=orb')->assertOk());
        contractFixture('search/popular', mobileCall('GET', 'storefront/search/popular')->assertOk());
        contractFixture('search/suggest_validation_failed', mobileCall('GET', 'storefront/search/suggest?q=o')->assertUnprocessable());
    });
});
