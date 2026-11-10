<?php

namespace App\Domains\Suppliers\Adapters;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Contracts\SupplierAdapter;
use App\Domains\Suppliers\DTOs\AvailabilityQuery;
use App\Domains\Suppliers\DTOs\AvailabilityResult;
use App\Domains\Suppliers\DTOs\DeliverablePayload;
use App\Domains\Suppliers\DTOs\ProductQuery;
use App\Domains\Suppliers\DTOs\SupplierBalanceData;
use App\Domains\Suppliers\DTOs\SupplierCancelResult;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\DTOs\SupplierPricingData;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\DTOs\SupplierProductPage;
use App\Domains\Suppliers\DTOs\SupplierRefundResult;
use App\Domains\Suppliers\DTOs\SupplierTransactionPage;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Exceptions\CapabilityNotSupported;
use App\Domains\Suppliers\Models\Supplier;
use Carbon\CarbonInterface;

/**
 * Every optional operation throws CapabilityNotSupported until an adapter overrides it, so an
 * undeclared capability can never silently do nothing.
 */
abstract class AbstractSupplierAdapter implements SupplierAdapter
{
    public function __construct(protected Supplier $supplier) {}

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    public function getProducts(ProductQuery $query): SupplierProductPage
    {
        throw $this->unsupported(SupplierCapability::ListProducts);
    }

    public function getProductDetails(string $supplierProductId): SupplierProductData
    {
        throw $this->unsupported(SupplierCapability::ProductDetails);
    }

    public function getCountries(): array
    {
        throw $this->unsupported(SupplierCapability::Countries);
    }

    public function getPricing(string $supplierProductId): SupplierPricingData
    {
        throw $this->unsupported(SupplierCapability::Pricing);
    }

    public function checkAvailability(AvailabilityQuery $query): AvailabilityResult
    {
        throw $this->unsupported(SupplierCapability::Availability);
    }

    public function getBalance(): SupplierBalanceData
    {
        throw $this->unsupported(SupplierCapability::Balance);
    }

    public function createOrder(SupplierOrderRequest $request): SupplierOrderOutcome
    {
        throw $this->unsupported(SupplierCapability::CreateOrder);
    }

    public function getOrderStatus(string $supplierTransactionId): SupplierOrderOutcome
    {
        throw $this->unsupported(SupplierCapability::OrderStatus);
    }

    public function findOrderByReference(string $clientReference, CarbonInterface $since): SupplierOrderOutcome
    {
        throw $this->unsupported(SupplierCapability::LookupByReference);
    }

    public function fetchDeliverables(string $supplierTransactionId): DeliverablePayload
    {
        throw $this->unsupported(SupplierCapability::FetchDeliverables);
    }

    public function getTransactions(CarbonInterface $from, CarbonInterface $to, int $page): SupplierTransactionPage
    {
        throw $this->unsupported(SupplierCapability::TransactionHistory);
    }

    public function cancelOrder(string $supplierTransactionId): SupplierCancelResult
    {
        throw $this->unsupported(SupplierCapability::CancelOrder);
    }

    public function refundOrder(string $supplierTransactionId, ?Money $amount): SupplierRefundResult
    {
        throw $this->unsupported(SupplierCapability::RefundOrder);
    }

    protected function unsupported(SupplierCapability $capability): CapabilityNotSupported
    {
        return new CapabilityNotSupported($this->key(), $capability);
    }
}
