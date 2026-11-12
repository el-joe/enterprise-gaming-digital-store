<?php

use App\DataTables\AdminTables;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Suppliers\Models\SupplierProduct;

/*
| Owner request: the active image appears in the admin tables as a 40 px thumbnail (one shared
| renderer, Cell::thumbnail): the 480 px AVIF and WebP from the media formats, lazy, the record's
| name as alt, a neutral placeholder without an image; not sortable.
*/

function thumbMedia(): Media
{
    $base = 'media/2026/11/'.fake()->unique()->lexify('thumb????');

    return Media::factory()->create([
        'path' => $base.'.webp',
        'conversions' => ['webp' => $base.'.webp', 'avif' => $base.'.avif', 'webp_480' => $base.'-480.webp', 'avif_480' => $base.'-480.avif'],
    ]);
}

/**
 * @return array<int, array<string, mixed>>
 */
function imageRows(string $table, string $nameKey): array
{
    $response = postTable($table)->assertOk();
    $column = collect(AdminTables::make($table, auth()->user())->definition()['columns'])->firstWhere('key', 'image');

    expect($column)->not->toBeNull()->and($column['orderable'])->toBeFalse()->and($column['visible'])->toBeTrue();

    return collect($response->json('data'))->keyBy(static fn (array $row): string => strip_tags((string) $row[$nameKey]))->all();
}

it('shows the image thumbnail with the media format URLs, or the placeholder, in each table', function (string $table, string $nameKey, Closure $make): void {
    adminWith(['catalog.view', 'marketing.banners']);
    $media = thumbMedia();
    $make('With image', $media);
    $make('Without image', null);
    $formats = app(MediaUrls::class)->image($media)['formats'];

    $rows = imageRows($table, $nameKey);

    expect($rows['With image']['image'])
        ->toContain('<picture')
        ->toContain('type="image/avif" srcset="'.e($formats['avif_480']).'"')
        ->toContain('type="image/webp" srcset="'.e($formats['webp_480']).'"')
        ->toContain('src="'.e($formats['webp_480']).'"')
        ->toContain('alt="With image"')
        ->toContain('loading="lazy"')
        ->not->toContain('style=')
        ->and($rows['Without image']['image'])->toContain('data-thumb="empty"')->not->toContain('<img');
})->with([
    'products' => ['products', 'name', function (string $name, ?Media $media): void {
        $product = Product::factory()->create(['name' => $name]);

        if ($media !== null) {
            ProductImage::factory()->create(['product_id' => $product->id, 'media_id' => $media->id, 'sort_order' => 0]);
        }
    }],
    'platforms' => ['platforms', 'name', fn (string $name, ?Media $media) => GamingPlatform::factory()->create(['name' => $name, 'logo_media_id' => $media?->id])],
    'categories' => ['categories', 'name', fn (string $name, ?Media $media) => Category::factory()->create(['name' => $name, 'banner_media_id' => $media?->id])],
    'banners' => ['banners', 'title', function (string $name, ?Media $media): void {
        $banner = Banner::factory()->create(['title' => $name, 'media_id' => $media?->id ?? Media::factory()->create()->id]);

        if ($media === null) {
            $banner->media->delete();
        }
    }],
    'supplier catalog' => ['supplier-catalog', 'name', fn (string $name, ?Media $media) => SupplierProduct::factory()->create(['name' => $name, 'image_media_id' => $media?->id])],
]);

it('escapes the name used as alt', function (): void {
    adminWith(['catalog.view']);
    GamingPlatform::factory()->create(['name' => '"><script>x</script>', 'logo_media_id' => thumbMedia()->id]);

    $html = postTable('platforms')->json('data.0.image');

    expect($html)->not->toContain('<script>')->toContain('alt="&quot;&gt;&lt;script&gt;x&lt;/script&gt;"');
});
