<?php

namespace App\Domains\Orders\Services;

use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Models\Payment;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Actual profitability per order item (architecture 11.5, spec Phase 8), in the base currency,
 * written once when the order settles and corrected on refund.
 *
 * - Revenue: the sum of the units' net base price from the order snapshot.
 * - Supplier cost: the supplier-reported cost of each delivered unit's succeeded attempt, else
 *   the cost estimated at placement. A unit that did not deliver carries no supplier cost: a
 *   supplier `FAILED` attempt (`cost_reversal_pending`) is expected to be reversed, so its cost
 *   is not booked as an expense; the flag stays on the attempt for supplier reconciliation.
 * - Gateway fee: the provider's figure when it reported one, otherwise the fee-rule estimate
 *   recorded on the payment, allocated over items by revenue.
 * - FX cost: `pricing.conversion_cost_pct` of revenue when the charge was not in the base currency.
 * - Refunded: the net base price of the item's refunded units.
 * - Net = revenue - refunded - supplier cost - gateway fee - FX cost - other costs.
 */
class OrderFinancialsWriter
{
    public function __construct(private CurrencyConverter $converter, private SettingsService $settings) {}

    public function recognize(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        if ($order->primary_payment_id === null) {
            return;
        }

        if (OrderItemFinancial::query()->where('order_id', $orderId)->exists()) {
            $this->recordRefunds($orderId);

            return;
        }

        $payment = Payment::query()->findOrFail($order->primary_payment_id);
        $items = OrderItem::query()->where('order_id', $orderId)->with('orderItemUnits')->orderBy('id')->get();
        $base = $order->base_currency;

        $revenues = $items->mapWithKeys(fn (OrderItem $item): array => [$item->id => $this->revenue($item, $base)]);
        $totalRevenue = $revenues->reduce(static fn (Money $sum, Money $revenue): Money => $sum->plus($revenue), Money::zero($base));
        $fee = $this->feeInBase($payment, $base);
        $fees = $this->allocate($fee, $revenues, $totalRevenue);
        $conversionPct = $payment->currency === $base ? '0' : (string) $this->settings->get('pricing', 'conversion_cost_pct', '0');

        $transactions = SupplierTransaction::query()
            ->whereIn('order_item_unit_id', $items->flatMap->orderItemUnits->pluck('id')->all())
            ->where('status', SupplierTransactionStatus::Succeeded)
            ->get()
            ->keyBy('order_item_unit_id');

        foreach ($items as $item) {
            $revenue = $revenues[$item->id];
            [$supplierCost, $supplierId] = $this->supplierCost($item, $transactions, $base);
            $fx = $revenue->multipliedBy(bcdiv($conversionPct, '100', 12));
            $other = Money::zero($base);
            $refunded = $this->refunded($item, $base);

            (new OrderItemFinancial)->forceFill([
                'order_item_id' => $item->id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'supplier_id' => $supplierId,
                'payment_gateway_id' => $payment->payment_gateway_id,
                'country_code' => $order->country_code,
                'currency' => $payment->currency,
                'revenue_base' => $revenue->amount,
                'supplier_cost_base' => $supplierCost->amount,
                'gateway_fee_base' => $fees[$item->id]->amount,
                'gateway_fee_is_estimate' => $fee === null || $payment->fee_is_estimate,
                'fx_cost_base' => $fx->amount,
                'other_cost_base' => $other->amount,
                'refunded_base' => $refunded->amount,
                'gross_profit_base' => $revenue->minus($supplierCost)->amount,
                'net_profit_base' => $revenue->minus($refunded)->minus($supplierCost)->minus($fees[$item->id])->minus($fx)->minus($other)->amount,
                'recognized_at' => now(),
            ])->save();
        }
    }

