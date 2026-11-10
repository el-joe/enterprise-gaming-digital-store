<?php

namespace App\Domains\Affiliates\Services;

use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Affiliates\Models\AffiliateReferral;
use App\Domains\Affiliates\Repositories\AffiliateRepository;
use App\Domains\Orders\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Affiliate clicks and attribution. A click is stored against a hash of the visitor's random
 * token (never the token itself). After an order commits, the last click from the same browser
 * inside the window is linked to it, unless the affiliate is the buyer. Attribution never runs
 * inside the placement transaction and never blocks an order.
 */
class AffiliateTracker
{
    public function __construct(private AffiliateRepository $affiliates) {}

    /**
     * Unknown or inactive codes are ignored without saying so.
     */
    public function recordClick(string $code, string $visitorToken, ?string $landingUrl): void
    {
        $affiliate = $this->affiliates->activeByCode(strtoupper(trim($code)));

        if ($affiliate === null) {
            return;
        }

        (new AffiliateReferral)->forceFill([
            'affiliate_id' => $affiliate->id,
            'visitor_token_hash' => self::tokenHash($visitorToken),
            'landing_url' => $landingUrl === null ? null : mb_substr($landingUrl, 0, 500),
            'status' => AffiliateReferralStatus::Clicked,
            'clicked_at' => now(),
        ])->save();
    }

    /**
     * Link a just-placed order to the browser's last affiliate click. Returns whether it did.
     */
    public function attribute(string $orderPublicId, ?string $visitorToken): bool
    {
        if ($visitorToken === null || $visitorToken === '') {
            return false;
        }

        $click = $this->affiliates->lastClick(self::tokenHash($visitorToken), (int) config('affiliates.attribution_days'));

        if ($click === null) {
            return false;
        }

        return DB::transaction(function () use ($orderPublicId, $click): bool {
            $order = Order::query()->where('public_id', $orderPublicId)->lockForUpdate()->first();
            $referral = $this->affiliates->lockReferral($click->id);

            if ($order === null || $referral === null || $order->affiliate_referral_id !== null || $referral->order_id !== null) {
                return false;
            }

            $owner = $click->affiliate?->user;

            if ($owner === null || $owner->id === $order->user_id || mb_strtolower($owner->email) === mb_strtolower((string) $order->contact_email)) {
                return false;
            }

            $referral->forceFill(['order_id' => $order->id])->save();
            Order::query()->whereKey($order->id)->update(['affiliate_referral_id' => $referral->id]);

            return true;
        });
    }

    public static function tokenHash(string $token): string
    {
        return hash_hmac('sha256', $token, (string) config('app.key'));
    }
}
