<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Pricing\DTOs\DiscountSlot;
use App\Domains\Shared\ValueObjects\Money;
use InvalidArgumentException;

/**
 * Spreads a cart-level discount over units (ADR-025). Shares are proportional to unit price and
 * computed in whole steps of 10^-scale (the base currency's minor unit at checkout), so the
 * allocations always sum exactly to the amount placed. A unit never takes more than its room; its excess is
 * redistributed over units that still have room, and whatever no unit can take is forfeited.
 * Rounding remainders go one step at a time to the largest fractional parts, ties to the
 * earliest slot, so the same cart always allocates the same way.
 */
class CartDiscountAllocator
{
    private const int WORKING_SCALE = 24;

    private string $factor = '1000000';

    /**
     * @param  list<DiscountSlot>  $slots  in a stable order
     * @param  int  $scale  decimal places of one allocation step; rooms are rounded down to it
     * @return list<Money> the discount per slot, same order and currency as the input
     */
    public function allocate(Money $discount, array $slots, int $scale = Money::SCALE): array
    {
        if ($scale < 0 || $scale > Money::SCALE) {
            throw new InvalidArgumentException("Allocation scale must be between 0 and 6, got {$scale}.");
        }

        $this->factor = bcpow('10', (string) $scale, 0);
        $currency = $discount->currency;
        $rooms = [];
        $weights = [];

        foreach ($slots as $index => $slot) {
            if ($slot->weight->currency !== $currency || $slot->room->currency !== $currency) {
                throw new InvalidArgumentException('Discount slots must share the discount currency.');
            }

            $weights[$index] = $this->positiveMicros($slot->weight);
            $rooms[$index] = $this->positiveMicros($slot->room);
        }

        $allocated = array_fill_keys(array_keys($slots), '0');
        $remaining = $this->min($this->positiveMicros($discount), $this->sum($rooms));
        $active = array_keys(array_filter($rooms, fn (string $room, int $index): bool => bccomp($room, '0', 0) === 1 && bccomp($weights[$index], '0', 0) === 1, ARRAY_FILTER_USE_BOTH));

        while (bccomp($remaining, '0', 0) === 1 && $active !== []) {
            $totalWeight = $this->sum(array_intersect_key($weights, array_flip($active)));
            $shares = [];
            $saturated = [];

            foreach ($active as $index) {
                $shares[$index] = bcdiv(bcmul($remaining, $weights[$index], self::WORKING_SCALE), $totalWeight, self::WORKING_SCALE);
                $room = bcsub($rooms[$index], $allocated[$index], 0);

                if (bccomp($shares[$index], $room, self::WORKING_SCALE) >= 0) {
                    $saturated[] = $index;
                }
            }

            if ($saturated !== []) {
                foreach ($saturated as $index) {
                    $room = bcsub($rooms[$index], $allocated[$index], 0);
                    $allocated[$index] = $rooms[$index];
                    $remaining = bcsub($remaining, $room, 0);
                }

                $active = array_values(array_diff($active, $saturated));

                continue;
            }

            $remaining = $this->distributeWithRemainders($shares, $allocated, $remaining);
        }

        return array_map(fn (string $micros): Money => $this->fromMicros($micros, $currency), array_values($allocated));
    }

    /**
     * Give each active slot the whole steps of its share, then one more step to the largest
     * fractional parts until the amount is placed. No slot here is at its room, so the extra
     * step always fits.
     *
     * @param  array<int, string>  $shares
     * @param  array<int, string>  $allocated
     */
    private function distributeWithRemainders(array $shares, array &$allocated, string $remaining): string
    {
        $fractions = [];

        foreach ($shares as $index => $share) {
            $whole = bcadd($share, '0', 0);
            $allocated[$index] = bcadd($allocated[$index], $whole, 0);
            $remaining = bcsub($remaining, $whole, 0);
            $fractions[$index] = bcsub($share, $whole, self::WORKING_SCALE);
        }

        uksort($fractions, static fn (int $left, int $right): int => bccomp($fractions[$right], $fractions[$left], self::WORKING_SCALE) ?: $left <=> $right);

        foreach (array_keys($fractions) as $index) {
            if (bccomp($remaining, '0', 0) !== 1) {
                break;
            }

            $allocated[$index] = bcadd($allocated[$index], '1', 0);
            $remaining = bcsub($remaining, '1', 0);
        }

        return $remaining;
    }

    private function positiveMicros(Money $money): string
    {
        return $money->isPositive() ? bcmul($money->amount, $this->factor, 0) : '0';
    }

    private function fromMicros(string $micros, string $currency): Money
    {
        return Money::of(bcdiv($micros, $this->factor, Money::SCALE), $currency);
    }

    /**
     * @param  array<int, string>  $values
     */
    private function sum(array $values): string
    {
        return array_reduce($values, static fn (string $carry, string $value): string => bcadd($carry, $value, 0), '0');
    }

    private function min(string $left, string $right): string
    {
        return bccomp($left, $right, 0) <= 0 ? $left : $right;
    }
}
