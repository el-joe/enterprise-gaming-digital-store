<?php

/**
 * The right image of every product (Storefront v2, owner request): one shared component
 * (`x-store.product-image`) and one priority (ProductImages) for cards, bag lines, checkout,
 * orders, codes and the wishlist: the primary product image (a staff image before a supplier one),
 * then the brand logo, then the generated art. Line JSON carries `image` and `image_source`. A
 * supplier image downloaded after the product was added reaches every mapped product.
 */

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Catalog\Services\ProductImages;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Jobs\FulfilOrder;
use App\Domains\Suppliers\Jobs\FetchSupplierProductImage;
use App\Domains\Suppliers\Services\SupplierImageService;
use App\Domains\Suppliers\Services\SupplierMappingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

function imageMedia(string $path, MediaSource $source = MediaSource::Upload): Media
{
    return Media::factory()->create(['path' => $path, 'width' => 800, 'height' => 400, 'conversions' => ['webp' => $path, 'avif' => str_replace('.webp', '.avif', $path)], 'source' => $source]);
}

function withProductImage(Product $product, string $path, int $sortOrder = 0, MediaSource $source = MediaSource::Upload): ProductImage
{
    return ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => imageMedia($path, $source)->id, 'sort_order' => $sortOrder, 'alt' => $product->name]);
}

function imagesPng(): string
{
    $image = imagecreatetruecolor(600, 300);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 255, 46));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

describe('priority', function (): void {
    it('uses the primary product image, then the brand logo, then nothing (the generated art)', function (): void {
        $images = app(ProductImages::class);
        $withImage = storefrontProduct('With image', '10');
        withProductImage($withImage, 'media/supplier.webp', 1, MediaSource::Supplier);
        withProductImage($withImage, 'media/staff.webp', 0);

        $logoBrand = GamingPlatform::factory()->create(['slug' => 'logo-brand', 'logo_media_id' => imageMedia('media/logo.webp')->id]);
        $logoOnly = storefrontProduct('Logo only', '10', ['gaming_platform_id' => $logoBrand->id]);
        $bare = storefrontProduct('Bare', '10', ['gaming_platform_id' => GamingPlatform::factory()->create(['slug' => 'bare'])->id]);

        $products = Product::query()->whereIn('id', [$withImage->id, $logoOnly->id, $bare->id])->get()->keyBy('name');
        $images->load($products);

        expect($images->resolve($products['With image']))->toMatchArray(['source' => 'product'])
            ->and($images->resolve($products['With image'])['image']['path'])->toBe('media/staff.webp')
            ->and($images->resolve($products['Logo only']))->toMatchArray(['source' => 'brand'])
            ->and($images->resolve($products['Logo only'])['image']['path'])->toBe('media/logo.webp')
            ->and($images->resolve($products['Bare']))->toBeNull();
    });

    it('draws the card with the product image, else the brand logo over the art, else the art alone', function (): void {
        $withImage = storefrontProduct('Card with image', '10');
        withProductImage($withImage, 'media/card.webp');
        $logoBrand = GamingPlatform::factory()->create(['slug' => 'logo-brand', 'name' => 'Logo brand', 'logo_media_id' => imageMedia('media/brand-logo.webp')->id]);
        storefrontProduct('Card with logo', '10', ['gaming_platform_id' => $logoBrand->id]);

        $html = $this->get('/products')->assertOk()->getContent();

        expect($html)->toContain('media/card.avif"')
            ->toContain('alt="Card with image"')
            ->toContain('data-tile-logo')
            ->toContain('alt="Card with logo"');
    });
});

describe('lines', function (): void {
    it('shows the product image on the bag line and in the cart JSON, and the fallback without one', function (): void {
        $this->actingAs(User::factory()->create());
        $pictured = storefrontProduct('Adidas DE', '25');
        withProductImage($pictured, 'media/adidas.webp');
        $plain = storefrontProduct('Plain card', '10', ['gaming_platform_id' => GamingPlatform::factory()->create(['slug' => 'plain'])->id]);

        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($pictured)->public_id, 'quantity' => 1])->assertCreated();
        $items = $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($plain)->public_id, 'quantity' => 1])->assertCreated()->json('data.cart.items');

        expect($items[0]['product'])->toMatchArray(['image_source' => 'product'])
            ->and($items[0]['product']['image']['path'])->toBe('media/adidas.webp')
            ->and($items[1]['product'])->toMatchArray(['image' => null, 'image_source' => null]);

        $bag = $this->get('/cart')->assertOk()->getContent();
        expect($bag)->toContain('media/adidas.avif"')->toContain('alt="Adidas DE"')
            ->and(substr_count($bag, 'data-line-image'))->toBeGreaterThanOrEqual(2);
    });

    it('shows the image in the checkout summary', function (): void {
        Queue::fake([FulfilOrder::class]);
        $this->actingAs(User::factory()->create());
        $product = storefrontProduct('Adidas DE', '25');
        withProductImage($product, 'media/adidas.webp');
        $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($product)->public_id, 'quantity' => 1])->assertCreated();

        $this->get('/checkout')->assertOk()->assertSee('media/adidas.avif"', false)->assertSee('data-line-image', false);
    });

    it('shows the image on the order, the account order and the codes list, and in the order JSON', function (): void {
        $customer = User::factory()->create();
        [$order] = accountPageDeliveredOrder($customer, product: 'Adidas DE');
        withProductImage(Product::query()->where('name', 'Adidas DE')->sole(), 'media/adidas.webp');
        $this->actingAs($customer);

        $this->get('/account/orders/'.$order->public_id)->assertOk()->assertSee('media/adidas.avif"', false);
        $this->get('/account/codes')->assertOk()->assertSee('media/adidas.avif"', false)->assertSee('alt="Adidas DE"', false);
        $this->getJson('/api/v1/account/orders/'.$order->public_id)->assertOk()
            ->assertJsonPath('data.order.items.0.product.image_source', 'product')
            ->assertJsonPath('data.order.items.0.product.image.path', 'media/adidas.webp');
    });

    it('falls back to the brand logo on an order line without a product image', function (): void {
        $customer = User::factory()->create();
        [$order] = accountPageDeliveredOrder($customer, product: 'Logo card');
        GamingPlatform::query()->where('slug', 'steam')->update(['logo_media_id' => imageMedia('media/steam-logo.webp')->id]);
        $this->actingAs($customer);

        $this->get('/account/orders/'.$order->public_id)->assertOk()->assertSee('data-tile-logo', false)->assertSee('media/steam-logo', false);
    });

    it('shows the product image on the wishlist', function (): void {
        $customer = User::factory()->create();
        $product = storefrontProduct('Adidas DE', '25');
        withProductImage($product, 'media/adidas.webp');
        WishlistItem::factory()->create(['user_id' => $customer->id, 'product_id' => $product->id]);
        $this->actingAs($customer);

        $this->get('/account/wishlist')->assertOk()->assertSee('media/adidas.avif"', false)->assertSee('alt="Adidas DE"', false);
        $this->getJson('/api/v1/account/wishlist')->assertOk()->assertJsonPath('data.items.0.image.path', 'media/adidas.webp');
    });
});

