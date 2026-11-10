<?php

namespace App\Domains\Catalog\Jobs;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\SearchIndexer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Reindex the products under a platform or category after its name changes.
 */
class ReindexCatalogScope implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    private const int CHUNK = 200;

    /**
     * @param  'platform'|'category'  $scope
     */
    public function __construct(public string $scope, public int $scopeId) {}

    public function handle(SearchIndexer $indexer): void
    {
        $query = Product::query();

        if ($this->scope === 'platform') {
            $query->where('gaming_platform_id', $this->scopeId);
        } else {
            $query->where(function ($inner): void {
                $inner->where('primary_category_id', $this->scopeId)
                    ->orWhereHas('categories', fn ($categories) => $categories->where('categories.id', $this->scopeId));
            });
        }

        $query->chunkById(self::CHUNK, static function ($products) use ($indexer): void {
            $products->each(static fn (Product $product) => $indexer->indexProduct($product));
        });
    }
}
