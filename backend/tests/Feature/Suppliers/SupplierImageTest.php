<?php

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Jobs\FetchSupplierProductImage;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Services\SupplierCatalogImporter;
use App\Domains\Suppliers\Services\SupplierCatalogSync;
use App\Domains\Suppliers\Services\SupplierImageService;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/*
| Owner decision 12: supplier product images are downloaded (SSRF rules), optimized to AVIF and
| WebP, attached to store products without ever replacing a staff image, and shown on the cards.
*/

const SUPPLIER_IMAGE_URL = 'https://cdn.reloadly.com/giftcards/card.png';

beforeEach(function (): void {
    Storage::fake('public');
    Http::preventStrayRequests();
});

function pngBytes(int $width = 1200, int $height = 600): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 120));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

function jpegBytes(int $width = 300, int $height = 200): string
{
    $image = imagecreatetruecolor($width, $height);
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

/**
 * @param  array<string, mixed>  $raw
 */
function imageCard(array $raw = ['logoUrls' => [SUPPLIER_IMAGE_URL], 'brand' => ['brandName' => 'Adidas', 'logoUrl' => 'https://cdn.reloadly.com/giftcards/brand.png']]): SupplierProduct
{
    return SupplierProduct::factory()->create([
        'name' => 'Adidas US',
        'brand_name' => 'Adidas',
        'category_name' => 'Shopping',
        'country_code' => 'US',
        'denomination_type' => DenominationType::Fixed,
        'currency' => 'USD',
        'sender_currency' => 'USD',
        'fixed_denominations' => ['10'],
        'cost_map' => ['10' => '9.5'],
        'availability' => SupplierAvailability::Available,
        'last_seen_at' => now(),
        'raw' => $raw,
    ]);
}

function refreshImage(SupplierProduct $source): string
{
    return app(SupplierImageService::class)->refresh($source->refresh());
}

describe('download rules', function (): void {
    it('refuses a host off the allow-list, http, an oversize file, a non-image and SVG', function (string $url, array $answer, string $reason): void {
        Http::fake(['cdn.reloadly.com/*' => Http::response($answer[0] ?? '', $answer[1] ?? 200, $answer[2] ?? [])]);
        $source = imageCard(['logoUrls' => [$url]]);

        expect(refreshImage($source))->toBe($reason)
            ->and($source->refresh()->image_media_id)->toBeNull()
            ->and($source->image_error)->toBe($reason);
        expect(Storage::disk('public')->allFiles())->toBe([]);
    })->with([
        'other host' => ['https://evil.example/card.png', [], 'host'],
        'lookalike host' => ['https://cdn.reloadly.com.evil.example/card.png', [], 'host'],
        'http' => ['http://cdn.reloadly.com/giftcards/card.png', [], 'not_https'],
        'port' => ['https://cdn.reloadly.com:8443/card.png', [], 'host'],
        'oversize' => [SUPPLIER_IMAGE_URL, [str_repeat('a', 5 * 1024 * 1024 + 10), 200, ['Content-Type' => 'image/png']], 'too_large'],
        'declared oversize' => [SUPPLIER_IMAGE_URL, ['x', 200, ['Content-Type' => 'image/png', 'Content-Length' => (string) (6 * 1024 * 1024)]], 'too_large'],
        'html' => [SUPPLIER_IMAGE_URL, ['<html></html>', 200, ['Content-Type' => 'text/html']], 'not_image'],
        'image type, not an image' => [SUPPLIER_IMAGE_URL, ['<?php echo 1;', 200, ['Content-Type' => 'image/png']], 'not_image'],
        'svg type' => [SUPPLIER_IMAGE_URL, ['<svg xmlns="http://www.w3.org/2000/svg"/>', 200, ['Content-Type' => 'image/svg+xml']], 'svg'],
        'svg named png' => [SUPPLIER_IMAGE_URL, ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 200, ['Content-Type' => 'image/png']], 'svg'],
        'redirect' => [SUPPLIER_IMAGE_URL, ['', 302, ['Location' => 'https://169.254.169.254/']], 'redirect'],
    ]);
});

