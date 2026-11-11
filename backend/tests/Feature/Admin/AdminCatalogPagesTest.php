<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Models\Media;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
| M15: catalog products, supplier mappings (with bulk mapping), the media library and exchange rates.
*/

describe('products', function (): void {
    it('guards the product pages by permission', function (): void {
        $product = Product::factory()->create();

        adminWith(['orders.view']);
        $this->get('/admin/catalog/products')->assertForbidden();
        $this->get('/admin/catalog/products/'.$product->public_id)->assertForbidden();

        adminWith(['catalog.view']);
        $this->get('/admin/catalog/products')->assertOk();
        $this->get('/admin/catalog/products/create')->assertForbidden();
        $edit = $this->get('/admin/catalog/products/'.$product->public_id)->assertOk()
            ->assertSee(__('admin.common.read_only'))->assertDontSee('data-resource-submit', false)->assertDontSee('/activate', false);
        assertCspSafeHtml($edit);
    });

    it('shows the full editor with variants, delivery fields and status to catalog managers, in Arabic too', function (): void {
        $product = Product::factory()->create(['name' => 'Steam Wallet Code']);
        ProductVariant::factory()->create(['product_id' => $product->id, 'title' => '$20 card', 'sku' => 'STM-US-20']);
        DeliveryFieldDefinition::factory()->create(['label' => 'Player ID', 'code' => 'player_id']);
        adminWith(['catalog.view', 'catalog.manage', 'catalog.publish', 'mappings.manage']);

        $page = $this->get('/admin/catalog/products/'.$product->public_id)->assertOk()
            ->assertSee('Steam Wallet Code')->assertSee('STM-US-20')->assertSee('Player ID')
            ->assertSee('data-rows="delivery_fields"', false)->assertSee('data-resource-submit', false)
            ->assertSee(url('/admin/api/products/'.$product->public_id.'/activate'));
        assertCspSafeHtml($page);

        $this->withSession(['admin.locale' => 'ar'])->get('/admin/catalog/products/create')->assertOk()->assertSee('dir="rtl"', false)->assertSee('منتج جديد');
    });

    it('lists products server-side by public id, with bulk actions only for the permissions held', function (): void {
        $product = Product::factory()->create(['sku' => 'SKU-ONE']);
        adminWith(['catalog.view']);

        postTable('products', ['search' => ['value' => 'SKU-O']])->assertOk()
            ->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.DT_RowId', $product->public_id);
        $this->get('/admin/catalog/products')->assertDontSee('data-dt-bulk-action="activate"', false);

        adminWith(['catalog.view', 'catalog.publish']);
        $this->get('/admin/catalog/products')->assertSee('data-dt-bulk-action="activate"', false)->assertDontSee('data-dt-bulk-action="delete"', false);
    });

    it('refuses bulk product actions at the route without catalog.publish or catalog.manage', function (): void {
        $product = Product::factory()->create();
        adminWith(['catalog.view']);

        $this->postJson('/admin/api/products/bulk', ['action' => 'deactivate', 'ids' => [$product->public_id]])->assertForbidden();
    });

    it('saves delivery field rows sent as a list of objects', function (): void {
        $product = Product::factory()->create();
        $field = DeliveryFieldDefinition::factory()->create();
        adminWith(['catalog.view', 'catalog.manage']);

        $this->patchJson('/admin/api/products/'.$product->public_id, [
            'delivery_fields' => [['delivery_field_definition_id' => (string) $field->id, 'is_required' => true, 'sort_order' => '1']],
        ])->assertOk();

        expect($product->productDeliveryFields()->where('delivery_field_definition_id', $field->id)->exists())->toBeTrue();
    });
});

