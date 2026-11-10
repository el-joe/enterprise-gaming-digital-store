<?php

namespace Database\Seeders;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Seed clearly labelled sample catalog data for development, using no real brands.
     * Run explicitly: php artisan db:seed --class=DemoCatalogSeeder
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoCatalogSeeder refuses to run when APP_ENV=production.');
        }

        $this->call(CurrencySeeder::class);

        $platform = GamingPlatform::query()->updateOrCreate(['slug' => 'demo-platform'], [
            'name' => 'Demo Platform',
            'is_active' => true,
        ]);

        $category = Category::query()->updateOrCreate(['slug' => 'demo-game-credit'], [
            'name' => 'Demo Game Credit',
            'is_active' => true,
        ]);

        foreach (['10', '25', '50'] as $denomination) {
            $product = Product::query()->firstOrNew(['sku' => 'DEMO-CREDIT']);

            $product->forceFill([
                'gaming_platform_id' => $platform->id,
                'primary_category_id' => $category->id,
                'name' => 'Demo Game Credit',
                'slug' => 'demo-game-credit',
                'delivery_type' => DeliveryType::Manual,
                'status' => ProductStatus::Draft,
            ])->save();

            $variant = ProductVariant::query()->firstOrNew(['sku' => "DEMO-CREDIT-{$denomination}"]);

            $variant->forceFill([
                'product_id' => $product->id,
                'title' => "Demo Credit {$denomination} USD",
                'denomination_value' => $denomination,
                'denomination_currency' => 'USD',
                'availability' => ProductVariantAvailability::OutOfStock,
                'is_active' => true,
            ])->save();
        }
    }
}
