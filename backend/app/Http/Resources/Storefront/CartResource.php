<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Orders\DTOs\CartLineQuote;
use App\Domains\Orders\DTOs\CartQuote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CartQuote $resource
 */
class CartResource extends JsonResource
{
    /**
     * Amounts are in the display currency. Cost, floor and margin stay on the server. Delivery
     * values are shown only to the cart's owner, who entered them.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $quote = $this->resource;
        $converter = app(CurrencyConverter::class);
        $currency = $quote->displayCurrency;

        return [
            'public_id' => $quote->cart->public_id,
            'currency' => $currency,
            'country' => $quote->cart->country_code,
            'items' => array_map(static fn (CartLineQuote $line): array => [
                'id' => $line->item->id,
                'variant' => [
                    'public_id' => $line->item->productVariant?->public_id,
                    'title' => $line->item->productVariant?->title,
                    'sku' => $line->item->productVariant?->sku,
                ],
                'product' => [
                    'name' => $line->item->productVariant?->product?->name,
                    'slug' => $line->item->productVariant?->product?->slug,
                    'delivery_type' => $line->item->productVariant?->product?->delivery_type->value,
                ],
                'quantity' => $line->item->quantity,
                'delivery_values' => (object) ($line->item->delivery_values ?? []),
                'purchasable' => $line->purchasable(),
                'block_reason' => $line->blockReason,
                'unit_price' => $line->purchasable() ? $converter->present($line->quote->amount) : null,
                'subtotal' => $line->purchasable() ? $converter->present($line->subtotalDisplay($currency)) : null,
                'total' => $line->purchasable() ? $converter->present($line->totalDisplay($currency)) : null,
            ], $quote->lines),
            'coupon' => $quote->cart->coupon_id === null ? null : [
                'code' => $quote->coupon?->code ?? $quote->cart->coupon?->code,
                'applied' => $quote->coupon !== null,
                'error' => $quote->couponError,
                'limited_by_margin' => $quote->couponLimitedByMargin,
            ],
            'totals' => [
                'subtotal' => $converter->present($quote->subtotalDisplay),
                'discount' => $converter->present($quote->discountDisplay()),
                'total' => $converter->present($quote->totalDisplay),
            ],
            'price_may_vary' => $quote->displayRate?->isStale ?? false,
            'checkout_ready' => $quote->isReady() && ! ($quote->displayRate?->isStale ?? false),
        ];
    }
}
