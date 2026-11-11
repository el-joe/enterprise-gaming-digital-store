<?php

namespace App\Http\Controllers\Web\Account\Concerns;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Http\Resources\Account\OrderSummaryResource;
use App\Http\Resources\Storefront\ProductCardResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Shared presentation of the account pages (M13): order rows as the order history JSON returns
 * them plus the item names the table shows, and product cards with translated names.
 */
trait PresentsAccountData
{
    /**
     * @param  iterable<int, Order>  $orders
     * @return list<array<string, mixed>>
     */
    protected function orderRows(iterable $orders, Request $request): array
    {
        $rows = [];

        foreach ($orders as $order) {
            /** @var OrderItem|null $first */
            $first = $order->orderItems->first();

            $rows[] = [
                ...(new OrderSummaryResource($order))->resolve($request),
                'first_item' => $first === null ? null : ['name' => $first->name_snapshot, 'quantity' => $first->quantity],
                'other_items' => max(0, $order->orderItems->count() - 1),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, ProductCard>  $cards
     * @return list<array<string, mixed>>
     */
    protected function productCards(Collection $cards, ContentTranslator $translator, Request $request): array
    {
        $products = $cards->map(static fn (ProductCard $card): Product => $card->product);
        $translator->preload([...$products, ...$products->map->gamingPlatform->filter(), ...$products->map->primaryCategory->filter()], ['name', 'short_description']);

        return ProductCardResource::collection($cards)->resolve($request);
    }
}
