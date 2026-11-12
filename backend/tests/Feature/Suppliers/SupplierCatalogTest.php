<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

/*
| Supplier catalog screen: the synced supplier products table and "Add to store".
*/

const ADD_PERMISSIONS = ['catalog.view', 'catalog.manage', 'mappings.manage'];

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    Queue::fake();
});

/**
 * A synced fixed-denomination gift card whose costs come from the supplier cost map (USD).
 *
 * @param  array<string, mixed>  $attributes
 */
function syncedCard(array $attributes = []): SupplierProduct
{
    return SupplierProduct::factory()->create([
        'name' => 'Steam Wallet US',
        'brand_name' => 'Steam',
        'country_code' => 'US',
        'denomination_type' => DenominationType::Fixed,
        'currency' => 'USD',
        'sender_currency' => 'USD',
        'fixed_denominations' => ['10', '25'],
        'cost_map' => ['10' => '9.5', '25' => '23.75'],
        'availability' => SupplierAvailability::Available,
        'last_seen_at' => now(),
        ...$attributes,
    ]);
}

function addToStore(array $ids, array $extra = []): TestResponse
{
    return test()->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => $ids, 'primary_category_id' => Category::factory()->create()->id, ...$extra]);
}

describe('table', function (): void {
    it('needs catalog.view for the page and the table, and shows add only with catalog.manage and mappings.manage', function (): void {
        syncedCard();

        adminWith(['orders.view']);
        $this->get('/admin/catalog/supplier-catalog')->assertForbidden();
        postTable('supplier-catalog')->assertForbidden();

        adminWith(['catalog.view']);
        $page = $this->get('/admin/catalog/supplier-catalog')->assertOk()
            ->assertSee(__('admin.supplier_catalog.intro'))->assertDontSee('data-dt-bulk-action="add_to_store"', false);
        assertCspSafeHtml($page);
        postTable('supplier-catalog')->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.actions', null);

        adminWith(ADD_PERMISSIONS);
        $this->get('/admin/catalog/supplier-catalog')->assertOk()->assertSee('data-dt-bulk-action="add_to_store"', false)->assertSee('supplier-catalog-add-fields', false);
        expect(postTable('supplier-catalog')->json('data.0.actions'))->toContain('data-confirm-fields="supplier-catalog-add-fields"');

        $this->withSession(['admin.locale' => 'ar'])->get('/admin/catalog/supplier-catalog')->assertOk()->assertSee('dir="rtl"', false)->assertSee('كتالوج الموردين');
    });

    it('filters by brand, type, status and in store, searches, and links products in the store', function (): void {
        $steam = syncedCard(['name' => 'Steam Wallet US']);
        syncedCard(['name' => 'Xbox Gift Card', 'category_name' => 'Gaming', 'brand_name' => 'Xbox', 'denomination_type' => DenominationType::Range, 'min_denomination' => '5', 'max_denomination' => '100', 'fixed_denominations' => null]);
        syncedCard(['name' => 'Old card', 'brand_name' => 'Old', 'removed_at' => now()]);
        $product = Product::factory()->create();
        SupplierProductMapping::factory()->create([
            'supplier_id' => $steam->supplier_id, 'supplier_product_id' => $steam->id,
            'product_variant_id' => ProductVariant::factory()->create(['product_id' => $product->id])->id,
        ]);
        adminWith(['catalog.view']);

        postTable('supplier-catalog', ['filters' => ['brand' => 'Xbox']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.name', 'Xbox Gift Card');
        postTable('supplier-catalog', ['filters' => ['type' => 'range']])->assertJsonPath('recordsFiltered', 1);
        postTable('supplier-catalog', ['filters' => ['status' => 'removed']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.name', 'Old card');
        postTable('supplier-catalog', ['search' => ['value' => 'Xbo']])->assertJsonPath('recordsFiltered', 1);

        $linked = postTable('supplier-catalog', ['filters' => ['in_store' => '1']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.DT_RowId', (string) $steam->id);
        expect($linked->json('data.0.in_store'))->toContain(route('admin.catalog.products.edit', ['product' => $product->public_id]))
            ->and($linked->json('data.0.amounts'))->toContain('10, 25');
        postTable('supplier-catalog', ['filters' => ['in_store' => '0']])->assertJsonPath('recordsFiltered', 2);
        postTable('supplier-catalog', ['filters' => ['brand' => 'Nope']])->assertUnprocessable();
        postTable('supplier-catalog', ['filters' => ['category' => 'Gaming']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.category', 'Gaming');
    });
});

describe('add to store', function (): void {
    it('creates an unpublished draft with one priced variant and one primary mapping per fixed amount, audited', function (): void {
        PricingRule::factory()->create(['percent_markup' => '20', 'is_active' => true]);
        $platform = GamingPlatform::factory()->create(['slug' => 'steam']);
        $card = syncedCard();
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id])->assertCreated()->assertJsonPath('data.added', 1)->assertJsonPath('data.already', 0)
            ->assertJsonPath('message', '1 added, 0 already in store, 0 skipped.');

        $product = Product::query()->sole();
        $variants = ProductVariant::query()->where('product_id', $product->id)->orderBy('denomination_value')->get();
        $mappings = SupplierProductMapping::query()->where('supplier_product_id', $card->id)->get();

        expect($product->status)->toBe(ProductStatus::Draft)->and($product->published_at)->toBeNull()
            ->and($product->name)->toBe('Steam Wallet US')->and($product->gaming_platform_id)->toBe($platform->id)
            ->and($product->productImages()->count())->toBe(0)
            ->and($variants->pluck('denomination_value')->all())->toBe(['10.000000', '25.000000'])
            ->and($variants->pluck('is_active')->all())->toBe([true, true])
            ->and($variants->pluck('price_base')->all())->toBe(['11.400000', '28.500000'])
            ->and($mappings)->toHaveCount(2)
            ->and($mappings->every(fn (SupplierProductMapping $mapping): bool => $mapping->role === SupplierProductMappingRole::Primary && $mapping->is_active))->toBeTrue()
            ->and(AuditLog::query()->where('action', 'suppliers.catalog.added_to_store')->count())->toBe(1)
            ->and(AuditLog::query()->where('action', 'catalog.product.created')->count())->toBe(1)
            ->and(AuditLog::query()->where('action', 'catalog.variant.created')->count())->toBe(2)
            ->and(AuditLog::query()->where('action', 'suppliers.mapping.created')->count())->toBe(2);

        Queue::assertPushed(SyncSupplierCatalog::class, fn (SyncSupplierCatalog $job): bool => $job->supplierId === $card->supplier_id && $job->type === 'pricing');
    });

    it('skips supplier products already in the store', function (): void {
        PricingRule::factory()->create(['percent_markup' => '20', 'is_active' => true]);
        $card = syncedCard();
        $platform = GamingPlatform::factory()->create();
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id], ['gaming_platform_id' => $platform->id])->assertCreated();
        addToStore([$card->id], ['gaming_platform_id' => $platform->id])->assertOk()
            ->assertJsonPath('data.added', 0)->assertJsonPath('data.already', 1);

        expect(Product::query()->count())->toBe(1)->and(ProductVariant::query()->count())->toBe(2);
    });

    it('leaves a variant inactive and reports why when no price can be computed', function (): void {
        $card = syncedCard(['cost_map' => ['10' => '9.5']]);
        PricingRule::factory()->create(['percent_markup' => '20', 'is_active' => true]);
        adminWith(ADD_PERMISSIONS);

        $response = addToStore([$card->id], ['gaming_platform_id' => GamingPlatform::factory()->create()->id])->assertCreated();

        $inactive = ProductVariant::query()->where('denomination_value', '25')->sole();
        expect($inactive->is_active)->toBeFalse()->and($inactive->price_base)->toBeNull()
            ->and(ProductVariant::query()->where('denomination_value', '10')->sole()->is_active)->toBeTrue()
            ->and($response->json('data.inactive'))->toBe([['sku' => $inactive->sku, 'reason' => 'cost_unavailable']]);
    });

    it('creates one inactive custom-amount variant for a range product, since the engine does not price custom amounts', function (): void {
        $card = syncedCard(['denomination_type' => DenominationType::Range, 'fixed_denominations' => null, 'min_denomination' => '5', 'max_denomination' => '500', 'cost_map' => ['rate' => '1.02']]);
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id], ['gaming_platform_id' => GamingPlatform::factory()->create()->id])->assertCreated();

        $variant = ProductVariant::query()->sole();
        expect($variant->is_custom_amount)->toBeTrue()->and($variant->custom_min)->toBe('5.000000')->and($variant->custom_max)->toBe('500.000000')
            ->and($variant->is_active)->toBeFalse()
            ->and(SupplierProductMapping::query()->sole()->product_variant_id)->toBe($variant->id);
    });

    it('skips products it cannot build and says why', function (): void {
        $removed = syncedCard(['removed_at' => now()]);
        $noBrand = syncedCard(['brand_name' => null, 'raw' => null]);
        adminWith(ADD_PERMISSIONS);

        $response = addToStore([$removed->id, $noBrand->id])->assertOk()->assertJsonPath('data.added', 0)->assertJsonCount(2, 'data.skipped');

        expect($response->json('data.skipped.0.reason'))->toBe(__('admin.supplier_catalog.reason_removed'))
            ->and($response->json('data.skipped.1.reason'))->toBe(__('admin.supplier_catalog.reason_no_brand'))
            ->and(Product::query()->count())->toBe(0);
        Queue::assertNothingPushed();
    });

    it('needs catalog.manage and mappings.manage, and refuses an unknown category beside its field', function (): void {
        $card = syncedCard();

        adminWith(['catalog.view', 'catalog.manage']);
        addToStore([$card->id])->assertForbidden();

        adminWith(['catalog.view', 'mappings.manage']);
        addToStore([$card->id])->assertForbidden();

        adminWith(ADD_PERMISSIONS);
        $this->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => [$card->id], 'primary_category_id' => 999999])->assertUnprocessable()->assertJsonValidationErrors('primary_category_id');
    });
});

