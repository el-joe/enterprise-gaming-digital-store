<?php

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Models\SearchDocument;
use Database\Factories\Currency\CurrencyFactory;

/**
 * @return array<string, mixed>
 */
function productPayload(array $overrides = []): array
{
    return [
        'gaming_platform_id' => GamingPlatform::factory()->create()->id,
        'primary_category_id' => Category::factory()->create()->id,
        'name' => 'Steamy Wallet',
        'slug' => 'steamy-wallet',
        'sku' => 'STM-WALLET',
        'delivery_type' => 'instant_code',
        ...$overrides,
    ];
}

it('creates a product as a draft regardless of what the client sends', function (): void {
    actingAsStaffWith(['catalog.manage']);

    $response = $this->postJson('/admin/api/products', productPayload(['status' => 'active']))->assertCreated();

    expect($response->json('data.status'))->toBe('draft');
});

it('sanitizes the description', function (): void {
    actingAsStaffWith(['catalog.manage']);

    $response = $this->postJson('/admin/api/products', productPayload(['description' => '<p onclick="x()">Ok</p><script>alert(1)</script>']))->assertCreated();

    expect($response->json('data.description'))->toBe('<p>Ok</p>');
});

it('syncs categories, regions, delivery fields and restrictions', function (): void {
    actingAsStaffWith(['catalog.manage']);
    $extra = Category::factory()->create();
    $region = Region::factory()->create();
    $field = DeliveryFieldDefinition::factory()->create();
    DB::table('countries')->insertOrIgnore(['code' => 'EG', 'name' => 'Egypt', 'is_active' => true]);

    $response = $this->postJson('/admin/api/products', productPayload([
        'category_ids' => [$extra->id],
        'region_ids' => [$region->id],
        'delivery_fields' => [['delivery_field_definition_id' => $field->id, 'is_required' => true]],
        'country_restrictions' => [['country_code' => 'EG', 'mode' => 'deny']],
    ]))->assertCreated();

    expect($response->json('data.category_ids'))->toBe([$extra->id])
        ->and($response->json('data.region_codes'))->toBe([$region->code])
        ->and($response->json('data.delivery_fields.0.delivery_field_definition_id'))->toBe($field->id)
        ->and($response->json('data.country_restrictions.0'))->toBe(['country_code' => 'EG', 'mode' => 'deny']);
});

it('refuses a duplicate sku or slug, including one held by a deleted product', function (): void {
    actingAsStaffWith(['catalog.manage']);
    $deleted = Product::factory()->create(['sku' => 'TAKEN', 'slug' => 'taken']);
    $deleted->delete();

    $this->postJson('/admin/api/products', productPayload(['sku' => 'TAKEN', 'slug' => 'taken']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sku', 'slug'], 'errors');
});

it('needs catalog.publish to publish', function (): void {
    actingAsStaffWith(['catalog.manage']);
    $product = Product::factory()->create();

    $this->postJson("/admin/api/products/{$product->public_id}/activate")->assertForbidden();
});

it('refuses to publish without an active variant', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $product = Product::factory()->create();

    $this->postJson("/admin/api/products/{$product->public_id}/activate")
        ->assertUnprocessable()
        ->assertJsonPath('code', 'product_not_publishable')
        ->assertJsonStructure(['errors' => ['variants']]);

    expect($product->refresh()->status)->toBe(ProductStatus::Draft);
});

it('refuses to publish a top-up with no required delivery field', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $product = Product::factory()->create(['delivery_type' => DeliveryType::DirectTopup]);
    ProductVariant::factory()->for($product)->create();

    $this->postJson("/admin/api/products/{$product->public_id}/activate")
        ->assertUnprocessable()
        ->assertJsonStructure(['errors' => ['delivery_fields']]);
});

