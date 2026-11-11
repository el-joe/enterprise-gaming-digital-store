<?php

namespace App\Http\Support\AdminScreens;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Product;

/**
 * Fields of the product edit page (M15, Figma "Admin Catalog Edit"): basics, delivery and
 * descriptions; categories and regions; and the fields of a variant. Delivery fields, variants,
 * images and supplier mappings are their own cards on the page.
 */
final class ProductForm
{
    /**
     * @return array<string, list<Field>>
     */
    public function product(?Product $product): array
    {
        return [
            'basics' => [
                Field::text('name', __('admin.fields.product_title'))->required()->max(190)->wide(),
                Field::text('slug', __('admin.fields.slug'))->required()->max(210)->ltr()->help(__('admin.help.slug')),
                Field::text('sku', __('admin.fields.sku'))->required()->max(64)->ltr(),
                Field::select('gaming_platform_id', __('admin.fields.platform'), Options::platforms())->required(),
                Field::select('primary_category_id', __('admin.fields.primary_category'), Options::categories())->required(),
                Field::textarea('short_description', __('admin.fields.short_description'))->nullable()->max(500),
            ],
            'delivery' => [
                Field::select('delivery_type', __('admin.fields.delivery_type'), Options::enum('delivery_type', DeliveryType::cases()))->required(),
                Field::number('delivery_time_minutes', __('admin.fields.delivery_time_minutes'))->nullable(),
                Field::number('max_quantity_per_order', __('admin.fields.max_quantity_per_order'))->default(5)->help(__('admin.help.max_quantity')),
                Field::toggle('is_featured', __('admin.fields.featured_home')),
                Field::toggle('is_trending', __('admin.fields.is_trending')),
            ],
            'availability' => [
                Field::multi('category_ids', __('admin.fields.categories'), Options::categories())
                    ->using(static fn (Product $record): array => $record->categories->pluck('id')->all()),
                Field::multi('region_ids', __('admin.fields.regions'), Options::regions())
                    ->using(static fn (Product $record): array => $record->regions->pluck('id')->all()),
            ],
            'descriptions' => [
                Field::html('description', __('admin.fields.description'))->nullable()->help(__('admin.help.html')),
                Field::textarea('instructions', __('admin.fields.instructions'))->nullable(),
                Field::textarea('terms', __('admin.fields.terms'))->nullable(),
            ],
        ];
    }

    /**
     * @return list<Field>
     */
    public function variant(): array
    {
        return [
            Field::text('title', __('admin.fields.title'))->required()->max(190),
            Field::text('sku', __('admin.fields.sku'))->required()->max(80)->ltr(),
            Field::select('region_id', __('admin.fields.region'), Options::regions())->nullable(),
            Field::decimal('denomination_value', __('admin.fields.denomination_value'))->nullable(),
            Field::select('denomination_currency', __('admin.fields.denomination_currency'), Options::currencies())->nullable(),
            Field::select('availability', __('admin.fields.availability'), Options::enum('variant_availability', ProductVariantAvailability::cases()))->required()->default(ProductVariantAvailability::InStock->value),
            Field::decimal('manual_cost_base', __('admin.fields.manual_cost_base'))->nullable()->help(__('admin.help.base_money')),
            Field::decimal('compare_at_base', __('admin.fields.compare_at_base'))->nullable()->help(__('admin.help.base_money')),
            Field::toggle('is_custom_amount', __('admin.fields.is_custom_amount')),
            Field::decimal('custom_min', __('admin.fields.custom_min'))->nullable(),
            Field::decimal('custom_max', __('admin.fields.custom_max'))->nullable(),
            Field::number('sort_order', __('admin.fields.sort_order'))->default(0),
            Field::toggle('is_active', __('admin.fields.is_active'))->default(true),
        ];
    }
}
