<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use App\Domains\Suppliers\Adapters\Reloadly\ReloadlyGiftCardAdapter;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Jobs\FetchSupplierImageCandidate;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Services\SupplierBrandLogos;
use App\Domains\Suppliers\Services\SupplierCatalogImporter;
use App\Domains\Suppliers\Services\SupplierCategoryImages;
use App\Domains\Suppliers\Services\SupplierImageCandidates;
use App\Domains\Suppliers\Services\SupplierImageService;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/*
| Supplier images for products, platforms (brand logos) and categories (owner request 2026-11-12):
| one candidate per subject, supplier and kind; the first is activated only while the subject has
| no image; staff choose with "Use this"; a staff image is never replaced.
*/

const BRAND_LOGO_A = 'https://cdn.reloadly.com/brands/steam-a.png';
const BRAND_LOGO_B = 'https://cdn.reloadly.com/brands/steam-b.png';

beforeEach(function (): void {
    Storage::fake('public');
    Http::preventStrayRequests();
    Http::fake([
        'cdn.reloadly.com/brands/steam-b.png' => Http::response(candidatePng(400, 200), 200, ['Content-Type' => 'image/png']),
        'cdn.reloadly.com/*' => Http::response(candidatePng(), 200, ['Content-Type' => 'image/png']),
    ]);
});

function candidatePng(int $width = 600, int $height = 300): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 120, 220));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

function candidates(): SupplierImageCandidates
{
    return app(SupplierImageCandidates::class);
}

function brandLogo(GamingPlatform $platform, Supplier $supplier, string $url = BRAND_LOGO_A): string
{
    return candidates()->fetch($platform, $supplier->id, SupplierImageKind::BrandLogo, $url);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function brandedCard(Supplier $supplier, string $logo = BRAND_LOGO_A, array $attributes = []): SupplierProduct
{
    return SupplierProduct::factory()->create([
        'supplier_id' => $supplier->id,
        'name' => 'Steam Wallet US',
        'brand_name' => 'Steam',
        'category_name' => 'Gaming',
        'country_code' => 'US',
        'denomination_type' => DenominationType::Fixed,
        'currency' => 'USD',
        'sender_currency' => 'USD',
        'fixed_denominations' => ['10'],
        'cost_map' => ['10' => '9.5'],
        'availability' => SupplierAvailability::Available,
        'last_seen_at' => now(),
        'raw' => ['brand' => ['brandName' => 'Steam', 'logoUrl' => $logo], 'category' => ['id' => 1, 'name' => 'Gaming']],
        ...$attributes,
    ]);
}

describe('candidates', function (): void {
    it('keeps one candidate per supplier for a platform and auto-activates only the first', function (): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Steam', 'logo_media_id' => null]);
        [$a, $b] = Supplier::factory()->count(2)->create();

        expect(brandLogo($platform, $a))->toBe('stored')
            ->and(brandLogo($platform, $b, BRAND_LOGO_B))->toBe('stored');

        $rows = SupplierImageCandidate::query()->where('subject_type', 'gaming_platform')->where('subject_id', $platform->id)->with('media')->get()->keyBy('supplier_id');
        expect($rows)->toHaveCount(2)
            ->and($rows[$a->id]->kind)->toBe(SupplierImageKind::BrandLogo)
            ->and($rows[$a->id]->source_url)->toBe(BRAND_LOGO_A)
            ->and($rows[$b->id]->media->source)->toBe(MediaSource::Supplier)
            ->and($rows[$b->id]->media->width)->toBe(400);

        $platform->refresh();
        expect($platform->logo_media_id)->toBe($rows[$a->id]->media_id)
            ->and($platform->image_source)->toBe('supplier:'.$a->id);
    });

    it('does not auto-activate when the platform already has an image', function (): void {
        $staffLogo = Media::factory()->create();
        $platform = GamingPlatform::factory()->create(['logo_media_id' => $staffLogo->id]);

        brandLogo($platform, Supplier::factory()->create());

        expect($platform->refresh()->logo_media_id)->toBe($staffLogo->id)
            ->and(SupplierImageCandidate::query()->count())->toBe(1);
    });

    it('deduplicates by URL and by the image bytes', function (): void {
        $platform = GamingPlatform::factory()->create();
        $supplier = Supplier::factory()->create();

        brandLogo($platform, $supplier);
        $media = SupplierImageCandidate::query()->sole()->media_id;

        expect(brandLogo($platform, $supplier))->toBe('unchanged');
        Http::assertSentCount(1);

        expect(brandLogo($platform, $supplier, 'https://cdn.reloadly.com/brands/steam-renamed.png'))->toBe('unchanged')
            ->and(SupplierImageCandidate::query()->sole()->media_id)->toBe($media)
            ->and(Media::query()->where('source', MediaSource::Supplier)->count())->toBe(1);
    });

    it('queues one brand logo per brand and supplier in the backfill, for existing platforms only', function (): void {
        Queue::fake();
        GamingPlatform::factory()->create(['name' => 'Steam', 'slug' => 'steam']);
        [$a, $b] = Supplier::factory()->count(2)->create();
        brandedCard($a);
        brandedCard($a, attributes: ['name' => 'Steam Wallet UK', 'country_code' => 'GB']);
        brandedCard($b, BRAND_LOGO_B);
        brandedCard($a, 'https://cdn.reloadly.com/brands/unknown.png', ['brand_name' => 'Nobody Brand']);

        expect(app(SupplierBrandLogos::class)->backfill())->toBe(2);
        Queue::assertPushedTimes(FetchSupplierImageCandidate::class, 2);
        Queue::assertPushedOn('sync', FetchSupplierImageCandidate::class);
    });

    it('refuses a brand logo off the image host allow-list', function (): void {
        $platform = GamingPlatform::factory()->create();

        expect(brandLogo($platform, Supplier::factory()->create(), 'https://evil.example/logo.png'))->toBe('host')
            ->and($platform->refresh()->logo_media_id)->toBeNull()
            ->and(SupplierImageCandidate::query()->sole()->error)->toBe('host');
    });
});