it('publishes, sets the publication date, audits and activates the search document', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $product = Product::factory()->create();
    ProductVariant::factory()->for($product)->create();

    $this->postJson("/admin/api/products/{$product->public_id}/activate")->assertOk()->assertJsonPath('data.status', 'active');

    expect($product->refresh()->published_at)->not->toBeNull()
        ->and(SearchDocument::query()->where('searchable_id', $product->id)->where('searchable_type', 'product')->where('is_active', true)->exists())->toBeTrue();
    $this->assertDatabaseHas('audit_logs', ['action' => 'catalog.product.status_changed', 'subject_id' => $product->id]);
});

it('applies bulk actions and reports individual failures', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $ready = Product::factory()->create();
    ProductVariant::factory()->for($ready)->create();
    $notReady = Product::factory()->create();

    $this->postJson('/admin/api/products/bulk', ['action' => 'activate', 'ids' => [$ready->public_id, $notReady->public_id]])
        ->assertOk()
        ->assertJsonPath('data.processed', 1)
        ->assertJsonPath('data.failed.0.id', $notReady->public_id)
        ->assertJsonPath('data.failed.0.name', $notReady->name)
        ->assertJsonPath('data.failed.0.reasons.0', 'No active variant: activate a variant that has a price.')
        ->assertJsonPath('message', '1 done, 1 not done.');
});

it('explains that custom-amount-only products cannot be published yet', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $range = Product::factory()->create();
    ProductVariant::factory()->for($range)->create(['is_custom_amount' => true, 'is_active' => false, 'price_base' => null]);

    $this->postJson('/admin/api/products/bulk', ['action' => 'activate', 'ids' => [$range->public_id]])
        ->assertOk()
        ->assertJsonPath('data.processed', 0)
        ->assertJsonPath('data.failed.0.reasons.0', 'Only custom-amount (range) variants, which cannot be priced yet. Add fixed amounts to sell this product.');
});

it('requires catalog.manage for bulk delete', function (): void {
    actingAsStaffWith(['catalog.publish']);
    $product = Product::factory()->create();

    $this->postJson('/admin/api/products/bulk', ['action' => 'delete', 'ids' => [$product->public_id]])->assertForbidden();

    expect($product->refresh()->exists)->toBeTrue();
});

describe('variants', function (): void {
    beforeEach(function (): void {
        CurrencyFactory::ensure('USD');
        actingAsStaffWith(['catalog.manage']);
        $this->product = Product::factory()->create();
    });

    it('marks a new variant dirty for pricing', function (): void {
        $response = $this->postJson("/admin/api/products/{$this->product->public_id}/variants", [
            'sku' => 'V-1', 'title' => '10 USD', 'denomination_value' => '10', 'denomination_currency' => 'USD', 'is_active' => true,
        ])->assertCreated();

        expect($response->json('data.pricing_dirty_at'))->not->toBeNull();
    });

    it('rejects money sent as a JSON number', function (): void {
        $this->postJson("/admin/api/products/{$this->product->public_id}/variants", [
            'sku' => 'V-1', 'title' => '10 USD', 'denomination_value' => 10.5, 'denomination_currency' => 'USD', 'is_active' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors(['denomination_value'], 'errors');
    });

    it('requires a denomination on fixed variants and a range on custom ones', function (array $payload, string $field): void {
        $this->postJson("/admin/api/products/{$this->product->public_id}/variants", ['sku' => 'V-2', 'title' => 'T', 'is_active' => true, ...$payload])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'errors');
    })->with([
        'fixed without value' => [[], 'denomination_value'],
        'custom without range' => [['is_custom_amount' => true], 'custom_min'],
        'custom with inverted range' => [['is_custom_amount' => true, 'custom_min' => '50', 'custom_max' => '10'], 'custom_max'],
    ]);

    it('rejects a duplicate denomination when the region is null', function (): void {
        $payload = ['title' => 'T', 'denomination_value' => '10', 'denomination_currency' => 'USD', 'is_active' => true];

        $this->postJson("/admin/api/products/{$this->product->public_id}/variants", [...$payload, 'sku' => 'V-A'])->assertCreated();
        $this->postJson("/admin/api/products/{$this->product->public_id}/variants", [...$payload, 'sku' => 'V-B'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['denomination_value'], 'errors');
    });
});
