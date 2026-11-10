<?php

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Services\DeliveryFieldValidator;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

describe('category slugs', function (): void {
    beforeEach(fn () => actingAsStaffWith(['catalog.manage']));

    it('rejects reserved words', function (string $slug): void {
        $this->postJson('/admin/api/categories', ['name' => 'X', 'slug' => $slug, 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug'], 'errors');
    })->with(['admin', 'api', 'checkout', 'cart', 'search', 'gaming', 'products', 'login', 'webhooks', 'sitemap']);

    it('reserves the first segment of any registered route automatically', function (): void {
        Route::get('/brand-new-area/thing', static fn () => 'ok');
        app('router')->getRoutes()->refreshNameLookups();

        $this->postJson('/admin/api/categories', ['name' => 'X', 'slug' => 'brand-new-area', 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug'], 'errors');
    });

    it('rejects malformed slugs', function (string $slug): void {
        $this->postJson('/admin/api/categories', ['name' => 'X', 'slug' => $slug, 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug'], 'errors');
    })->with(['Upper-Case', 'has space', 'double--hyphen', '-leading', '../etc']);

    it('accepts an ordinary slug', function (): void {
        $this->postJson('/admin/api/categories', ['name' => 'Gift Cards', 'slug' => 'gift-cards', 'is_active' => true])->assertCreated();
    });

    it('rejects a category nested under its own descendant', function (): void {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->patchJson("/admin/api/categories/{$parent->id}", ['parent_id' => $child->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id'], 'errors');
    });
});

describe('delivery field definitions', function (): void {
    beforeEach(fn () => actingAsStaffWith(['catalog.manage']));

    it('rejects a regex that does not compile', function (): void {
        $this->postJson('/admin/api/delivery-fields', ['code' => 'x', 'label' => 'X', 'input_type' => 'text', 'validation_regex' => '([a-z'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['validation_regex'], 'errors');
    });

    it('requires options for a select', function (): void {
        $this->postJson('/admin/api/delivery-fields', ['code' => 'x', 'label' => 'X', 'input_type' => 'select'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['options'], 'errors');
    });

    it('refuses to delete a definition in use', function (): void {
        $field = ProductDeliveryField::factory()->create();

        $this->deleteJson("/admin/api/delivery-fields/{$field->delivery_field_definition_id}")->assertStatus(409);
    });
});

describe('customer delivery values', function (): void {
    /**
     * @param  array<string, mixed>  $definition
     */
    function productWithDeliveryField(array $definition, bool $required = true): Product
    {
        $product = Product::factory()->create();

        ProductDeliveryField::factory()->create([
            'product_id' => $product->id,
            'delivery_field_definition_id' => DeliveryFieldDefinition::factory()->create(['code' => 'player_id', 'label' => 'Player ID', ...$definition])->id,
            'is_required' => $required,
        ]);

        return $product;
    }

    function deliveryErrorsFor(Product $product, array $values): array
    {
        try {
            app(DeliveryFieldValidator::class)->validate($product, $values);

            return [];
        } catch (ValidationException $exception) {
            return $exception->errors();
        }
    }

    it('accepts and trims a valid value', function (): void {
        $product = productWithDeliveryField(['input_type' => DeliveryFieldDefinitionInputType::Text, 'validation_regex' => '[0-9]{8}']);

        expect(app(DeliveryFieldValidator::class)->validate($product, ['player_id' => ' 12345678 ']))->toBe(['player_id' => '12345678']);
    });

    it('requires a required field', function (): void {
        $product = productWithDeliveryField(['input_type' => DeliveryFieldDefinitionInputType::Text]);

        expect(deliveryErrorsFor($product, []))->toHaveKey('delivery_values.player_id');
    });

    it('skips an absent optional field', function (): void {
        $product = productWithDeliveryField(['input_type' => DeliveryFieldDefinitionInputType::Text], required: false);

        expect(deliveryErrorsFor($product, []))->toBe([]);
    });

    it('applies the pattern to the whole value', function (string $value): void {
        $product = productWithDeliveryField(['input_type' => DeliveryFieldDefinitionInputType::Text, 'validation_regex' => '[0-9]{8}']);

        expect(deliveryErrorsFor($product, ['player_id' => $value]))->toHaveKey('delivery_values.player_id');
    })->with(['123', '123456789', 'abc12345678', "1234\n5678"]);

    it('validates each input type', function (DeliveryFieldDefinitionInputType $type, ?array $options, string $good, string $bad): void {
        $product = productWithDeliveryField(['input_type' => $type, 'options' => $options]);

        expect(deliveryErrorsFor($product, ['player_id' => $good]))->toBe([])
            ->and(deliveryErrorsFor($product, ['player_id' => $bad]))->toHaveKey('delivery_values.player_id');
    })->with([
        'number' => [DeliveryFieldDefinitionInputType::Number, null, '123', '12a'],
        'email' => [DeliveryFieldDefinitionInputType::Email, null, 'a@example.test', 'nope'],
        'select' => [DeliveryFieldDefinitionInputType::Select, [['value' => 'eu', 'label' => 'EU']], 'eu', 'us'],
    ]);

    it('rejects fields the product does not take and non-string values', function (): void {
        $product = productWithDeliveryField(['input_type' => DeliveryFieldDefinitionInputType::Text]);

        expect(deliveryErrorsFor($product, ['player_id' => 'ok', 'evil' => 'x']))->toHaveKey('delivery_values.evil')
            ->and(deliveryErrorsFor($product, ['player_id' => ['array']]))->toHaveKey('delivery_values.player_id');
    });
});
