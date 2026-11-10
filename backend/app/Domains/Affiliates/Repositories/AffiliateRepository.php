<?php

namespace App\Domains\Affiliates\Repositories;

use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Models\AffiliateReferral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AffiliateRepository
{
    public function activeByCode(string $code): ?Affiliate
    {
        return Affiliate::query()->where('code', $code)->where('status', AffiliateStatus::Active->value)->first();
    }

    /**
     * The last unconverted click from this browser inside the attribution window (index on
     * visitor_token_hash, clicked_at).
     */
    public function lastClick(string $tokenHash, int $days): ?AffiliateReferral
    {
        return AffiliateReferral::query()
            ->with('affiliate.user')
            ->where('visitor_token_hash', $tokenHash)
            ->where('clicked_at', '>=', now()->subDays($days))
            ->where('status', AffiliateReferralStatus::Clicked->value)
            ->whereNull('order_id')
            ->orderByDesc('clicked_at')
            ->orderByDesc('id')
            ->first();
    }

    public function lockReferral(int $referralId): ?AffiliateReferral
    {
        return AffiliateReferral::query()->whereKey($referralId)->lockForUpdate()->first();
    }

    public function lockCommissionForOrder(int $orderId): ?AffiliateCommission
    {
        return AffiliateCommission::query()->where('order_id', $orderId)->lockForUpdate()->first();
    }

    public function commissions(int $affiliateId, ?string $status, int $page, int $perPage): LengthAwarePaginator
    {
        return AffiliateCommission::query()
            ->with('order:id,public_id,order_number,status')
            ->where('affiliate_id', $affiliateId)
            ->when($status !== null, static fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
