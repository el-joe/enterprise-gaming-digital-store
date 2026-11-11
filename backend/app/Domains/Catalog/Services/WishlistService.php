<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * The customer's wishlist (spec section 9, M13): saved products, newest first, shown as listing
 * cards in the display currency. Only listed products can be added; a saved product that stops
 * being listed stays saved but is not shown. One row per customer and product (`uq_wi`).
 */
class WishlistService
{
    public function __construct(private StorefrontCatalogService $catalog, private ProductRepository $products) {}

    /**
     * @return Collection<int, ProductCard>
     */
    public function cards(User $user, string $displayCurrency): Collection
    {
        return $this->catalog->cardsForIds($this->productIds($user), $displayCurrency);
    }

    /**
     * @return list<int>
     */
    public function productIds(User $user): array
    {
        return WishlistItem::query()->where('user_id', $user->id)->orderByDesc('id')->limit(200)->pluck('product_id')->map(static fn (mixed $id): int => (int) $id)->all();
    }

    public function contains(User $user, int $productId): bool
    {
        return WishlistItem::query()->where('user_id', $user->id)->where('product_id', $productId)->exists();
    }

    /**
     * Save a listed product by slug (idempotent).
     *
     * @throws ModelNotFoundException
     */
    public function add(User $user, string $slug): Product
    {
        $product = $this->products->storefrontDetail($slug) ?? throw (new ModelNotFoundException)->setModel(Product::class);

        $user->wishlistItems()->firstOrCreate(['product_id' => $product->id]);

        return $product;
    }

    /**
     * Remove a saved product by slug (idempotent; an unknown slug removes nothing).
     */
    public function remove(User $user, string $slug): void
    {
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->whereIn('product_id', Product::query()->select('id')->where('slug', $slug))
            ->delete();
    }
}