describe('categories from the supplier', function (): void {
    it('offers the supplier category by default and works with no store categories at all', function (): void {
        adminWith(ADD_PERMISSIONS);

        expect(Category::query()->count())->toBe(0);
        $this->get('/admin/catalog/supplier-catalog')->assertOk()
            ->assertSee(__('admin.supplier_catalog.category_supplier'))
            ->assertSee('data-error-for="primary_category_id"', false)
            ->assertSee(route('admin.api.supplier-catalog.create-categories'));
    });

    it('creates the supplier category once for many products, in one bulk request and across requests', function (): void {
        PricingRule::factory()->create(['percent_markup' => '20', 'is_active' => true]);
        GamingPlatform::factory()->create(['slug' => 'steam']);
        $cards = collect(['US', 'GB', 'DE'])->map(fn (string $country) => syncedCard(['country_code' => $country, 'category_name' => 'Gaming']));
        $later = syncedCard(['country_code' => 'FR', 'category_name' => 'gaming']);
        adminWith(ADD_PERMISSIONS);

        $this->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => $cards->pluck('id')->all()])->assertCreated()->assertJsonPath('data.added', 3);
        $this->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => [$later->id]])->assertCreated();

        $category = Category::query()->sole();
        // "gaming" is a reserved storefront path, so the slug gets a suffix.
        expect($category->name)->toBe('Gaming')->and($category->slug)->toBe('gaming-cards')->and($category->is_active)->toBeTrue()
            ->and(Product::query()->where('primary_category_id', $category->id)->count())->toBe(4)
            ->and(AuditLog::query()->where('action', 'catalog.category.created')->count())->toBe(1);
    });

    it('reuses an existing category with the same name', function (): void {
        $existing = Category::factory()->create(['name' => 'Shopping', 'slug' => 'shop']);
        $card = syncedCard(['category_name' => 'SHOPPING']);
        adminWith(ADD_PERMISSIONS);

        $this->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => [$card->id], 'gaming_platform_id' => GamingPlatform::factory()->create()->id])->assertCreated();

        expect(Category::query()->count())->toBe(1)->and(Product::query()->sole()->primary_category_id)->toBe($existing->id);
    });

    it('lets a chosen category override the supplier category', function (): void {
        $override = Category::factory()->create(['name' => 'Gift cards']);
        $card = syncedCard(['category_name' => 'Gaming']);
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id], ['primary_category_id' => $override->id, 'gaming_platform_id' => GamingPlatform::factory()->create()->id])->assertCreated();

        expect(Product::query()->sole()->primary_category_id)->toBe($override->id)
            ->and(Category::query()->where('name', 'Gaming')->exists())->toBeFalse();
    });

    it('skips a product without a supplier category when no category is chosen', function (): void {
        $card = syncedCard(['category_name' => null]);
        adminWith(ADD_PERMISSIONS);

        $this->postJson('/admin/api/supplier-catalog/add-to-store', ['ids' => [$card->id], 'gaming_platform_id' => GamingPlatform::factory()->create()->id])->assertOk()
            ->assertJsonPath('data.added', 0)->assertJsonPath('data.skipped.0.reason', __('admin.supplier_catalog.reason_no_category'));

        expect(Product::query()->count())->toBe(0);
    });

    it('creates the missing categories from the supplier names without adding products', function (): void {
        Category::factory()->create(['name' => 'Crypto']);
        syncedCard(['category_name' => 'Crypto']);
        syncedCard(['category_name' => 'Travel']);
        syncedCard(['category_name' => 'Travel']);

        adminWith(['catalog.view']);
        $this->postJson('/admin/api/supplier-catalog/create-categories')->assertForbidden();

        adminWith(['catalog.view', 'catalog.manage']);
        $this->postJson('/admin/api/supplier-catalog/create-categories')->assertOk()->assertJsonPath('data.created', 1)->assertJsonPath('data.existing', 1);

        expect(Category::query()->pluck('name')->sort()->values()->all())->toBe(['Crypto', 'Travel'])->and(Product::query()->count())->toBe(0);
    });
});

