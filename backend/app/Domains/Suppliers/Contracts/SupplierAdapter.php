<?php

namespace App\Domains\Suppliers\Contracts;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\DTOs\AvailabilityQuery;
use App\Domains\Suppliers\DTOs\AvailabilityResult;
use App\Domains\Suppliers\DTOs\ConnectionResult;
use App\Domains\Suppliers\DTOs\DeliverablePayload;
use App\Domains\Suppliers\DTOs\ProductQuery;
use App\Domains\Suppliers\DTOs\SupplierBalanceData;
use App\Domains\Suppliers\DTOs\SupplierCancelResult;
use App\Domains\Suppliers\DTOs\SupplierCapabilities;
use App\Domains\Suppliers\DTOs\SupplierCountryData;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\DTOs\SupplierPricingData;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\DTOs\SupplierProductPage;
use App\Domains\Suppliers\DTOs\SupplierRefundResult;
use App\Domains\Suppliers\DTOs\SupplierTransactionPage;
use App\Domains\Suppliers\Exceptions\CapabilityNotSupported;
use App\Domains\Suppliers\Exceptions\DeliverableFetchFailed;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use Carbon\CarbonInterface;

/**
 * The supplier adapter contract (architecture 7.1). Order and checkout code only ever talk to
 * this interface, so a new supplier is a new class and a database row (rule 7).
 *
 * - createOrder, getOrderStatus and findOrderByReference never throw for supplier behaviour:
 *   they classify every response, including transport failures, into a SupplierOrderOutcome,
 *   default-deny (anything unclear is Ambiguous).
 * - Read methods throw SupplierRequestFailed. fetchDeliverables throws DeliverableFetchFailed
 *   without the body.
 * - Every unsupported operation throws CapabilityNotSupported and is declared `none`.
 */
interface SupplierAdapter
{
    public function key(): string;

    public function capabilities(): SupplierCapabilities;

    public function testConnection(): ConnectionResult;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getProducts(ProductQuery $query): SupplierProductPage;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getProductDetails(string $supplierProductId): SupplierProductData;

    /**
     * @return list<SupplierCountryData>
     *
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getCountries(): array;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getPricing(string $supplierProductId): SupplierPricingData;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function checkAvailability(AvailabilityQuery $query): AvailabilityResult;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getBalance(): SupplierBalanceData;

    /**
     * @throws CapabilityNotSupported
     */
    public function createOrder(SupplierOrderRequest $request): SupplierOrderOutcome;

    /**
     * @throws CapabilityNotSupported
     */
    public function getOrderStatus(string $supplierTransactionId): SupplierOrderOutcome;

    /**
     * @throws CapabilityNotSupported
     */
    public function findOrderByReference(string $clientReference, CarbonInterface $since): SupplierOrderOutcome;

    /**
     * @throws DeliverableFetchFailed|CapabilityNotSupported
     */
    public function fetchDeliverables(string $supplierTransactionId): DeliverablePayload;

    /**
     * @throws SupplierRequestFailed|CapabilityNotSupported
     */
    public function getTransactions(CarbonInterface $from, CarbonInterface $to, int $page): SupplierTransactionPage;

    /**
     * @throws CapabilityNotSupported
     */
    public function cancelOrder(string $supplierTransactionId): SupplierCancelResult;

    /**
     * @throws CapabilityNotSupported
     */
    public function refundOrder(string $supplierTransactionId, ?Money $amount): SupplierRefundResult;
}
