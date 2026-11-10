<?php

namespace App\Domains\Orders\Services;

use App\Domains\Catalog\Enums\ProductCountryRestrictionMode;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\ProductCountryRestriction;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;

/**
 * Whether each cart line can be bought now, independent of price. Reasons are stable codes the
 * storefront can translate.
 */
class CartLineValidator
{
    /**
     * @return array<int, string> block reason keyed by cart item id; absent means purchasable
     */
    public function reasons(Cart $cart, ?string $country): array
    {
        $quantityByProduct = [];

        foreach ($cart->cartItems as $item) {
            $productId = $item->productVariant?->product_id;

            if ($productId !== null) {
                $quantityByProduct[$productId] = ($quantityByProduct[$productId] ?? 0) + $item->quantity;
            }
        }

        $reasons = [];

        foreach ($cart->cartItems as $item) {
            $reason = $this->reason($item, $country, $quantityByProduct);

            if ($reason !== null) {
                $reasons[$item->id] = $reason;
            }
        }

        return $reasons;
    }

    /**
     * @param  array<int, int>  $quantityByProduct
     */
    private function reason(CartItem $item, ?string $country, array $quantityByProduct): ?string
    {
        $variant = $item->productVariant;
        $product = $variant?->product;

        if ($variant === null || $product === null || ! $variant->is_active || $product->status !== ProductStatus::Active) {
            return 'unavailable';
        }

        if ($variant->is_custom_amount) {
            return 'custom_amount_unsupported';
        }

        if ($variant->availability === ProductVariantAvailability::OutOfStock) {
            return 'out_of_stock';
        }

        if ($quantityByProduct[$product->id] > $product->max_quantity_per_order) {
            return 'quantity_limit';
        }

        if ($country !== null && ! $this->soldIn($product->productCountryRestrictions, strtoupper($country))) {
            return 'not_sold_in_country';
        }

        return null;
    }

    /**
     * A deny row excludes a country; any allow row turns the list into an allow-list.
     *
     * @param  iterable<ProductCountryRestriction>  $restrictions
     */
    private function soldIn(iterable $restrictions, string $country): bool
    {
        $hasAllowList = false;
        $allowed = false;

        foreach ($restrictions as $restriction) {
            if ($restriction->mode === ProductCountryRestrictionMode::Deny && $restriction->country_code === $country) {
                return false;
            }

            if ($restriction->mode === ProductCountryRestrictionMode::Allow) {
                $hasAllowList = true;
                $allowed = $allowed || $restriction->country_code === $country;
            }
        }

        return ! $hasAllowList || $allowed;
    }
}