describe('staff choice', function (): void {
    it('switches the active image with "Use this", marks it as staff, and audits it', function (): void {
        $platform = GamingPlatform::factory()->create();
        [$a, $b] = Supplier::factory()->count(2)->create();
        brandLogo($platform, $a);
        brandLogo($platform, $b, BRAND_LOGO_B);
        $second = SupplierImageCandidate::query()->where('supplier_id', $b->id)->sole();

        adminWith(['catalog.view', 'catalog.manage']);
        $this->postJson("/admin/api/supplier-image-candidates/{$second->id}/activate")->assertOk();

        $platform->refresh();
        expect($platform->logo_media_id)->toBe($second->media_id)
            ->and($platform->image_source)->toBe(SupplierImageCandidates::STAFF)
            ->and(AuditLog::query()->where('action', 'catalog.image.activated_from_supplier')->sole()->after['supplier_id'] ?? null)->toBe($b->id);

        // A later supplier image is never swapped in over the staff choice.
        $platform->forceFill(['logo_media_id' => $second->media_id])->save();
        brandLogo($platform, $a, 'https://cdn.reloadly.com/brands/steam-new.png');
        expect($platform->refresh()->logo_media_id)->toBe($second->media_id);
    });

    it('never replaces a staff upload, on the form or on the product', function (): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Steam', 'slug' => 'steam-x']);
        adminWith(['catalog.view', 'catalog.manage']);
        $upload = Media::factory()->create(['source' => MediaSource::Upload]);

        $this->putJson("/admin/api/platforms/{$platform->id}", ['name' => 'Steam', 'slug' => 'steam-x', 'logo_media_id' => $upload->id, 'is_active' => true])->assertOk();
        expect($platform->refresh()->image_source)->toBe('staff');

        $platform->forceFill(['logo_media_id' => $upload->id])->save();
        brandLogo($platform, Supplier::factory()->create());
        expect($platform->refresh()->logo_media_id)->toBe($upload->id);

        $variant = ProductVariant::factory()->create();
        $product = $variant->product;
        $this->postJson("/admin/api/products/{$product->public_id}/images", ['media_id' => $upload->id])->assertCreated();
        expect($product->refresh()->image_source)->toBe('staff');

        $supplierMedia = Media::factory()->create(['source' => MediaSource::Supplier]);
        candidates()->record($product, Supplier::factory()->create()->id, SupplierImageKind::ProductCard, $supplierMedia->id);
        expect(candidates()->activeMediaId($product))->toBe($upload->id);
    });

    it('needs catalog.manage to use a supplier image', function (): void {
        $candidate = SupplierImageCandidate::factory()->create();

        adminWith(['catalog.view']);
        $this->postJson("/admin/api/supplier-image-candidates/{$candidate->id}/activate")->assertForbidden();
        expect($candidate->subject->refresh()->logo_media_id)->toBeNull();
    });

    it('shows the gallery with the Active badge and Use this on the platform edit page', function (): void {
        $platform = GamingPlatform::factory()->create();
        $supplier = Supplier::factory()->create(['name' => 'Reloadly Gift Cards']);
        brandLogo($platform, $supplier);
        $other = SupplierImageCandidate::factory()->create(['subject_id' => $platform->id]);

        adminWith(['catalog.view', 'catalog.manage']);
        $this->get(route('admin.catalog.platforms.edit', ['record' => $platform->getRouteKey()]))->assertOk()
            ->assertSee(__('admin.supplier_images.title'))
            ->assertSee('Reloadly Gift Cards')
            ->assertSee(__('admin.supplier_images.active'))
            ->assertSee("/admin/api/supplier-image-candidates/{$other->id}/activate", false);

        adminWith(['catalog.view']);
        $this->get(route('admin.catalog.platforms.edit', ['record' => $platform->getRouteKey()]))->assertOk()
            ->assertDontSee("/admin/api/supplier-image-candidates/{$other->id}/activate", false);
    });
});

