<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\Enums\RiskAssessmentDecision;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\RiskAssessment;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scores a paid order before fulfilment (architecture section 15). Weights and windows come from
 * config/orders.php; the review and block thresholds from `settings.security.*`. Each signal
 * that fired is stored with its weight.
 */
class RiskEngine
{
    public function __construct(private SettingsService $settings) {}

    /**
     * @return array{score: int, decision: RiskAssessmentDecision, signals: list<array{code: string, weight: int}>}
     */
    public function score(Order $order): array
    {
        $config = config('orders.risk');
        $weights = $config['weights'];
        $fired = [];

        foreach ($this->signals($order, $config) as $code => $applies) {
            if ($applies) {
                $fired[] = ['code' => $code, 'weight' => (int) $weights[$code]];
            }
        }

        $score = array_sum(array_column($fired, 'weight'));
        $review = (int) $this->settings->get('security', 'risk_review_score', 40);
        $block = (int) $this->settings->get('security', 'risk_block_score', 80);

        $decision = match (true) {
            $score >= $block => RiskAssessmentDecision::Block,
            $score >= $review => RiskAssessmentDecision::Review,
            default => RiskAssessmentDecision::Pass,
        };

        return ['score' => $score, 'decision' => $decision, 'signals' => $fired];
    }

    /**
     * Store the assessment once per order; a second call returns the first result.
     */
    public function assess(Order $order): RiskAssessment
    {
        $existing = RiskAssessment::query()->where('order_id', $order->id)->first();

        if ($existing !== null) {
            return $existing;
        }

        $result = $this->score($order);

        $assessment = (new RiskAssessment)->forceFill([
            'order_id' => $order->id,
            'score' => $result['score'],
            'decision' => $result['decision'],
            'signals' => $result['signals'],
        ]);
        $assessment->save();

        $order->forceFill(['risk_score' => $result['score']])->save();

        return $assessment;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, bool>
     */
    private function signals(Order $order, array $config): array
    {
        $order->loadMissing(['user', 'primaryPayment']);
        $history = $this->paidHistory($order);
        $since = now()->subMinutes((int) $config['velocity_window_minutes']);
        $domain = strtolower((string) substr(strrchr($order->contact_email, '@') ?: '', 1));
        $payment = $order->primaryPayment;

        return [
            'guest_checkout' => $order->user_id === null,
            'new_account' => $order->user !== null && $order->user->created_at->greaterThan(now()->subHours((int) $config['new_account_hours'])),
            'first_order_high_value' => $history['count'] === 0
                && bccomp($order->total_base, (string) $this->settings->get('security', 'risk_first_order_amount_base', '100'), 6) >= 0,
            'value_above_history' => $history['count'] > 0
                && bccomp($order->total_base, bcmul((string) $history['average'], (string) $config['history_multiplier'], 6), 6) === 1,
            'country_mismatch' => $order->ip_country !== null && strtoupper($order->ip_country) !== $order->country_code,
            'card_country_mismatch' => $payment?->card_country !== null && strtoupper($payment->card_country) !== $order->country_code,
            'buyer_velocity' => $this->buyerOrders($order)->where('created_at', '>=', $since)->count() >= (int) $config['buyer_velocity_threshold'],
            'ip_velocity' => $order->ip_address !== null
                && Order::query()->where('ip_address', $order->ip_address)->whereKeyNot($order->id)->where('created_at', '>=', $since)->count() >= (int) $config['ip_velocity_threshold'],
            'card_velocity' => $payment?->card_fingerprint_hash !== null
                && Payment::query()->where('card_fingerprint_hash', $payment->card_fingerprint_hash)->where('order_id', '!=', $order->id)
                    ->where('created_at', '>=', now()->subHours(24))->distinct()->count('order_id') >= (int) $config['card_velocity_threshold'],
            'failed_payment_attempts' => Payment::query()->where('order_id', $order->id)->where('status', PaymentStatus::Failed)->count() >= (int) $config['failed_attempts_threshold'],
            'disposable_email' => $domain !== '' && in_array($domain, $config['disposable_email_domains'], true),
        ];
    }

    /**
     * @return array{count: int, average: string}
     */
    private function paidHistory(Order $order): array
    {
        $row = $this->buyerOrders($order)->whereNotNull('paid_at')->selectRaw('COUNT(*) AS paid_count, AVG(total_base) AS average_total')->first();

        return ['count' => (int) ($row?->paid_count ?? 0), 'average' => (string) ($row?->average_total ?? '0')];
    }

    /**
     * Other orders by the same buyer: by account, or by guest email (idx_o_user, idx_o_guest).
     *
     * @return Builder<Order>
     */
    private function buyerOrders(Order $order): Builder
    {
        return Order::query()
            ->whereKeyNot($order->id)
            ->when($order->user_id !== null,
                static fn ($query) => $query->where('user_id', $order->user_id),
                static fn ($query) => $query->whereNull('user_id')->where('guest_email', $order->guest_email));
    }
}
