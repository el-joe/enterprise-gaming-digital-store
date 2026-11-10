<?php

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Cms\Models\Media;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Str;

/**
 * @return array<string, array{0: string, 1: string, 2: string, 3: Closure(): array<string, mixed>}>
 */
function adminCrudResources(): array
{
    $slug = static fn (): string => 'x'.Str::lower(Str::random(10));

    return [
        'platforms' => ['platforms', 'catalog.view', 'catalog.manage', fn () => ['name' => 'Alpha', 'slug' => $slug(), 'is_active' => true]],
        'categories' => ['categories', 'catalog.view', 'catalog.manage', fn () => ['name' => 'Gift', 'slug' => $slug(), 'is_active' => true]],
        'regions' => ['regions', 'catalog.view', 'catalog.manage', fn () => ['code' => strtoupper(Str::random(6)), 'name' => 'EU']],
        'attributes' => ['attributes', 'catalog.view', 'catalog.manage', fn () => ['code' => 'a'.$slug(), 'name' => 'Edition', 'type' => 'text']],
        'delivery fields' => ['delivery-fields', 'catalog.view', 'catalog.manage', fn () => ['code' => 'f'.$slug(), 'label' => 'Player', 'input_type' => 'text']],
        'products' => ['products', 'catalog.view', 'catalog.manage', fn () => [
            'gaming_platform_id' => GamingPlatform::factory()->create()->id,
            'primary_category_id' => Category::factory()->create()->id,
            'name' => 'Credit', 'slug' => $slug(), 'sku' => strtoupper($slug()), 'delivery_type' => 'instant_code',
        ]],
        'pages' => ['pages', 'content.pages', 'content.pages', fn () => ['title' => 'About', 'slug' => $slug(), 'body' => '<p>Hi</p>']],
        'blog categories' => ['blog-categories', 'content.blog', 'content.blog', fn () => ['name' => 'News', 'slug' => $slug()]],
        'blog posts' => ['blog-posts', 'content.blog', 'content.blog', fn () => ['title' => 'Post', 'slug' => $slug(), 'body' => '<p>Hi</p>']],
        'faq groups' => ['faq-groups', 'content.faq', 'content.faq', fn () => ['name' => 'General', 'slug' => $slug()]],
        'faqs' => ['faqs', 'content.faq', 'content.faq', fn () => ['faq_group_id' => FaqGroup::factory()->create()->id, 'question' => 'Q?', 'answer' => 'A', 'is_active' => true]],
        'menus' => ['menus', 'content.menus', 'content.menus', fn () => ['location' => 'l'.$slug(), 'name' => 'Header']],
        'homepage sections' => ['homepage-sections', 'content.homepage', 'content.homepage', fn () => ['key' => 'k'.$slug(), 'source' => 'manual', 'is_active' => true, 'sort_order' => 1]],
        'banners' => ['banners', 'marketing.banners', 'marketing.banners', fn () => ['placement' => 'hero', 'media_id' => Media::factory()->create()->id, 'is_active' => true]],
        'redirects' => ['redirects', 'seo.redirects', 'seo.redirects', fn () => ['source_path' => '/old-'.$slug(), 'target_path' => '/new', 'status_code' => 301, 'is_active' => true]],
    ];
}

it('requires authentication', function (string $uri): void {
    $this->getJson("/admin/api/{$uri}")->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
})->with(fn (): array => array_map(static fn (array $resource): array => [$resource[0]], adminCrudResources()));

it('denies staff without the view or manage permission', function (string $uri, string $view, string $manage, Closure $payload): void {
    CurrencyFactory::ensure('USD');
    actingAsStaffWith(array_values(array_diff(allPermissionNames(), [$view, $manage])));

    $this->getJson("/admin/api/{$uri}")->assertForbidden();
    $this->postJson("/admin/api/{$uri}", $payload())->assertForbidden();
})->with(fn (): array => adminCrudResources());

it('lets a view-only user read but not write when the permissions differ', function (string $uri, string $view, string $manage, Closure $payload): void {
    actingAsStaffWith([$view]);

    $this->getJson("/admin/api/{$uri}")->assertOk();

    if ($view !== $manage) {
        $this->postJson("/admin/api/{$uri}", $payload())->assertForbidden();
    }
})->with(fn (): array => adminCrudResources());

it('creates, reads, updates and deletes with the manage permission, leaving an audit trail', function (string $uri, string $view, string $manage, Closure $payload): void {
    actingAsStaffWith([$view, $manage]);

    $created = $this->postJson("/admin/api/{$uri}", $payload())->assertCreated()->assertJsonPath('success', true);

    $key = $created->json('data.public_id') ?? $created->json('data.id');

    $this->getJson("/admin/api/{$uri}/{$key}")->assertOk();
    $this->getJson("/admin/api/{$uri}")->assertOk()->assertJsonPath('meta.total', 1);
    $this->patchJson("/admin/api/{$uri}/{$key}", [])->assertOk();
    $this->deleteJson("/admin/api/{$uri}/{$key}")->assertOk();
    $this->getJson("/admin/api/{$uri}/{$key}")->assertNotFound();

    expect(DB::table('audit_logs')->where('action', 'like', '%.created')->count())->toBeGreaterThanOrEqual(1)
        ->and(DB::table('audit_logs')->where('action', 'like', '%.deleted')->count())->toBeGreaterThanOrEqual(1);
})->with(fn (): array => adminCrudResources());

it('returns field errors in the envelope for invalid input', function (): void {
    actingAsStaffWith(['catalog.manage']);

    $this->postJson('/admin/api/products', ['name' => ''])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'validation_failed')
        ->assertJsonStructure(['errors' => ['name', 'slug', 'sku', 'delivery_type', 'gaming_platform_id', 'primary_category_id']]);
});

it('reorders by the position of each id', function (): void {
    actingAsStaffWith(['catalog.manage']);
    [$first, $second] = GamingPlatform::factory()->count(2)->create();

    $this->postJson('/admin/api/platforms/reorder', ['ids' => [$second->id, $first->id]])->assertOk();

    expect($second->refresh()->sort_order)->toBe(0)->and($first->refresh()->sort_order)->toBe(1);
});

it('refuses to delete a category or platform that still has products', function (string $uri, string $key): void {
    actingAsStaffWith(['catalog.manage']);
    $product = Product::factory()->create();

    $this->deleteJson("/admin/api/{$uri}/{$product->{$key}}")->assertStatus(409)->assertJsonPath('code', 'conflict');
})->with([
    ['platforms', 'gaming_platform_id'],
    ['categories', 'primary_category_id'],
]);

it('scopes nested variants to their product', function (): void {
    actingAsStaffWith(['catalog.view']);
    $variant = ProductVariant::factory()->create();
    $other = Product::factory()->create();

    $this->getJson("/admin/api/products/{$other->public_id}/variants/{$variant->public_id}")->assertNotFound();
    $this->getJson("/admin/api/products/{$variant->product->public_id}/variants/{$variant->public_id}")->assertOk();
});

it('builds no N+1 on the admin product list', function (): void {
    actingAsStaffWith(['catalog.view']);
    Product::factory()->count(5)->create();
    DB::enableQueryLog();

    $this->getJson('/admin/api/products')->assertOk()->assertJsonPath('meta.total', 5);
    $few = count(DB::getQueryLog());

    Product::factory()->count(20)->create();
    DB::flushQueryLog();

    $this->getJson('/admin/api/products?per_page=100')->assertOk()->assertJsonPath('meta.total', 25);

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual($few);
});
