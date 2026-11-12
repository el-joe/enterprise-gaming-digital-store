<?php

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
| Owner request: admin image fields are file inputs (<x-admin.image-upload>), not media id numbers.
| Uploads go through ImageOptimizer (AVIF and WebP, resized, GD re-encode, no SVG, 5 MB).
*/

beforeEach(function (): void {
    Storage::fake('public');
});

it('optimizes an upload to AVIF and WebP at the upload widths', function (): void {
    adminWith(['catalog.manage']);

    $response = $this->postJson('/admin/api/media/images', ['file' => UploadedFile::fake()->image('hero.jpg', 2400, 1200)])->assertCreated();
    $conversions = $response->json('data.conversions');

    expect(array_keys($conversions))->toEqualCanonicalizing(['webp', 'avif', 'webp_960', 'avif_960', 'webp_480', 'avif_480'])
        ->and($response->json('data.width'))->toBe(1920)
        ->and($response->json('data.url'))->toEndWith($response->json('data.path'));

    expect(substr(Storage::disk('public')->get($conversions['avif']), 4, 8))->toBe('ftypavif')
        ->and(getimagesizefromstring(Storage::disk('public')->get($conversions['webp_480']))[0])->toBe(480);
});

it('rejects SVG, non-images and files over 5 MB', function (UploadedFile $file): void {
    adminWith(['catalog.manage']);

    $this->postJson('/admin/api/media/images', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors(['file'], 'errors');
    expect(Media::query()->count())->toBe(0);
})->with([
    'svg' => fn () => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
    'svg named png' => fn () => UploadedFile::fake()->createWithContent('x.png', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
    'not an image' => fn () => UploadedFile::fake()->createWithContent('x.png', '<?php echo 1;'),
    'over 5 MB' => fn () => UploadedFile::fake()->create('x.png', 5200, 'image/png'),
]);

it('lets only roles that edit image fields upload', function (): void {
    $file = fn () => UploadedFile::fake()->image('a.png', 40, 30);

    adminWith(['orders.view']);
    $this->postJson('/admin/api/media/images', ['file' => $file()])->assertForbidden();

    foreach (['catalog.manage', 'marketing.banners', 'content.blog', 'content.media'] as $permission) {
        adminWith([$permission]);
        $this->postJson('/admin/api/media/images', ['file' => $file()])->assertCreated();
    }
});

it('renders the upload component instead of a media id number on every image form', function (string $url, array $permissions, array $names): void {
    adminWith($permissions);

    $page = $this->get($url)->assertOk()->assertSee('data-module="image-upload"', false)
        ->assertSee('accept="image/png,image/jpeg,image/webp,image/avif"', false);

    foreach ($names as $name) {
        $page->assertSee('type="hidden" name="'.$name.'"', false)->assertDontSee('name="'.$name.'" type="number"', false);
        expect(preg_match('/<input[^>]*type="number"[^>]*name="'.preg_quote($name, '/').'"|<input[^>]*name="'.preg_quote($name, '/').'"[^>]*type="number"/', $page->getContent()))->toBe(0);
    }

    assertCspSafeHtml($page);
})->with([
    'banner' => ['/admin/marketing/banners/create', ['marketing.banners'], ['media_id', 'mobile_media_id']],
    'platform' => ['/admin/catalog/platforms/create', ['catalog.view', 'catalog.manage'], ['logo_media_id', 'banner_media_id']],
    'category' => ['/admin/catalog/categories/create', ['catalog.view', 'catalog.manage'], ['banner_media_id']],
    'blog post' => ['/admin/content/blog-posts/create', ['content.blog'], ['cover_media_id']],
]);

it('shows the upload, primary and remove controls in the product editor', function (): void {
    adminWith(['catalog.view', 'catalog.manage']);
    $product = Product::factory()->create();
    $first = ProductImage::factory()->create(['product_id' => $product->id, 'sort_order' => 0]);
    $second = ProductImage::factory()->create(['product_id' => $product->id, 'sort_order' => 1]);

    $this->get("/admin/catalog/products/{$product->public_id}")->assertOk()
        ->assertSee('data-module="image-upload"', false)
        ->assertSee("/images/{$second->id}/primary", false)
        ->assertDontSee("/images/{$first->id}/primary", false)
        ->assertDontSee('id="pi-media" type="number"', false);

    $this->postJson("/admin/api/products/{$product->public_id}/images/{$second->id}/primary")->assertOk();
    expect($product->productImages()->orderBy('sort_order')->pluck('id')->all())->toBe([$second->id, $first->id]);

    $this->deleteJson("/admin/api/products/{$product->public_id}/images/{$second->id}")->assertOk();
    expect($product->productImages()->pluck('id')->all())->toBe([$first->id]);
});

it('removes an image from a form by sending an empty id', function (): void {
    adminWith(['catalog.view', 'catalog.manage']);
    $category = Category::factory()->create(['banner_media_id' => Media::factory()->create()->id]);

    $this->patchJson("/admin/api/categories/{$category->id}", ['banner_media_id' => null])->assertOk();

    expect($category->refresh()->banner_media_id)->toBeNull();
});

it('lets a staff image win over the supplier image', function (): void {
    adminWith(['catalog.view', 'catalog.manage']);
    $product = Product::factory()->create();
    $supplier = ProductImage::factory()->create(['product_id' => $product->id, 'sort_order' => 0, 'media_id' => Media::factory()->create(['source' => MediaSource::Supplier])->id]);

    $media = $this->postJson('/admin/api/media/images', ['file' => UploadedFile::fake()->image('a.png', 40, 30)])->assertCreated()->json('data.id');
    $this->postJson("/admin/api/products/{$product->public_id}/images", ['media_id' => $media])->assertCreated();

    expect($product->productImages()->pluck('media_id')->all())->toBe([$media])
        ->and(ProductImage::query()->find($supplier->id))->toBeNull();
});

it('denies image changes without catalog.manage', function (): void {
    adminWith(['catalog.view']);
    $product = Product::factory()->create();
    $image = ProductImage::factory()->create(['product_id' => $product->id]);

    $this->postJson("/admin/api/products/{$product->public_id}/images/{$image->id}/primary")->assertForbidden();
    $this->deleteJson("/admin/api/products/{$product->public_id}/images/{$image->id}")->assertForbidden();
    $this->get("/admin/catalog/products/{$product->public_id}")->assertOk()->assertDontSee('data-module="image-upload"', false);
});
