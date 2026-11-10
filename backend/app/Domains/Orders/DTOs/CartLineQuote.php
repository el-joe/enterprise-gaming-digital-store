<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Orders\Models\CartItem;
use App\Domains\Pricing\DTOs\PriceQuote;
use App\Domains\Shared\ValueObjects\Money;

/**
 * A priced cart line. When `blockReason` is set the line cannot be bought and carries no units.
 */
final readonly class CartLineQuote
{
    /**
     * @param  list<UnitPrice>  $units
     */
    public function __construct(
        public CartItem $item,
        public PriceQuote $quote,
        public ?string $blockReason,
        public array $units = [],
    ) {}

    public function purchasable(): bool
    {
        return $this->blockReason === null;
    }

    public function subtotalBase(string $currency): Money
    {
        return $this->sum(static fn (UnitPrice $unit): Money => $unit->grossBase, $currency);
    }

    public function discountBase(string $currency): Money
    {
        return $this->sum(static fn (UnitPrice $unit): Money => $unit->discountBase, $currency);
    }

    public function totalBase(string $currency): Money
    {
        return $this->sum(static fn (UnitPrice $unit): Money => $unit->netBase, $currency);
    }

    public function totalDisplay(string $currency): Money
    {
        return $this->sum(static fn (UnitPrice $unit): Money => $unit->netDisplay, $currency);
    }

    public function subtotalDisplay(string $currency): Money
    {
        return $this->quote->amount === null ? Money::zero($currency) : $this->quote->amount->multipliedBy($this->item->quantity);
    }

    /**
     * @param  callable(UnitPrice): Money  $amount
     */
    private function sum(callable $amount, string $currency): Money
    {
        return array_reduce($this->units, static fn (Money $carry, UnitPrice $unit): Money => $carry->plus($amount($unit)), Money::zero($currency));
    }
}
