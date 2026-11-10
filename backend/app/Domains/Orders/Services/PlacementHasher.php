<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use Carbon\CarbonImmutable;

/**
 * Duplicate-placement fingerprint (DATABASE_DESIGN.md orders.placement_hash): an HMAC of the
 * buyer, the sorted cart contents and the coupon, in a 10-minute bucket. The current and the
 * previous bucket are both checked, so a double submit across a bucket edge is still caught.
 */
class PlacementHasher
{
    public const int BUCKET_SECONDS = 600;

    /**
     * @return array{current: string, previous: string}
     */
    public function hashes(string $buyer, Cart $cart, ?string $couponCode, ?CarbonImmutable $at = null): array
    {
        $bucket = intdiv(($at ?? CarbonImmutable::now())->getTimestamp(), self::BUCKET_SECONDS);
        $contents = $this->contents($cart, $couponCode);

        return [
            'current' => $this->hash($buyer, $contents, $bucket),
            'previous' => $this->hash($buyer, $contents, $bucket - 1),
        ];
    }

    /**
     * A signed-in customer is identified by account, a guest by normalized email.
     */
    public static function buyer(?int $userId, ?string $email): string
    {
        return $userId !== null ? 'user:'.$userId : 'email:'.mb_strtolower(trim((string) $email));
    }

    private function contents(Cart $cart, ?string $couponCode): string
    {
        $lines = $cart->cartItems
            ->map(static fn (CartItem $item): string => implode(':', [
                $item->product_variant_id,
                $item->quantity,
                $item->custom_amount ?? '-',
                hash('sha256', json_encode(self::sorted($item->delivery_values ?? []), JSON_THROW_ON_ERROR)),
            ]))
            ->sort()
            ->values()
            ->all();

        return json_encode(['lines' => $lines, 'coupon' => $couponCode === null ? null : strtoupper($couponCode)], JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function sorted(array $values): array
    {
        ksort($values);

        return $values;
    }

    private function hash(string $buyer, string $contents, int $bucket): string
    {
        return hash_hmac('sha256', $buyer.'|'.$contents.'|'.$bucket, (string) config('app.key'));
    }
}