describe('supplier mappings', function (): void {
    beforeEach(function (): void {
        $this->supplier = Supplier::factory()->create();
        $this->variantA = ProductVariant::factory()->create(['sku' => 'BULK-A']);
        $this->variantB = ProductVariant::factory()->create(['sku' => 'BULK-B']);
        SupplierProduct::factory()->create(['supplier_id' => $this->supplier->id, 'external_id' => 'EXT-1']);
        SupplierProduct::factory()->create(['supplier_id' => $this->supplier->id, 'external_id' => 'EXT-2']);
    });

    it('maps many variants at once, each audited', function (): void {
        adminWith(['mappings.manage']);

        $this->postJson('/admin/api/supplier-mappings/bulk', [
            'supplier_id' => $this->supplier->id,
            'rows' => "# sku,external,price,currency\nBULK-A,EXT-1,19.40,USD,primary,10\nBULK-B,EXT-2,48.5,usd",
        ])->assertCreated()->assertJsonPath('data.created', 2);

        expect(SupplierProductMapping::query()->where('supplier_id', $this->supplier->id)->count())->toBe(2)
            ->and(SupplierProductMapping::query()->where('product_variant_id', $this->variantB->id)->value('role')->value)->toBe('secondary')
            ->and(AuditLog::query()->where('action', 'suppliers.mapping.created')->count())->toBe(2);
    });

    it('saves nothing when any line is refused and names the lines', function (): void {
        adminWith(['mappings.manage']);

        $this->postJson('/admin/api/supplier-mappings/bulk', [
            'supplier_id' => $this->supplier->id,
            'rows' => "BULK-A,EXT-1,19.40,USD\nNOPE,EXT-2,1,USD\nBULK-B,EXT-9,1,USD\nBULK-B,EXT-2,-1,USD",
        ])->assertUnprocessable()->assertJsonValidationErrors('rows')
            ->assertJsonPath('errors.rows.0', 'Line 2: no variant has the SKU "NOPE".')
            ->assertJsonPath('errors.rows.1', 'Line 3: the supplier has no product "EXT-9".')
            ->assertJsonPath('errors.rows.2', 'Line 4: the price must be a positive decimal.');

        expect(SupplierProductMapping::query()->count())->toBe(0);
    });

    it('rolls back every line when a later mapping breaks a rule (one primary per variant)', function (): void {
        adminWith(['mappings.manage']);

        $this->postJson('/admin/api/supplier-mappings/bulk', [
            'supplier_id' => $this->supplier->id,
            'rows' => "BULK-A,EXT-1,1,USD,primary\nBULK-A,EXT-2,1,USD,primary",
        ])->assertUnprocessable()->assertJsonPath('errors.rows.0', fn (string $message): bool => str_starts_with($message, 'Line 2:'));

        expect(SupplierProductMapping::query()->count())->toBe(0);
    });

    it('switches selected mappings on and off with mappings.manage only', function (): void {
        $mapping = SupplierProductMapping::factory()->create(['supplier_id' => $this->supplier->id, 'is_active' => true]);

        adminWith(['suppliers.view']);
        $this->postJson('/admin/api/supplier-mappings/bulk-status', ['ids' => [$mapping->id], 'is_active' => false])->assertForbidden();
        $this->postJson('/admin/api/supplier-mappings/bulk', ['supplier_id' => $this->supplier->id, 'rows' => 'x'])->assertForbidden();
        $this->get('/admin/catalog/mappings')->assertForbidden();

        adminWith(['mappings.manage']);
        $this->get('/admin/catalog/mappings')->assertOk()->assertSee('data-dt-bulk-action="deactivate"', false);
        $this->postJson('/admin/api/supplier-mappings/bulk-status', ['ids' => [$mapping->id], 'is_active' => false])->assertOk()->assertJsonPath('data.changed', 1);

        expect($mapping->refresh()->is_active)->toBeFalse();
        postTable('mappings', ['filters' => ['active' => '0']])->assertOk()->assertJsonPath('recordsFiltered', 1);
    });
});

describe('media library', function (): void {
    it('uploads a validated image with content.media and refuses other files', function (): void {
        Storage::fake('public');
        adminWith(['orders.view']);
        $this->get('/admin/content/media')->assertForbidden();
        $this->post('/admin/api/media', ['file' => UploadedFile::fake()->image('a.png', 40, 40)], ['Accept' => 'application/json'])->assertForbidden();

        adminWith(['content.media']);
        $this->get('/admin/content/media')->assertOk()->assertSee('data-multipart', false);

        $this->post('/admin/api/media', ['file' => UploadedFile::fake()->create('evil.svg', 2, 'image/svg+xml')], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('file');

        $this->post('/admin/api/media', ['file' => UploadedFile::fake()->image('card.png', 40, 40), 'alt' => 'Card'], ['Accept' => 'application/json'])->assertCreated();

        $media = Media::query()->latest('id')->firstOrFail();
        postTable('media')->assertOk()->assertJsonPath('data.0.id', (string) $media->id);
    });
});

describe('exchange rates', function (): void {
    it('shows rates to pricing.view without actions and to currency.manage with them', function (): void {
        givenRate('EGP', '50.000000', ['pending_rate' => '75.000000', 'pending_since' => now()]);

        adminWith(['pricing.view']);
        $this->get('/admin/pricing/exchange-rates')->assertOk()->assertDontSee(__('admin.rates.apply'));
        expect((string) postTable('exchange-rates')->assertOk()->json('data.0.actions'))->toBe('');

        adminWith(['currency.manage']);
        $page = $this->get('/admin/pricing/exchange-rates')->assertOk()->assertSee(__('admin.rates.apply'))->assertSee('/__currency__/override', false);
        assertCspSafeHtml($page);
        expect((string) postTable('exchange-rates')->json('data.0.actions'))->toContain('approve-pending')->toContain('data-reason=');
    });

    it('refuses the rates page without a pricing or currency permission', function (): void {
        adminWith(['catalog.view']);
        $this->get('/admin/pricing/exchange-rates')->assertForbidden();
        postTable('exchange-rates')->assertForbidden();
        expect(ExchangeRate::query()->count())->toBe(0);
    });
});
