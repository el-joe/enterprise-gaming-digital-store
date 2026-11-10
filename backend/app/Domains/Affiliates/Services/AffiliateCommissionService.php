<?php

namespace App\Domains\Affiliates\Services;

use App\Domains\Affiliates\Enums\AffiliateCommissionStatus;
use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Repositories\AffiliateRepository;
use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Affiliate commission (M11). Accrued once, as `pending`, when an attributed order completes:
 * `commission_pct` of what the customer kept (the order's base total less succeeded refunds),
 * rounded to the base currency. A later refund recomputes it: a full refund reverses it, a
 * partial one reduces it. A commission already paid out is never changed automatically; finance
 * is alerted to claw it back. Under the lock order `orders`, then the commission row; the unique
 * `uq_ac` makes a second accrual impossible.
 */
class AffiliateCommissionService
{
    public function __construct(
        private AffiliateRepository $affiliates,
        private ExchangeRateRegistry $rates,
        private AuditLogger $audit,
    ) {}

    public function accrue(int $orderId): ?AffiliateCommission
    {
        return DB::transaction(function () use ($orderId): ?AffiliateCommission {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->affiliate_referral_id === null) {
                return null;
            }

            $referral = $this->affiliates->lockReferral($order->affiliate_referral_id);
            $existing = $this->affiliates->lockCommissionForOrder($order->id);

            if ($referral === null || $existing !== null) {
                return $existing;
            }

            $affiliate = Affiliate::withTrashed()->find($referral->affiliate_id);

            if ($affiliate === null || $affiliate->trashed() || $affiliate->status !== AffiliateStatus::Active) {
                $referral->forceFill(['status' => AffiliateReferralStatus::Rejected])->save();

                return null;
            }

            $amount = $this->amountFor($order, $affiliate);
            $referral->forceFill(['status' => AffiliateReferralStatus::Converted, 'converted_at' => now()])->save();

            if (! $amount->isPositive()) {
                return null;
            }

            $commission = (new AffiliateCommission)->forceFill([
                'affiliate_id' => $affiliate->id,
                'order_id' => $order->id,
                'amount_base' => $amount->amount,
                'status' => AffiliateCommissionStatus::Pending,
            ]);
            $commission->save();

            return $commission;
        }, 3);
    }

    /**
     * Recompute after a refund of the order.
     */
    public function adjustForRefund(int $orderId): ?AffiliateCommission
    {
        return DB::transaction(function () use ($orderId): ?AffiliateCommission {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            $commission = $this->affiliates->lockCommissionForOrder($order->id);

            if ($commission === null || $commission->status === AffiliateCommissionStatus::Reversed) {
                return $commission;
            }

            $affiliate = Affiliate::withTrashed()->findOrFail($commission->affiliate_id);
            $amount = $this->amountFor($order, $affiliate);

            if ($commission->status === AffiliateCommissionStatus::Paid) {
                if ($amount->compareTo(Money::of($commission->amount_base, $order->base_currency)) < 0) {
                    Log::alert('affiliates.paid_commission_refunded', ['order_id' => $order->id, 'commission_id' => $commission->id, 'due_base' => $amount->amount]);
                }

                return $commission;
            }

            $before = ['status' => $commission->status->value, 'amount_base' => $commission->amount_base];

            $commission->forceFill($amount->isPositive()
                ? ['amount_base' => $amount->amount]
                : ['status' => AffiliateCommissionStatus::Reversed])->save();

            $this->audit->record(new AuditEntry(
                action: 'affiliates.commission.'.($amount->isPositive() ? 'reduced' : 'reversed'),
                subject: $commission,
                before: $before,
                after: ['status' => $commission->status->value, 'amount_base' => $commission->amount_base],
                reason: 'Order refunded.',
            ));

            return $commission;
        }, 3);
    }

    public function approve(AffiliateCommission $commission): AffiliateCommission
    {
        return DB::transaction(function () use ($commission): AffiliateCommission {
            $locked = AffiliateCommission::query()->lockForUpdate()->findOrFail($commission->id);

            if ($locked->status !== AffiliateCommissionStatus::Pending) {
                throw new ApplicationException('Only a pending commission can be approved.', 'conflict', 409);
            }

            $locked->forceFill(['status' => AffiliateCommissionStatus::Approved])->save();
            $this->audit->record(new AuditEntry(action: 'affiliates.commission.approved', subject: $locked, before: ['status' => 'pending'], after: ['status' => 'approved']));

            return $locked;
        });
    }

    private function amountFor(Order $order, Affiliate $affiliate): Money
    {
        $base = $order->base_currency;
        $refunded = $order->primary_payment_id === null ? '0' : (string) Refund::query()
            ->where('payment_id', $order->primary_payment_id)
            ->where('status', RefundStatus::Succeeded->value)
            ->sum('amount_base');

        $kept = Money::of($order->total_base, $base)->minus(Money::of($refunded, $base));

        if (! $kept->isPositive()) {
            return Money::zero($base);
        }

        $exponent = $this->rates->currency($base)['exponent'] ?? 2;

        return $kept->multipliedBy((string) $affiliate->commission_pct)->dividedBy('100')->roundToCurrency($exponent);
    }
}