    /**
     * Correct `refunded_base` and net profit after a refund. Recognised costs are not recomputed.
     */
    public function recordRefunds(int $orderId): void
    {
        $rows = OrderItemFinancial::query()->where('order_id', $orderId)->with('orderItem.orderItemUnits')->get();
        $currency = (string) Order::query()->whereKey($orderId)->value('base_currency');

        foreach ($rows as $row) {
            $refunded = $this->refunded($row->orderItem, $currency);
            $net = Money::of($row->revenue_base, $currency)
                ->minus($refunded)
                ->minus(Money::of($row->supplier_cost_base, $currency))
                ->minus(Money::of($row->gateway_fee_base, $currency))
                ->minus(Money::of($row->fx_cost_base, $currency))
                ->minus(Money::of($row->other_cost_base, $currency));

            $row->forceFill(['refunded_base' => $refunded->amount, 'net_profit_base' => $net->amount])->save();
        }
    }

    private function revenue(OrderItem $item, string $base): Money
    {
        $sum = Money::zero($base);

        foreach ($item->pricing_breakdown['units'] ?? [] as $entry) {
            $sum = $sum->plus(Money::of((string) $entry['net_base'], $sum->currency));
        }

        return $sum;
    }

    private function refunded(OrderItem $item, string $base): Money
    {
        $refundedIds = $item->orderItemUnits->filter(static fn (OrderItemUnit $unit): bool => $unit->status === OrderItemUnitStatus::Refunded)->pluck('public_id')->all();
        $sum = Money::zero($base);

        foreach ($item->pricing_breakdown['units'] ?? [] as $entry) {
            if (in_array($entry['unit_public_id'] ?? null, $refundedIds, true)) {
                $sum = $sum->plus(Money::of((string) $entry['net_base'], $base));
            }
        }

        return $sum;
    }

    /**
     * @param  Collection<int, SupplierTransaction>  $transactions  succeeded attempts keyed by unit id
     * @return array{0: Money, 1: ?int}
     */
    private function supplierCost(OrderItem $item, Collection $transactions, string $base): array
    {
        $cost = Money::zero($base);
        $supplierId = null;

        foreach ($item->orderItemUnits as $unit) {
            if ($unit->delivered_at === null) {
                continue;
            }

            $transaction = $transactions->get($unit->id);
            $supplierId ??= $transaction?->supplier_id;
            $cost = $cost->plus($this->unitCost($item, $transaction, $base));
        }

        return [$cost, $supplierId];
    }

    private function unitCost(OrderItem $item, ?SupplierTransaction $transaction, string $base): Money
    {
        if ($transaction !== null && $transaction->actual_cost !== null && $transaction->actual_cost_currency !== null) {
            try {
                return $this->converter->toBase(Money::of($transaction->actual_cost, $transaction->actual_cost_currency), withoutMarkup: true);
            } catch (ExchangeRateMissing) {
                Log::warning('orders.financials_cost_rate_missing', ['supplier_transaction_id' => $transaction->id]);
            }
        }

        return Money::of($item->estimated_unit_cost_base ?? '0', $base);
    }

    private function feeInBase(Payment $payment, string $base): ?Money
    {
        if ($payment->fee_amount === null || $payment->fee_currency === null) {
            return null;
        }

        $fee = Money::of($payment->fee_amount, $payment->fee_currency);

        if ($fee->currency === $base) {
            return $fee;
        }

        if ($fee->currency === $payment->currency && bccomp($payment->rate, '0', 12) === 1) {
            return Money::of($fee->dividedBy($payment->rate)->amount, $base);
        }

        try {
            return $this->converter->toBase($fee, withoutMarkup: true);
        } catch (ExchangeRateMissing) {
            return null;
        }
    }

    /**
     * Split the fee over items by revenue; the last item takes the rounding remainder.
     *
     * @param  Collection<int, Money>  $revenues
     * @return array<int, Money>
     */
    private function allocate(?Money $fee, Collection $revenues, Money $totalRevenue): array
    {
        $base = $totalRevenue->currency;
        $allocated = [];
        $remaining = $fee ?? Money::zero($base);
        $keys = $revenues->keys()->all();

        foreach ($keys as $index => $itemId) {
            if ($fee === null || $index === array_key_last($keys) || $totalRevenue->isZero()) {
                $allocated[$itemId] = $index === array_key_last($keys) ? $remaining : Money::zero($base);

                continue;
            }

            $share = $fee->multipliedBy(bcdiv($revenues[$itemId]->amount, $totalRevenue->amount, 12));
            $allocated[$itemId] = $share;
            $remaining = $remaining->minus($share);
        }

        return $allocated;
    }
}
