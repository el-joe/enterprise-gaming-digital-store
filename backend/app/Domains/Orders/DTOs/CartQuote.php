<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Currency\DTOs\ResolvedRate;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Shared\ValueObjects\Money;

/**
 * A whole cart priced in one display currency. Totals cover purchasable lines only; a cart is
 * ready for checkout when it has lines and every line is purchasable.
 */
final readonly class CartQuote
{
    /**
     * @param  list<CartLineQuote>  $lines
     */
    public function __construct(
        public Cart $cart,
        public array $lines,
        public string $baseCurrency,
        public string $displayCurrency,
        public ?ResolvedRate $displayRate,
        public Money $subtotalBase,
        public Money $discountBase,
        public Money $totalBase,
        public Money $subtotalDisplay,
        public Money $totalDisplay,
        public ?ResolvedCoupon $coupon,
        public ?string $couponError,
        public bool $couponLimitedByMargin,
    ) {}

    public function isReady(): bool
    {
        if ($this->lines === []) {
            return false;
        }

        foreach ($this->lines as $line) {
            if (! $line->purchasable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<CartItem>
     */
    public function purchasableItems(): array
    {
        return array_values(array_map(
            static fn (CartLineQuote $line): CartItem => $line->item,
            array_filter($this->lines, static fn (CartLineQuote $line): bool => $line->purchasable()),
        ));
    }

    public function discountDisplay(): Money
    {
        return $this->subtotalDisplay->minus($this->totalDisplay);
    }

    public function unitCount(): int
    {
        return array_sum(array_map(static fn (CartLineQuote $line): int => count($line->units), $this->lines));
    }
}
