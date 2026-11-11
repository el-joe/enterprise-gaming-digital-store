<?php

namespace App\Domains\Payments\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Currency\Models\Currency;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Payments\PaymentGatewayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('payment_gateways')]
#[Fillable(['key', 'name', 'adapter_key', 'is_active', 'mode', 'default_currency', 'settings', 'sort_order'])]
#[UseFactory(PaymentGatewayFactory::class)]
class PaymentGateway extends Model
{
    /** @use HasFactory<PaymentGatewayFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mode' => EnvironmentMode::class,
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The one currency this gateway charges (owner decision 1, OD-1): `default_currency`, seeded
     * EGP for Paymob and USD for every other gateway. It does not follow the display currency.
     */
    public function chargeCurrency(): string
    {
        return strtoupper((string) $this->default_currency);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function defaultCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency', 'code');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<OrderItemFinancial, $this>
     */
    public function orderItemFinancials(): HasMany
    {
        return $this->hasMany(OrderItemFinancial::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentGatewayCredential, $this>
     */
    public function paymentGatewayCredentials(): HasMany
    {
        return $this->hasMany(PaymentGatewayCredential::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentGatewayCurrency, $this>
     */
    public function paymentGatewayCurrencies(): HasMany
    {
        return $this->hasMany(PaymentGatewayCurrency::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentEligibilityRule, $this>
     */
    public function paymentEligibilityRules(): HasMany
    {
        return $this->hasMany(PaymentEligibilityRule::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentGatewayFeeRule, $this>
     */
    public function paymentGatewayFeeRules(): HasMany
    {
        return $this->hasMany(PaymentGatewayFeeRule::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_gateway_id');
    }

    /**
     * @return HasMany<PaymentWebhook, $this>
     */
    public function paymentWebhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class, 'payment_gateway_id');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function excludedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'payment_gateway_product_exclusions', 'payment_gateway_id', 'product_id');
    }
}