describe('supplier images', function (): void {
    it('attaches a supplier image that arrives after the product was added to every mapped product', function (): void {
        Storage::fake('public');
        Http::preventStrayRequests();
        Http::fake(['cdn.reloadly.com/*' => Http::response(imagesPng(), 200, ['Content-Type' => 'image/png'])]);

        $supplier = reloadlySupplier();
        $first = storefrontProduct('Adidas DE', '25');
        $second = storefrontProduct('Adidas DE 50', '50');
        $mapping = mapToSupplier(storefrontVariant($first), $supplier, externalId: '777');
        mapToSupplier(storefrontVariant($second), $supplier, externalId: '778')->forceFill(['supplier_product_id' => $mapping->supplier_product_id])->save();

        $source = $mapping->supplierProduct;
        $source->forceFill(['raw' => ['logoUrls' => ['https://cdn.reloadly.com/adidas.png']]])->save();

        expect(ProductImage::query()->whereIn('product_id', [$first->id, $second->id])->count())->toBe(0);

        // The download job runs after the products exist (late arrival).
        (new FetchSupplierProductImage($source->id))->handle(app(SupplierImageService::class));

        expect(ProductImage::query()->where('product_id', $first->id)->count())->toBe(1)
            ->and(ProductImage::query()->where('product_id', $second->id)->count())->toBe(1)
            ->and($first->refresh()->image_source)->toBe('supplier:'.$supplier->id);

        $this->get('/products')->assertOk()->assertSee('alt="Adidas DE"', false)->assertDontSee('data-tile-logo', false);
    });

    it('fills products without an image from already-downloaded supplier images with suppliers:sync-images', function (): void {
        Queue::fake();
        $supplier = reloadlySupplier();
        $product = storefrontProduct('Late card', '25');
        $mapping = mapToSupplier(storefrontVariant($product), $supplier, externalId: '901');
        $mapping->supplierProduct->forceFill(['image_media_id' => imageMedia('media/supplier-late.webp', MediaSource::Supplier)->id, 'image_url' => 'https://cdn.reloadly.com/late.png'])->save();

        $this->artisan('suppliers:sync-images')->assertSuccessful();

        expect(ProductImage::query()->where('product_id', $product->id)->value('media_id'))->toBe($mapping->supplierProduct->image_media_id);
        Queue::assertNotPushed(FetchSupplierProductImage::class, static fn (FetchSupplierProductImage $job): bool => $job->supplierProductId === $mapping->supplier_product_id);
    });

    it('gives a hand-mapped product the supplier image at once, never over a staff image', function (): void {
        $supplier = reloadlySupplier();
        $product = storefrontProduct('Mapped later', '25');
        $staff = storefrontProduct('Staff pictured', '25');
        withProductImage($staff, 'media/staff-own.webp');
        $staff->forceFill(['image_source' => 'staff'])->save();

        $source = mapToSupplier(storefrontVariant(storefrontProduct('Seed', '1')), $supplier, externalId: '555')->supplierProduct;
        $source->forceFill(['image_media_id' => imageMedia('media/supplier-555.webp', MediaSource::Supplier)->id])->save();

        app(SupplierMappingService::class)->create(['product_variant_id' => storefrontVariant($product)->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => $source->id, 'role' => 'secondary', 'supplier_unit_price' => '25', 'supplier_unit_currency' => 'USD', 'is_active' => true]);
        app(SupplierMappingService::class)->create(['product_variant_id' => storefrontVariant($staff)->id, 'supplier_id' => $supplier->id, 'supplier_product_id' => $source->id, 'role' => 'secondary', 'supplier_unit_price' => '25', 'supplier_unit_currency' => 'USD', 'is_active' => true]);

        expect(ProductImage::query()->where('product_id', $product->id)->value('media_id'))->toBe($source->image_media_id)
            ->and(ProductImage::query()->where('product_id', $staff->id)->orderBy('sort_order')->first()->media->path)->toBe('media/staff-own.webp');
    });
});
