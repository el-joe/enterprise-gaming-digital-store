<?php

namespace App\Domains\Reviews\Repositories;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository
{
    public function __construct(private ListQueryApplier $lists) {}

    /**
     * A verified purchase: an item of the customer's own order with at least one delivered unit.
     */
    public function purchasedItem(int $userId, int $orderItemId): ?OrderItem
    {
        return $this->purchasedItems($userId)->whereKey($orderItemId)->first();
    }

    /**
     * Items the customer could still review.
     *
     * @return Collection<int, OrderItem>
     */
    public function reviewableItems(int $userId): Collection
    {
        return $this->purchasedItems($userId)
            ->whereNotExists(static fn ($query) => $query->selectRaw('1')->from('reviews')
                ->whereColumn('reviews.order_item_id', 'order_items.id')
                ->where('reviews.user_id', $userId))
            ->with(['order:id,public_id,order_number,completed_at', 'product:id,name,slug'])
            ->orderByDesc('order_items.id')
            ->limit(100)
            ->get();
    }

    public function mine(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return Review::query()->with('product:id,name,slug')->where('user_id', $userId)->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Approved reviews of a product, newest first (idx_r_product).
     */
    public function approvedFor(int $productId, int $page, int $perPage): LengthAwarePaginator
    {
        return Review::query()->with('user:id,name')
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::Approved->value)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Featured approved reviews of active products, newest first (idx_r_featured), for the home
     * page (M-A4).
     *
     * @return Collection<int, Review>
     */
    public function featuredApproved(int $limit): Collection
    {
        return Review::query()->with(['user:id,name', 'product:id,name,slug'])
            ->where('status', ReviewStatus::Approved->value)
            ->where('is_featured', true)
            ->whereHas('product', static fn ($product) => $product->where('status', ProductStatus::Active))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function adminPage(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            Review::query()->with(['product:id,name,slug', 'user:id,name,email']),
            $list,
            ['status' => 'exact', 'product_id' => 'exact', 'is_featured' => 'bool', 'rating' => 'exact'],
            ['id', 'created_at', 'rating'],
            ['title'],
            '-id',
        );
    }

    /**
     * @return array{avg: string, count: int}
     */
    public function approvedStats(int $productId): array
    {
        $row = Review::query()->where('product_id', $productId)->where('status', ReviewStatus::Approved->value)
            ->selectRaw('COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average')->first();

        return ['avg' => number_format((float) $row?->getAttribute('average'), 2, '.', ''), 'count' => (int) $row?->getAttribute('total')];
    }

    /**
     * @return Builder<OrderItem>
     */
    private function purchasedItems(int $userId): Builder
    {
        return OrderItem::query()
            ->whereHas('order', static fn (Builder $order) => $order->where('user_id', $userId)->whereNotNull('paid_at'))
            ->whereHas('orderItemUnits', static fn (Builder $unit) => $unit->where('status', OrderItemUnitStatus::Delivered->value));
    }
}
