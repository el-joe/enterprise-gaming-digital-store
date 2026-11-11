<?php

namespace App\DataTables\Payments;

/**
 * Provider references shortened for display (`pi_3Qx…9Ad`): enough to match a provider
 * dashboard, not a full identifier in every screenshot.
 */
final class PaymentReference
{
    public static function short(?string $reference): ?string
    {
        if ($reference === null || $reference === '') {
            return null;
        }

        return mb_strlen($reference) <= 12 ? $reference : mb_substr($reference, 0, 7).'…'.mb_substr($reference, -3);
    }
}