describe('optimizing', function (): void {
    it('stores AVIF and WebP at 960 and 480 px under media/supplier-products/{id}, without upscaling', function (): void {
        Http::fake([
            'cdn.reloadly.com/giftcards/small.jpg' => Http::response(jpegBytes(), 200, ['Content-Type' => 'image/jpeg']),
            'cdn.reloadly.com/*' => Http::response(pngBytes(), 200, ['Content-Type' => 'image/png']),
        ]);
        $source = imageCard();

        expect(refreshImage($source))->toBe('stored');

        $media = $source->refresh()->imageMedia;
        expect($media->source)->toBe(MediaSource::Supplier)
            ->and($media->width)->toBe(960)->and($media->height)->toBe(480)
            ->and(array_keys($media->conversions))->toEqualCanonicalizing(['webp', 'avif', 'webp_480', 'avif_480'])
            ->and($source->image_hash)->toBe(hash('sha256', pngBytes()))
            ->and($source->image_url)->toBe(SUPPLIER_IMAGE_URL);

        foreach ($media->conversions as $path) {
            expect($path)->toStartWith("media/supplier-products/{$source->id}/");
            Storage::disk('public')->assertExists($path);
        }

        $avif = Storage::disk('public')->get($media->conversions['avif']);
        expect(substr($avif, 4, 8))->toBe('ftypavif')
            ->and(substr(Storage::disk('public')->get($media->conversions['webp']), 8, 4))->toBe('WEBP')
            ->and(getimagesizefromstring(Storage::disk('public')->get($media->conversions['webp_480']))[0])->toBe(480);

        $small = imageCard(['logoUrls' => ['https://cdn.reloadly.com/giftcards/small.jpg']]);
        refreshImage($small);
        expect($small->refresh()->imageMedia->width)->toBe(300)->and(array_keys($small->imageMedia->conversions))->toEqualCanonicalizing(['webp', 'avif']);
    });

    it('prefers the product card image over the brand logo', function (): void {
        expect(imageCard()->sourceImageUrl())->toBe(SUPPLIER_IMAGE_URL)
            ->and(imageCard(['brand' => ['logoUrl' => 'https://cdn.reloadly.com/b.png']])->sourceImageUrl())->toBe('https://cdn.reloadly.com/b.png')
            ->and(imageCard([])->sourceImageUrl())->toBeNull();
    });

    it('does not download an unchanged URL again, and replaces the image when the URL changes', function (): void {
        Http::fake([
            'cdn.reloadly.com/giftcards/new.png' => Http::response(pngBytes(800, 400), 200, ['Content-Type' => 'image/png']),
            'cdn.reloadly.com/*' => Http::response(pngBytes(), 200, ['Content-Type' => 'image/png']),
        ]);
        $source = imageCard();

        refreshImage($source);
        $first = $source->refresh()->image_media_id;

        expect(refreshImage($source))->toBe('unchanged');
        Http::assertSentCount(1);

        $source->forceFill(['raw' => ['logoUrls' => ['https://cdn.reloadly.com/giftcards/new.png']]])->save();

        expect(refreshImage($source))->toBe('stored')
            ->and($source->refresh()->image_media_id)->not->toBe($first)
            ->and(Media::query()->find($first))->toBeNull();
    });

    it('makes the catalog sync queue one image job per new or changed URL, on the sync queue', function (): void {
        Queue::fake();
        CurrencyFactory::ensure('USD');
        $supplier = Supplier::factory()->create();
        $sync = app(SupplierCatalogSync::class);
        $data = fn (string $url): SupplierProductData => new SupplierProductData('X1', 'Adidas US', 'Adidas', 'US', 'Shopping', DenominationType::Fixed, 'USD', ['10'], null, null, 'USD', ['10' => '9.5'], null, null, null, null, false, false, SupplierAvailability::Available, ['logoUrls' => [$url]]);
        $flush = fn () => (fn () => $this->queueImages())->call($sync);

        $sync->upsert($supplier, $data(SUPPLIER_IMAGE_URL));
        $sync->upsert($supplier, $data(SUPPLIER_IMAGE_URL));
        $flush();
        Queue::assertPushedOn('sync', FetchSupplierProductImage::class);
        Queue::assertPushedTimes(FetchSupplierProductImage::class, 1);

        SupplierProduct::query()->update(['image_url' => SUPPLIER_IMAGE_URL, 'image_media_id' => Media::factory()->create()->id]);
        $sync->upsert($supplier, $data(SUPPLIER_IMAGE_URL));
        $flush();
        Queue::assertPushedTimes(FetchSupplierProductImage::class, 1);

        $sync->upsert($supplier, $data('https://cdn.reloadly.com/giftcards/new.png'));
        $flush();
        Queue::assertPushedTimes(FetchSupplierProductImage::class, 2);
    });
});

