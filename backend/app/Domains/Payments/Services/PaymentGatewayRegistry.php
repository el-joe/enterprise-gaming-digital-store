<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway as GatewayAccount;
use Illuminate\Contracts\Container\Container;
use LogicException;

/**
 * Resolves `payment_gateways.adapter_key` to an adapter for that gateway row. Built-in adapters
 * come from config/payments.php. Test doubles may be registered at runtime, never in production
 * (SECURITY.md section 6).
 */
class PaymentGatewayRegistry
{
    /**
     * @var array<string, class-string<PaymentGateway>>
     */
    private array $extra = [];

    public function __construct(private Container $container) {}

    /**
     * @param  class-string<PaymentGateway>  $class
     */
    public function register(string $key, string $class): void
    {
        if ($this->container->make('app')->isProduction()) {
            throw new LogicException('Payment adapters cannot be registered at runtime in production.');
        }

        $this->extra[$key] = $class;
    }

    public function has(string $adapterKey): bool
    {
        return $this->classFor($adapterKey) !== null;
    }

    /**
     * @throws GatewayNotConfigured
     */
    public function for(GatewayAccount $account): PaymentGateway
    {
        $class = $this->classFor($account->adapter_key) ?? throw new GatewayNotConfigured("No adapter is registered for {$account->adapter_key}.");

        return $this->container->make($class, ['account' => $account]);
    }

    /**
     * The adapter for the gateway account a payment attempt was made with.
     *
     * @throws GatewayNotConfigured
     */
    public function forPayment(Payment $payment): PaymentGateway
    {
        return $this->for(GatewayAccount::query()->findOrFail($payment->payment_gateway_id));
    }

    /**
     * @return class-string<PaymentGateway>|null
     */
    private function classFor(string $key): ?string
    {
        return $this->extra[$key] ?? config("payments.adapters.{$key}");
    }
}
