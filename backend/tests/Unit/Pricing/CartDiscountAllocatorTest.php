<?php

use App\Domains\Pricing\DTOs\DiscountSlot;
use App\Domains\Pricing\Services\CartDiscountAllocator;
use App\Domains\Shared\ValueObjects\Money;

function usd(string $amount): Money
{
    return Money::of($amount, 'USD');
}

/**
 * @param  list<array{0: string, 1: string}>  $slots  weight and room
 * @return list<string>
 */
function allocateDiscount(string $discount, array $slots): array
{
    $allocations = (new CartDiscountAllocator)->allocate(usd($discount), array_map(static fn (array $slot): DiscountSlot => new DiscountSlot(usd($slot[0]), usd($slot[1])), $slots));

    return array_map(static fn (Money $money): string => $money->amount, $allocations);
}

function sumOf(array $amounts): string
{
    return array_reduce($amounts, static fn (string $carry, string $amount): string => bcadd($carry, $amount, 6), '0.000000');
}

it('splits proportionally to unit price', function (): void {
    expect(allocateDiscount('3', [['10', '10'], ['20', '20']]))->toBe(['1.000000', '2.000000']);
});

it('gives the rounding remainder to the earliest of equal units, summing exactly', function (): void {
    $allocated = allocateDiscount('1', [['10', '10'], ['10', '10'], ['10', '10']]);

    expect($allocated)->toBe(['0.333334', '0.333333', '0.333333'])
        ->and(sumOf($allocated))->toBe('1.000000');
});

it('gives remainders to the largest fractional parts first', function (): void {
    $allocated = allocateDiscount('0.000010', [['1', '1'], ['2', '2'], ['4', '4']]);

    expect($allocated)->toBe(['0.000001', '0.000003', '0.000006'])
        ->and(sumOf($allocated))->toBe('0.000010');
});

it('caps a unit at its margin room and moves the excess to units with room', function (): void {
    $allocated = allocateDiscount('6', [['10', '1'], ['10', '10']]);

    expect($allocated)->toBe(['1.000000', '5.000000']);
});

it('forfeits what no unit has room for', function (): void {
    $allocated = allocateDiscount('10', [['10', '1'], ['10', '2']]);

    expect($allocated)->toBe(['1.000000', '2.000000'])
        ->and(sumOf($allocated))->toBe('3.000000');
});

it('gives nothing to units without weight or room', function (): void {
    expect(allocateDiscount('2', [['10', '0'], ['0', '5'], ['10', '10']]))->toBe(['0.000000', '0.000000', '2.000000']);
});

it('is deterministic and exact for awkward amounts', function (): void {
    $slots = [['7.333333', '7'], ['0.010000', '0.01'], ['99.999999', '50'], ['13.131313', '13']];

    $first = allocateDiscount('17.777777', $slots);

    expect($first)->toBe(allocateDiscount('17.777777', $slots))
        ->and(sumOf($first))->toBe('17.777777');

    foreach ($first as $index => $amount) {
        expect(bccomp($amount, $slots[$index][1], 6))->toBeLessThanOrEqual(0);
    }
});

it('refuses slots in another currency', function (): void {
    (new CartDiscountAllocator)->allocate(usd('1'), [new DiscountSlot(Money::of('1', 'EUR'), Money::of('1', 'EUR'))]);
})->throws(InvalidArgumentException::class);

it('allocates in whole minor units when given a scale, rounding rooms down', function (): void {
    $allocated = array_map(
        static fn (Money $money): string => $money->amount,
        (new CartDiscountAllocator)->allocate(usd('1.00'), [new DiscountSlot(usd('15'), usd('5')), new DiscountSlot(usd('15'), usd('5')), new DiscountSlot(usd('15'), usd('0.339'))], 2),
    );

    expect($allocated)->toBe(['0.340000', '0.330000', '0.330000'])
        ->and(sumOf($allocated))->toBe('1.000000');
});