describe('store products', function (): void {
    beforeEach(function (): void {
        CurrencyFactory::ensure('USD');
        Http::fake(['cdn.reloadly.com/*' => Http::response(pngBytes(), 200, ['Content-Type' => 'image/png'])]);
    });

    it('attaches the supplier image as the primary image on Add to store', function (): void {
        $source = imageCard();
        refreshImage($source);
        Queue::fake();

        $result = app(SupplierCatalogImporter::class)->addToStore([$source->id], Category::factory()->create()->id, null, 'test');
        $product = Product::query()->where('public_id', $result['products'][0])->firstOrFail();

        expect($product->productImages()->count())->toBe(1)
            ->and($product->productImages()->first()->media_id)->toBe($source->refresh()->image_media_id)
            ->and($product->productImages()->first()->alt)->toBe($product->name);
    });

    it('gives an already added product the image when it arrives, but never replaces a staff image', function (): void {
        $source = imageCard();
        $plain = ProductVariant::factory()->create();
        SupplierProductMapping::factory()->create(['product_variant_id' => $plain->id, 'supplier_id' => $source->supplier_id, 'supplier_product_id' => $source->id]);

        $staffed = ProductVariant::factory()->create();
        SupplierProductMapping::factory()->create(['product_variant_id' => $staffed->id, 'supplier_id' => $source->supplier_id, 'supplier_product_id' => $source->id, 'role' => 'secondary']);
        $staffImage = ProductImage::factory()->create(['product_id' => $staffed->product_id, 'sort_order' => 0]);

        (new FetchSupplierProductImage($source->id))->handle(app(SupplierImageService::class));

        expect(ProductImage::query()->where('product_id', $plain->product_id)->value('media_id'))->toBe($source->refresh()->image_media_id)
            ->and(ProductImage::query()->where('product_id', $staffed->product_id)->pluck('media_id')->all())->toBe([$staffImage->media_id]);
    });
});

describe('storefront card', function (): void {
    /**
     * @return array<string, mixed>
     */
    function cardPayload(?array $image): array
    {
        return [
            'slug' => 'adidas-us', 'name' => 'Adidas US', 'delivery_type' => 'instant_code', 'rating_avg' => null,
            'price_from' => null, 'price_may_vary' => false, 'image' => $image,
            'platform' => ['slug' => 'adidas', 'name' => 'Adidas'], 'category' => ['name' => 'Shopping'],
        ];
    }

    it('draws the product image with <picture> (AVIF first, WebP fallback) when there is one', function (): void {
        Http::fake(['cdn.reloadly.com/*' => Http::response(pngBytes(), 200, ['Content-Type' => 'image/png'])]);
        $source = imageCard();
        refreshImage($source);
        $image = app(MediaUrls::class)->image($source->refresh()->imageMedia);

        $html = (string) $this->blade('<x-store.product-card :product="$product" />', ['product' => cardPayload($image)]);

        expect($html)->toContain('<picture')
            ->toContain('type="image/avif" srcset="'.$image['formats']['avif_480'].' 480w, '.$image['formats']['avif'].' 960w"')
            ->toContain('type="image/webp"')
            ->toContain('alt="Adidas US"')->toContain('width="960" height="480"')->toContain('loading="lazy"')
            ->toContain($image['formats']['webp_480'].' 480w')
            ->and(strpos($html, 'image/avif'))->toBeLessThan(strpos($html, 'image/webp'));
    });

    it('keeps the generated art when there is no image', function (): void {
        $html = (string) $this->blade('<x-store.product-card :product="$product" />', ['product' => cardPayload(null)]);

        expect($html)->not->toContain('<picture')->toContain('Adidas')->toContain('Shopping');
    });
});
