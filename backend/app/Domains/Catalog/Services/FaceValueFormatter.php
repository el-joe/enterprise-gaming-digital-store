<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\ExchangeRateRegistry;

/**
 * The face value of a gift card in its own currency (owner request, Storefront v2): the
 * variant's `denomination_value` in `denomination_currency`, never converted. Whole amounts
 * drop their decimals ("$20"); others use the currency's exponent ("€12.50"). A one-character
 * symbol is written before the amount, otherwise the code after it ("25 SAR"). A product with
 * several fixed amounts shows the range ("€10–100"), several currencies "From" the lowest, and a
 * custom amount its min–max.
 */
class FaceValueFormatter
{
    public function __construct(private ExchangeRateRegistry $registry) {}

    public function format(string $amount, string $currency): string
    {
        [$symbol, $number] = $this->parts($amount, $currency);

        return $symbol === null ? $number.' '.strtoupper($currency) : $symbol.$number;
    }

    public function variant(ProductVariant $variant): ?string
    {
        if ($variant->denomination_currency === null) {
            return null;
        }

        if ($variant->is_custom_amount) {
            return $variant->custom_min === null || $variant->custom_max === null ? null : $this->range((string) $variant->custom_min, (string) $variant->custom_max, $variant->denomination_currency);
        }

        return $variant->denomination_value === null ? null : $this->format((string) $variant->denomination_value, $variant->denomination_currency);
    }

    /**
     * The card face value from the listing aggregates (ProductRepository::withCardAggregates).
     *
     * @return array{label: string, currency: string, min: string, max: string, is_range: bool, is_custom: bool}|null
     */
    public function card(?string $min, ?string $max, ?string $currency, int $currencies, ?string $customMin, ?string $customMax, ?string $customCurrency): ?array
    {
        if ($min !== null && $max !== null && $currency !== null) {
            $isRange = bccomp($min, $max, 6) !== 0;
            $label = match (true) {
                $currencies > 1 => (string) __('storefront.price.from').' '.$this->format($min, $currency),
                $isRange => $this->range($min, $max, $currency),
                default => $this->format($min, $currency),
            };

            return ['label' => $label, 'currency' => $currency, 'min' => $this->plain($min, $currency), 'max' => $this->plain($max, $currency), 'is_range' => $isRange || $currencies > 1, 'is_custom' => false];
        }

        if ($customMin !== null && $customMax !== null && $customCurrency !== null) {
            return ['label' => $this->range($customMin, $customMax, $customCurrency), 'currency' => $customCurrency, 'min' => $this->plain($customMin, $customCurrency), 'max' => $this->plain($customMax, $customCurrency), 'is_range' => true, 'is_custom' => true];
        }

        return null;
    }

    public function range(string $min, string $max, string $currency): string
    {
        [$symbol, $low] = $this->parts($min, $currency);
        [, $high] = $this->parts($max, $currency);

        return $symbol === null ? $low.'–'.$high.' '.strtoupper($currency) : $symbol.$low.'–'.$high;
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function parts(string $amount, string $currency): array
    {
        $info = $this->registry->currency($currency);
        $symbol = (string) ($info['symbol'] ?? '');

        return [mb_strlen($symbol) === 1 ? $symbol : null, $this->plain($amount, $currency)];
    }

    private function plain(string $amount, string $currency): string
    {
        $exponent = (int) ($this->registry->currency($currency)['exponent'] ?? 2);
        $whole = bcadd($amount, '0', 0);

        return bccomp($amount, $whole, 6) === 0 ? $whole : bcadd($amount, '0', $exponent);
    }
}
