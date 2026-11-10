<?php

/*
 * Fixtures for the storefront page tests (M12).
 */

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Shared\ValueObjects\Money;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Testing\TestResponse;

/**
 * A listed product (active, priced, in stock) on the `steam` platform in the `gift-cards`
 * category, created once per name.
 *
 * @param  array<string, mixed>  $product
 * @param  array<string, mixed>  $variant
 */
function storefrontProduct(string $name = 'Steam Wallet $20', string $price = '20', array $product = [], array $variant = []): Product
{
    CurrencyFactory::ensure('USD');
    CountryFactory::ensure('US');

    $platform = GamingPlatform::query()->firstWhere('slug', 'steam') ?? GamingPlatform::factory()->create(['slug' => 'steam', 'name' => 'Steam']);
    $category = Category::query()->firstWhere('slug', 'gift-cards') ?? Category::factory()->create(['slug' => 'gift-cards', 'name' => 'Gift cards']);

    $model = Product::factory()->create([
        'status' => ProductStatus::Active,
        'name' => $name,
        'gaming_platform_id' => $platform->id,
        'primary_category_id' => $category->id,
        ...$product,
    ]);

    ProductVariant::factory()->for($model)->create([
        'title' => '$'.$price,
        'manual_cost_base' => $price,
        'availability' => ProductVariantAvailability::InStock,
        ...$variant,
    ])->forceFill(['price_base' => $price])->save();

    return $model;
}

/**
 * A base-currency amount as the server formats it (CurrencyConverter::present).
 */
function storefrontPrice(string $amount, string $currency = 'USD'): string
{
    return app(CurrencyConverter::class)->present(Money::of($amount, $currency))['formatted'];
}

function storefrontVariant(Product $product): ProductVariant
{
    return ProductVariant::query()->where('product_id', $product->id)->firstOrFail();
}

/**
 * Every <script> on a page either loads a file or carries the response's CSP nonce, and no
 * element has an inline style attribute.
 */
function assertCspSafeHtml(TestResponse $response): void
{
    $policy = (string) $response->headers->get('Content-Security-Policy');
    preg_match("/'nonce-([^']+)'/", $policy, $nonce);

    expect($nonce[1] ?? null)->not->toBeNull();

    $html = $response->getContent();
    preg_match_all('/<script\b[^>]*>/i', $html, $scripts);

    foreach ($scripts[0] as $tag) {
        expect(str_contains($tag, ' src=') || str_contains($tag, 'nonce="'.$nonce[1].'"'))->toBeTrue("Script without nonce: {$tag}");
    }

    preg_match_all('/<style\b[^>]*>/i', $html, $styles);

    foreach ($styles[0] as $tag) {
        expect($tag)->toContain('nonce="'.$nonce[1].'"');
    }

    expect($html)->not->toMatch('/<[a-z][^>]*\sstyle="/i')
        ->and($html)->not->toMatch('/\son(click|load|error|submit|change)=/i');
}

/**
 * Render pages without a Vite build (public/build is not committed): entry tags and fonts are
 * empty and asset URLs point at their source path. The CSP nonce still works.
 */
function storefrontWithoutBuild(): void
{
    Illuminate\Support\Facades\Vite::clearResolvedInstance();

    app()->instance(Vite::class, new class extends Vite
    {
        public function __invoke($entrypoints, $buildDirectory = null)
        {
            return new HtmlString('');
        }

        public function asset($asset, $buildDirectory = null)
        {
            return '/'.ltrim($asset, '/');
        }

        public function fonts($aliases = null)
        {
            return new HtmlString('');
        }
    });
}
