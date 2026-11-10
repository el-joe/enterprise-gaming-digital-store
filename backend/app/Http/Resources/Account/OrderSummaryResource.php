<?php

namespace App\Http\Resources\Account;

use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A row of the customer's order history (M-A5). Amounts are Money JSON: the display total the
 * customer confirmed and, once a gateway was chosen, the amount charged in the payment currency.
 * Needs `orderItems.product.productImages.media` loaded (CustomerOrderRepository).
 *
 * @mixin Order
 */
class OrderSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);

        return [
            'public_id' => $this->public_id,
            'number' => $this->order_number,
            'status' => $this->status->value,
            'payment_status' => app(OrderStatusReader::class)->paymentStatus($this->resource),
            'is_final' => $this->status->isClosed(),
            'totals' => [
                'display' => $converter->present(Money::of($this->display_total, $this->display_currency)),
                'payment' => $this->payment_total === null || $this->payment_currency === null
                    ? null
                    : $converter->present(Money::of($this->payment_total, $this->payment_currency)),
            ],
            'item_count' => (int) $this->orderItems->sum('quantity'),
            'thumbnail_url' => $this->thumbnailUrl(),
            'created_at' => $this->created_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }

    /**
     * The first image of the first item's product, as an absolute URL (MediaUrls).
     */
    private function thumbnailUrl(): ?string
    {
        /** @var OrderItem|null $item */
        $item = $this->orderItems->first();

        /** @var ProductImage|null $image */
        $image = $item?->product?->productImages->first();

        return $image?->media === null ? null : app(MediaUrls::class)->url($image->media->disk, $image->media->path);
    }
}
