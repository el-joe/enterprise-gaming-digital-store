<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\SearchDocument;
use Illuminate\Database\Eloquent\Model;

/**
 * Maintains the denormalized search_documents table: one row per entity per locale.
 */
class SearchIndexer
{
    private const int TITLE_LIMIT = 255;

    private const int CHUNK = 200;

    public function indexProduct(Product $product): void
    {
        $product->loadMissing([
            'gamingPlatform:id,name',
            'primaryCategory:id,name',
            'categories:id,name',
            'productVariants:id,product_id,sku,title',
            'productAttributeValues:id,product_id,value',
        ]);

        $keywords = collect([
            $product->sku,
            $product->gamingPlatform?->name,
            $product->primaryCategory?->name,
            ...$product->categories->pluck('name'),
            ...$product->productVariants->flatMap(static fn (ProductVariant $variant): array => [$variant->sku, $variant->title]),
            ...$product->productAttributeValues->map(static fn (ProductAttributeValue $value): string => $value->value),
        ])->filter()->unique()->implode(' ');

        $this->write($product, $product->name, $keywords, (int) min($product->sales_count, 1000), $product->status === ProductStatus::Active);
    }

    public function indexPlatform(GamingPlatform $platform): void
    {
        $this->write($platform, $platform->name, $platform->slug, 2000 + (int) min($platform->popularity_score, 1000), $platform->is_active);
    }

    public function indexCategory(Category $category): void
    {
        $this->write($category, $category->name, $category->slug, 1500, $category->is_active);
    }

    public function remove(Model $model): void
    {
        SearchDocument::query()
            ->where('searchable_type', $model->getMorphClass())
            ->where('searchable_id', $model->getKey())
            ->delete();

    }

    /**
     * Rebuild every document. Returns the number of entities indexed.
     */
    public function rebuild(): int
    {
        $count = 0;

        Product::query()->chunkById(self::CHUNK, function ($products) use (&$count): void {
            $products->each(function (Product $product) use (&$count): void {
                $this->indexProduct($product);
                $count++;
            });
        });

        GamingPlatform::query()->chunkById(self::CHUNK, function ($platforms) use (&$count): void {
            $platforms->each(function (GamingPlatform $platform) use (&$count): void {
                $this->indexPlatform($platform);
                $count++;
            });
        });

        Category::query()->chunkById(self::CHUNK, function ($categories) use (&$count): void {
            $categories->each(function (Category $category) use (&$count): void {
                $this->indexCategory($category);
                $count++;
            });
        });

        return $count;
    }

    private function write(Model $model, string $title, string $keywords, int $boost, bool $isActive): void
    {
        $translations = ContentTranslation::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('field', 'name')
            ->pluck('value', 'locale');

        foreach (config('catalog.search_locales') as $locale) {
            $localizedTitle = (string) ($translations[$locale] ?? $title);

            SearchDocument::query()->updateOrCreate(
                ['searchable_type' => $model->getMorphClass(), 'searchable_id' => $model->getKey(), 'locale' => $locale],
                [
                    'title' => mb_substr($localizedTitle, 0, self::TITLE_LIMIT),
                    'keywords' => $localizedTitle === $title ? $keywords : $title.' '.$keywords,
                    'boost' => $boost,
                    'is_active' => $isActive,
                ],
            );
        }
    }
}
