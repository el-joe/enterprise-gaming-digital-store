<?php

namespace App\Domains\Suppliers\Adapters\Reloadly;

use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Services\ExactJson;

/**
 * Validates one Reloadly product (INTEGRATIONS.md 1.2) into a SupplierProductData. Values come
 * from ExactJson, so every number is still its literal text. A product without an id or name is
 * refused rather than guessed.
 */
final class ReloadlyProductParser
{
    /**
     * @param  array<array-key, mixed>  $product
     */
    public static function parse(array $product): ?SupplierProductData
    {
        $id = ExactJson::identifier($product['productId'] ?? null);
        $name = $product['productName'] ?? null;

        if ($id === null || ! is_string($name) || trim($name) === '') {
            return null;
        }

        $type = match (strtoupper((string) ($product['denominationType'] ?? ''))) {
            'FIXED' => DenominationType::Fixed,
            'RANGE' => DenominationType::Range,
            default => null,
        };

        $costMap = [];

        if ($type === DenominationType::Fixed && is_array($product['fixedRecipientToSenderDenominationsMap'] ?? null)) {
            foreach ($product['fixedRecipientToSenderDenominationsMap'] as $recipient => $sender) {
                $denomination = ExactJson::decimal((string) $recipient);
                $senderAmount = ExactJson::decimal($sender);

                if ($denomination !== null && $senderAmount !== null) {
                    $costMap[$denomination] = $senderAmount;
                }
            }
        }

        $rate = ExactJson::decimal($product['recipientCurrencyToSenderCurrencyExchangeRate'] ?? null);

        if ($type === DenominationType::Range && $rate !== null) {
            $costMap['rate'] = $rate;
        }

        $fixed = [];

        foreach (is_array($product['fixedRecipientDenominations'] ?? null) ? $product['fixedRecipientDenominations'] : [] as $value) {
            $decimal = ExactJson::decimal($value);

            if ($decimal !== null) {
                $fixed[] = $decimal;
            }
        }

        $country = is_array($product['country'] ?? null) ? ($product['country']['isoName'] ?? null) : null;

        return new SupplierProductData(
            externalId: $id,
            name: mb_substr(trim($name), 0, 255),
            brandName: self::text(is_array($product['brand'] ?? null) ? ($product['brand']['brandName'] ?? null) : null, 190),
            countryCode: is_string($country) && preg_match('/^[A-Za-z]{2}$/', $country) === 1 ? strtoupper($country) : null,
            categoryName: self::text(is_array($product['category'] ?? null) ? ($product['category']['name'] ?? null) : null, 190),
            denominationType: $type,
            currency: self::currency($product['recipientCurrencyCode'] ?? null),
            fixedDenominations: $fixed,
            minDenomination: ExactJson::decimal($product['minRecipientDenomination'] ?? null),
            maxDenomination: ExactJson::decimal($product['maxRecipientDenomination'] ?? $product['maxrecipientDenomination'] ?? null),
            senderCurrency: self::currency($product['senderCurrencyCode'] ?? null),
            costMap: $costMap,
            recipientToSenderRate: $rate,
            discountPct: ExactJson::decimal($product['discountPercentage'] ?? null),
            feeFixed: ExactJson::decimal($product['senderFee'] ?? null),
            feePct: ExactJson::decimal($product['senderFeePercentage'] ?? null),
            supportsPreorder: ($product['supportsPreOrder'] ?? null) === true,
            requiresUserId: is_array($product['additionalRequirements'] ?? null) && ($product['additionalRequirements']['userIdRequired'] ?? null) === true,
            availability: self::availability($product['status'] ?? null),
            raw: $product,
        );
    }

    /**
     * Product `status` values are not enumerated in the documentation (UNVERIFIED): only ACTIVE
     * and INACTIVE are interpreted; anything else stays unknown and is advisory.
     */
    private static function availability(mixed $status): SupplierAvailability
    {
        return match (is_string($status) ? strtoupper($status) : null) {
            'ACTIVE' => SupplierAvailability::Available,
            'INACTIVE' => SupplierAvailability::Unavailable,
            default => SupplierAvailability::Unknown,
        };
    }

    private static function currency(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z]{3}$/', $value) === 1 ? strtoupper($value) : null;
    }

    private static function text(mixed $value, int $limit): ?string
    {
        return is_string($value) && trim($value) !== '' ? mb_substr(trim($value), 0, $limit) : null;
    }
}