describe('platforms from the supplier brand', function (): void {
    it('reuses an existing platform whose name matches the brand, ignoring case and spaces', function (): void {
        $existing = GamingPlatform::factory()->create(['name' => 'PlayStation', 'slug' => 'ps']);
        $card = syncedCard(['brand_name' => 'Play Station']);
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id])->assertCreated();

        expect(GamingPlatform::query()->count())->toBe(1)->and(Product::query()->sole()->gaming_platform_id)->toBe($existing->id);
    });

    it('creates an unknown brand once for many products, in bulk and across requests, active and without a logo', function (): void {
        $cards = collect(['US', 'GB'])->map(fn (string $country) => syncedCard(['country_code' => $country, 'brand_name' => null, 'raw' => ['brand' => ['brandName' => '1-800-PetSupplies', 'logoUrl' => 'https://cdn.example/logo.png']]]));
        $later = syncedCard(['country_code' => 'CA', 'brand_name' => '1-800-PetSupplies']);
        adminWith(ADD_PERMISSIONS);

        addToStore($cards->pluck('id')->all())->assertCreated()->assertJsonPath('data.added', 2);
        addToStore([$later->id])->assertCreated();

        $platform = GamingPlatform::query()->sole();
        expect($platform->name)->toBe('1-800-PetSupplies')->and($platform->slug)->toBe('1-800-petsupplies')->and($platform->is_active)->toBeTrue()
            ->and($platform->logo_media_id)->toBeNull()->and($platform->banner_media_id)->toBeNull()
            ->and(Product::query()->where('gaming_platform_id', $platform->id)->count())->toBe(3)
            ->and(AuditLog::query()->where('action', 'catalog.platform.created')->count())->toBe(1);
    });

    it('lets a chosen platform override the brand', function (): void {
        $override = GamingPlatform::factory()->create(['name' => 'Gift cards']);
        $card = syncedCard(['brand_name' => 'Steam']);
        adminWith(ADD_PERMISSIONS);

        addToStore([$card->id], ['gaming_platform_id' => $override->id])->assertCreated();

        expect(Product::query()->sole()->gaming_platform_id)->toBe($override->id)
            ->and(GamingPlatform::query()->count())->toBe(1);
    });
});