describe('products', function (): void {
    it('stores the supplier product image as a candidate of the mapped store product', function (): void {
        $supplier = Supplier::factory()->create();
        $source = brandedCard($supplier, attributes: ['raw' => ['logoUrls' => ['https://cdn.reloadly.com/giftcards/card.png']]]);
        $variant = ProductVariant::factory()->create();
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => $source->id]);

        app(SupplierImageService::class)->refresh($source);

        $candidate = SupplierImageCandidate::query()->sole();
        expect($candidate->subject_type)->toBe('product')
            ->and($candidate->subject_id)->toBe($variant->product_id)
            ->and($candidate->kind)->toBe(SupplierImageKind::ProductCard)
            ->and($candidate->media_id)->toBe($source->refresh()->image_media_id)
            ->and(ProductImage::query()->where('product_id', $variant->product_id)->value('media_id'))->toBe($source->image_media_id)
            ->and(Product::query()->find($variant->product_id)->image_source)->toBe('supplier:'.$supplier->id);
    });

    it('fetches the brand logo when Add to store creates the platform', function (): void {
        Queue::fakeExcept([FetchSupplierImageCandidate::class]);
        CurrencyFactory::ensure('USD');
        $supplier = Supplier::factory()->create();
        $first = brandedCard($supplier);
        $second = brandedCard($supplier, attributes: ['name' => 'Steam Wallet UK']);

        app(SupplierCatalogImporter::class)->addToStore([$first->id, $second->id], Category::factory()->create()->id, null, 'test');

        $platform = GamingPlatform::query()->where('name', 'Steam')->sole();
        expect($platform->logo_media_id)->not->toBeNull()
            ->and($platform->image_source)->toBe('supplier:'.$supplier->id)
            ->and(SupplierImageCandidate::query()->where('subject_type', 'gaming_platform')->sole()->source_url)->toBe(BRAND_LOGO_A);
        Http::assertSent(fn ($request): bool => $request->url() === BRAND_LOGO_A);
        expect(collect(Http::recorded())->filter(fn ($pair): bool => $pair[0]->url() === BRAND_LOGO_A))->toHaveCount(1);
    });
});

describe('categories', function (): void {
    it('has no category images from Reloadly, so nothing is queued or invented', function (): void {
        Queue::fake();
        $supplier = Supplier::factory()->create(['adapter_key' => ReloadlyGiftCardAdapter::KEY]);
        Category::factory()->create(['name' => 'Gaming']);
        brandedCard($supplier);

        expect(app(ReloadlyGiftCardAdapter::class, ['supplier' => $supplier])->categoryImageUrl('Gaming', ['category' => ['id' => 1, 'name' => 'Gaming']]))->toBeNull()
            ->and(app(SupplierCategoryImages::class)->queue($supplier))->toBe(0);
        Queue::assertNothingPushed();
        expect(SupplierImageCandidate::query()->count())->toBe(0);
    });

    it('activates a category banner candidate through the same plumbing', function (): void {
        $category = Category::factory()->create(['banner_media_id' => null]);

        candidates()->fetch($category, Supplier::factory()->create()->id, SupplierImageKind::CategoryBanner, BRAND_LOGO_A);

        expect($category->refresh()->banner_media_id)->toBe(SupplierImageCandidate::query()->sole()->media_id)
            ->and(candidates()->forSubject($category))->toHaveCount(1);
    });
});
