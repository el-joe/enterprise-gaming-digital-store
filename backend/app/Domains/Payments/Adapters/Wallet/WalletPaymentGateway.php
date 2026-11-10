<?php

namespace App\Domains\Payments\Adapters\Wallet;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Adapters\AbstractPaymentGateway;
use App\Domains\Payments\Contracts\InternalLedgerGateway;
use App\Domains\Payments\DTOs\CreatePaymentRequest;
use App\Domains\Payments\DTOs\CreatePaymentResult;
use App\Domains\Payments\DTOs\GatewayCapabilities;
use App\Domains\Payments\DTOs\GatewayEvent;
use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\DTOs\RefundResult;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\DTOs\VerifiedWebhook;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Exceptions\GatewayCapabilityNotSupported;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway as GatewayAccount;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\Services\WalletLedger;
use App\Domains\Wallet\Services\WalletPaymentService;
use App\Domains\Wallet\Services\WalletRefundService;

/**
 * The wallet as a payment method (BD3: store credit from refunds and staff credits only; there is
 * no top-up). Internal, so it is verified by the ledger itself: `settle` debits the wallet and
 * marks the order paid through PaymentStateApplier in one transaction. It charges only the base
 * currency, the wallet's currency, and has no webhooks and no credentials.
 */
class WalletPaymentGateway extends AbstractPaymentGateway implements InternalLedgerGateway
{
    public function __construct(
        GatewayAccount $account,
        private WalletPaymentService $payments,
        private WalletRefundService $refunds,
        private WalletLedger $ledger,
        private ExchangeRateRegistry $rates,
    ) {
        parent::__construct($account);
    }

    public function capabilities(): GatewayCapabilities
    {
        return new GatewayCapabilities(refunds: true, partialRefunds: true, capture: false, webhooks: false, refundIdempotency: true);
    }

    public function supportsCurrency(string $currency): bool
    {
        return strtoupper($currency) === $this->rates->baseCurrency();
    }

    public function settle(Payment $payment): PaymentSession
    {
        return $this->payments->pay($payment, $this->key());
    }

    public function canCover(?int $userId, Money $amount): bool
    {
        if ($userId === null) {
            return false;
        }

        $user = User::query()->find($userId);

        if ($user === null || $user->email_verified_at === null || $user->status !== UserStatus::Active) {
            return false;
        }

        $balance = $this->ledger->spendableBalance($userId);

        return $balance !== null && $balance->currency === $amount->currency && $balance->compareTo($amount) >= 0;
    }

    /**
     * Wallet attempts are settled by `settle`; there is nothing to create elsewhere.
     */
    public function createPayment(CreatePaymentRequest $request): CreatePaymentResult
    {
        throw new GatewayCapabilityNotSupported($this->key(), 'create_payment');
    }

    /**
     * The ledger is the source of truth: the debit keyed for this attempt, or nothing.
     */
    public function fetchState(Payment $payment): VerifiedPaymentState
    {
        $transaction = $this->payments->recordedDebit($payment);

        if ($transaction === null) {
            return new VerifiedPaymentState(ProviderPaymentStatus::NotFound);
        }

        $reference = WalletLedger::reference($transaction);

        return new VerifiedPaymentState(
            ProviderPaymentStatus::Succeeded,
            Money::of(bcmul($transaction->amount, '-1', Money::SCALE), $payment->currency),
            providerCaptureId: $reference,
            fee: Money::zero($payment->currency),
        );
    }

    public function verifyWebhook(WebhookRequestData $request): VerifiedWebhook
    {
        throw new InvalidWebhookSignature('The wallet has no webhooks.');
    }

    public function parseWebhook(VerifiedWebhook $webhook): GatewayEvent
    {
        throw new GatewayCapabilityNotSupported($this->key(), 'webhooks');
    }

    public function refund(Refund $refund, Payment $payment, int|string $gatewayAmount): RefundResult
    {
        return $this->refunds->credit($refund);
    }

    public function fetchRefund(Refund $refund, Payment $payment): RefundResult
    {
        return $this->refunds->lookup($refund);
    }
}
