<?php

use App\Domains\Shared\Exceptions\CurrencyMismatch;
use App\Domains\Shared\ValueObjects\CountryCode;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Shared\ValueObjects\Percentage;

it('normalizes amounts to six decimal places', function (mixed $input, string $expected): void {
    expect(Money::of($input, 'usd')->amount)->toBe($expected)
        ->and(Money::of($input, 'usd')->currency)->toBe('USD');
})->with([
    'integer' => [10, '10.000000'],
    'decimal string' => ['12.5', '12.500000'],
    'more than six places rounds half up' => ['1.0000005', '1.000001'],
    'just below half rounds down' => ['1.0000004', '1.000000'],
    'negative rounds away from zero' => ['-1.0000005', '-1.000001'],
    'zero never carries a sign' => ['-0.0000001', '0.000000'],
    'reloadly five place cost' => ['60553.3575', '60553.357500'],
]);

it('rejects floats and malformed amounts', function (mixed $value): void {
    expect(fn () => Money::of($value, 'USD'))->toThrow(InvalidArgumentException::class);
})->with([
    'float' => [1.5],
    'text' => ['abc'],
    'exponent' => ['1e3'],
    'empty' => [''],
    'null' => [null],
]);

it('rejects amounts beyond the decimal 20,6 column range', function (): void {
    expect(fn () => Money::of('123456789012345', 'USD'))->toThrow(InvalidArgumentException::class);
});

it('rejects invalid currency codes', function (): void {
    expect(fn () => Money::of('1', 'US'))->toThrow(InvalidArgumentException::class);
});

it('adds and subtracts without floating point drift', function (): void {
    $sum = Money::of('0.1', 'USD')->plus(Money::of('0.2', 'USD'));

    expect($sum->amount)->toBe('0.300000')
        ->and($sum->minus(Money::of('0.3', 'USD'))->isZero())->toBeTrue();
});

it('refuses to combine different currencies', function (string $operation): void {
    expect(fn () => Money::of('1', 'USD')->{$operation}(Money::of('1', 'EUR')))->toThrow(CurrencyMismatch::class);
})->with(['plus', 'minus', 'compareTo']);

it('multiplies and divides with half up rounding', function (): void {
    expect(Money::of('10', 'USD')->multipliedBy('1.2345678')->amount)->toBe('12.345678')
        ->and(Money::of('1', 'USD')->dividedBy('3')->amount)->toBe('0.333333')
        ->and(Money::of('2', 'USD')->dividedBy('3')->amount)->toBe('0.666667');
});

it('refuses to divide by zero or by a float', function (mixed $divisor): void {
    expect(fn () => Money::of('1', 'USD')->dividedBy($divisor))->toThrow(InvalidArgumentException::class);
})->with(['zero string' => ['0'], 'zero integer' => [0], 'float' => [0.5]]);

it('rounds to the currency exponent half up', function (string $amount, int $exponent, string $expected): void {
    expect(Money::of($amount, 'USD')->roundToCurrency($exponent)->amount)->toBe($expected);
})->with([
    'two digits up' => ['1.005', 2, '1.010000'],
    'two digits down' => ['1.004999', 2, '1.000000'],
    'no minor unit' => ['1499.5', 0, '1500.000000'],
    'three digits' => ['1.2345', 3, '1.235000'],
    'negative' => ['-1.005', 2, '-1.010000'],
]);

it('converts with an explicit rate only', function (): void {
    $converted = Money::of('100', 'USD')->convertedTo('egp', '48.123456789012');

    expect($converted->amount)->toBe('4812.345679')
        ->and($converted->currency)->toBe('EGP');
});

it('compares and reports sign', function (): void {
    expect(Money::of('2', 'USD')->compareTo(Money::of('1', 'USD')))->toBe(1)
        ->and(Money::of('1', 'USD')->equals(Money::of('1.000000', 'USD')))->toBeTrue()
        ->and(Money::of('1', 'USD')->equals(Money::of('1', 'EUR')))->toBeFalse()
        ->and(Money::of('-1', 'USD')->isNegative())->toBeTrue()
        ->and(Money::of('1', 'USD')->isPositive())->toBeTrue()
        ->and(Money::of('1', 'USD')->negated()->amount)->toBe('-1.000000');
});

it('serializes as strings, never JSON numbers', function (): void {
    expect(json_encode(Money::of('12.5', 'USD')))->toBe('{"amount":"12.500000","currency":"USD"}');
});

it('applies a percentage', function (): void {
    expect(Percentage::of('12.5')->applyTo(Money::of('80', 'USD'))->amount)->toBe('10.000000');
});

it('rejects a float percentage', function (): void {
    expect(fn () => Percentage::of(1.5))->toThrow(InvalidArgumentException::class);
});

it('validates country codes', function (): void {
    expect((string) CountryCode::of('eg'))->toBe('EG')
        ->and(fn () => CountryCode::of('EGY'))->toThrow(InvalidArgumentException::class);
});