describe('bulk add', function (): void {
    it('adds several selected supplier products in one request, each in its own product', function (): void {
        PricingRule::factory()->create(['percent_markup' => '20', 'is_active' => true]);
        GamingPlatform::factory()->create(['slug' => 'steam']);
        $us = syncedCard(['name' => 'Steam Wallet', 'country_code' => 'US']);
        $gb = syncedCard(['name' => 'Steam Wallet', 'country_code' => 'GB']);
        $linked = syncedCard(['name' => 'Linked']);
        SupplierProductMapping::factory()->create(['supplier_id' => $linked->supplier_id, 'supplier_product_id' => $linked->id]);
        adminWith(ADD_PERMISSIONS);

        addToStore([(string) $us->id, (string) $gb->id, (string) $linked->id])->assertCreated()
            ->assertJsonPath('data.added', 2)->assertJsonPath('data.already', 1)->assertJsonCount(2, 'data.products');

        expect(Product::query()->where('sku', 'like', 'SP%')->pluck('slug')->sort()->values()->all())->toBe(['steam-wallet', 'steam-wallet-gb'])
            ->and(SupplierProductMapping::query()->whereIn('supplier_product_id', [$us->id, $gb->id])->count())->toBe(4);
    });

    it('limits one request to the bulk maximum', function (): void {
        adminWith(ADD_PERMISSIONS);

        addToStore(range(1, 101))->assertUnprocessable()->assertJsonValidationErrors('ids');
    });
});
