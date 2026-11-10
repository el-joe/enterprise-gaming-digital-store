<?php

namespace App\Domains\Suppliers\Adapters\Reloadly;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Adapters\AbstractSupplierAdapter;
use App\Domains\Suppliers\DTOs\AvailabilityQuery;
use App\Domains\Suppliers\DTOs\AvailabilityResult;
use App\Domains\Suppliers\DTOs\ConnectionResult;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use App\Domains\Suppliers\DTOs\DeliverablePayload;
use App\Domains\Suppliers\DTOs\ProductQuery;
use App\Domains\Suppliers\DTOs\SupplierBalanceData;
use App\Domains\Suppliers\DTOs\SupplierCapabilities;
use App\Domains\Suppliers\DTOs\SupplierCountryData;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\DTOs\SupplierPricingData;
use App\Domains\Suppliers\DTOs\SupplierProductData;
use App\Domains\Suppliers\DTOs\SupplierProductPage;
use App\Domains\Suppliers\DTOs\SupplierTransactionPage;
use App\Domains\Suppliers\DTOs\SupplierTransactionRecord;
use App\Domains\Suppliers\Enums\CapabilityLevel;
use App\Domains\Suppliers\Enums\SupplierCapability as Cap;
use App\Domains\Suppliers\Exceptions\DeliverableFetchFailed;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\ExactJson;
use App\Domains\Suppliers\Services\SupplierApiLogger;
use App\Domains\Suppliers\Services\SupplierCostCalculator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

/**
 * Reloadly Gift Cards (INTEGRATIONS.md section 1). Status: built and tested with Http::fake only;
 * awaiting sandbox verification. Never called against the live API.
 *
 * Implemented only what the official documentation confirms. Cancel and refund do not exist and
 * are declared `none`; client idempotency and webhooks are `unverified` and never relied on.
 * One unit per request (`quantity = 1`), `customIdentifier` = our client reference, and
 * `recipientEmail` is never sent, so Reloadly emails no code outside our control.
 */
class ReloadlyGiftCardAdapter extends AbstractSupplierAdapter
{
    public const string KEY = 'reloadly_giftcards';

    /** Response keys that may carry code material; redacted from every log. */
    private const array SENSITIVE_KEYS = ['cardNumber', 'pinCode', 'redemptionUrl', 'access_token', 'client_secret'];

    private ReloadlyClient $client;

    public function __construct(
        Supplier $supplier,
        SupplierApiLogger $logger,
        private SettingsService $settings,
        private SupplierCostCalculator $costs,
    ) {
        parent::__construct($supplier);

        $this->client = new ReloadlyClient($supplier, $logger, self::SENSITIVE_KEYS);
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function capabilities(): SupplierCapabilities
    {
        $full = CapabilityLevel::Full;
        $cap = config('suppliers.reloadly.default_per_request_cap');

        return new SupplierCapabilities(
            levels: [
                Cap::ListProducts->value => $full,
                Cap::ProductDetails->value => $full,
                Cap::Countries->value => $full,
                Cap::Pricing->value => $full,
                Cap::Balance->value => $full,
                Cap::CreateOrder->value => $full,
                Cap::OrderStatus->value => $full,
                Cap::FetchDeliverables->value => $full,
                Cap::TransactionHistory->value => $full,
                Cap::LookupByReference->value => $full,
                Cap::Availability->value => CapabilityLevel::Advisory,
                Cap::CancelOrder->value => CapabilityLevel::None,
                Cap::RefundOrder->value => CapabilityLevel::None,
                Cap::ClientIdempotency->value => CapabilityLevel::Unverified,
                Cap::Webhooks->value => CapabilityLevel::Unverified,
                Cap::PerRequestAmountCap->value => $full,
            ],
            supportedDeliveryTypes: [DeliveryType::InstantCode, DeliveryType::ApiDelivery],
            sensitiveResponsePaths: self::SENSITIVE_KEYS,
            defaultPerRequestCap: Money::of($cap['amount'], $cap['currency']),
        );
    }

    public function testConnection(): ConnectionResult
    {
        $started = hrtime(true);

        try {
            $response = $this->client->send('test_connection', 'GET', 'accounts/balance');
        } catch (ConnectionException|SupplierNotConfigured $exception) {
            return new ConnectionResult(false, error: $exception instanceof SupplierNotConfigured ? $exception->getMessage() : 'Connection failed.');
        }

        return new ConnectionResult(
            $response->successful(),
            (int) ((hrtime(true) - $started) / 1_000_000),
            $response->successful() ? null : "HTTP {$response->status()}",
        );
    }

    public function getProducts(ProductQuery $query): SupplierProductPage
    {
        $params = ['page' => $query->page, 'size' => $query->size, 'includeRange' => 'true', 'includeFixed' => 'true'];

        if ($query->countryCode !== null) {
            $params['countryCode'] = $query->countryCode;
        }

        $payload = $this->read('products.list', 'products', $params);
        [$rows, $isLast] = $this->page($payload, $query->page);

        return new SupplierProductPage(
            array_values(array_filter(array_map(static fn (mixed $row): ?SupplierProductData => is_array($row) ? ReloadlyProductParser::parse($row) : null, $rows))),
            $query->page,
            $isLast,
        );
    }

    public function getProductDetails(string $supplierProductId): SupplierProductData
    {
        $payload = $this->read('products.show', 'products/'.$this->pathId($supplierProductId));

        return ReloadlyProductParser::parse($payload) ?? throw new SupplierRequestFailed('Reloadly returned an unrecognised product.');
    }

    public function getCountries(): array
    {
        $payload = $this->read('countries.list', 'countries');
        $countries = [];

        foreach (array_is_list($payload) ? $payload : [] as $row) {
            $code = is_array($row) ? ($row['isoName'] ?? null) : null;

            if (is_string($code) && preg_match('/^[A-Za-z]{2}$/', $code) === 1) {
                $countries[] = new SupplierCountryData(strtoupper($code), (string) ($row['name'] ?? $code), is_string($row['currencyCode'] ?? null) ? $row['currencyCode'] : null);
            }
        }

        return $countries;
    }

    public function getPricing(string $supplierProductId): SupplierPricingData
    {
        $product = $this->getProductDetails($supplierProductId);

        return new SupplierPricingData($product, $this->costs->forFixedDenominations($product));
    }

    /**
     * Advisory only: Reloadly has no stock endpoint, just the product status.
     */
    public function checkAvailability(AvailabilityQuery $query): AvailabilityResult
    {
        return new AvailabilityResult($this->getProductDetails($query->supplierProductId)->availability, advisory: true);
    }

    public function getBalance(): SupplierBalanceData
    {
        $payload = $this->read('balance', 'accounts/balance');
        $amount = ExactJson::decimal($payload['balance'] ?? null);
        $currency = $payload['currencyCode'] ?? null;

        if ($amount === null || ! is_string($currency) || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new SupplierRequestFailed('Reloadly returned an unrecognised balance.');
        }

        return new SupplierBalanceData(Money::of($amount, $currency), CarbonImmutable::now());
    }

    /**
     * POST /orders for one unit. Never retried here except after a 401 (one token refresh), which
     * the documentation states means the order did not run.
     */
    public function createOrder(SupplierOrderRequest $request): SupplierOrderOutcome
    {
        $productId = preg_match('/^\d{1,18}$/', $request->supplierProductId) === 1 ? $request->supplierProductId : null;
        $unitPrice = ExactJson::decimal($request->unitPrice);

        if ($productId === null || $unitPrice === null || bccomp($unitPrice, '0', 6) !== 1) {
            return SupplierOrderOutcome::rejected('invalid_mapping', notSent: true);
        }

        $body = [
            'productId' => (int) $productId,
            'quantity' => 1,
            'unitPrice' => '__UNIT_PRICE__',
            'customIdentifier' => $request->clientReference,
            'senderName' => mb_substr((string) $this->settings->get('suppliers', 'sender_name', config('app.name')), 0, 64),
            'preOrder' => false,
        ];

        if (isset($request->deliveryValues['user_id']) && $request->deliveryValues['user_id'] !== '') {
            $body['productAdditionalRequirements'] = ['userId' => $request->deliveryValues['user_id']];
        }

        // unitPrice is a JSON number in the documented schema; it is written from the decimal text
        // so no float ever represents it.
        $json = str_replace('"__UNIT_PRICE__"', $unitPrice, json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $logged = [...$body, 'unitPrice' => $unitPrice];

        try {
            $response = $this->client->send('orders.create', 'POST', 'orders', jsonBody: $json, loggedBody: $logged, supplierTransactionId: $request->supplierTransactionId);
        } catch (SupplierNotConfigured) {
            return SupplierOrderOutcome::rejected('not_sent_not_configured', notSent: true);
        } catch (ConnectionException $exception) {
            return ReloadlyOutcomeClassifier::fromTransport($exception);
        } catch (Throwable $exception) {
            report($exception);

            return SupplierOrderOutcome::ambiguous('unexpected_exception');
        }

        return ReloadlyOutcomeClassifier::fromOrderResponse($response);
    }

    public function getOrderStatus(string $supplierTransactionId): SupplierOrderOutcome
    {
        try {
            $response = $this->client->send('transactions.show', 'GET', 'reports/transactions/'.$this->pathId($supplierTransactionId));
        } catch (ConnectionException|SupplierNotConfigured) {
            return SupplierOrderOutcome::ambiguous('lookup_unavailable');
        }

        if (! $response->successful()) {
            return SupplierOrderOutcome::ambiguous('lookup_failed', httpStatus: $response->status());
        }

        $payload = ReloadlyClient::decode($response);

        return $payload === null ? SupplierOrderOutcome::ambiguous('malformed_body') : ReloadlyOutcomeClassifier::fromTransaction($payload, $response->status());
    }

    /**
     * GET /reports/transactions filtered by customIdentifier (INTEGRATIONS.md finding 4). Only a
     * well-formed, empty page is NotFound; an unreadable answer is Ambiguous, and more than one
     * transaction with our reference is Ambiguous and flagged, never chosen between.
     */
    public function findOrderByReference(string $clientReference, CarbonInterface $since): SupplierOrderOutcome
    {
        try {
            $response = $this->client->send('transactions.lookup', 'GET', 'reports/transactions', ['customIdentifier' => $clientReference, 'page' => 1, 'size' => 10]);
        } catch (ConnectionException|SupplierNotConfigured) {
            return SupplierOrderOutcome::ambiguous('lookup_unavailable');
        }

        $payload = $response->successful() ? ReloadlyClient::decode($response) : null;

        if ($payload === null) {
            return SupplierOrderOutcome::ambiguous('lookup_failed', httpStatus: $response->status());
        }

        $rows = array_is_list($payload) ? $payload : ($payload['content'] ?? null);

        if (! is_array($rows)) {
            return SupplierOrderOutcome::ambiguous('malformed_body', httpStatus: $response->status());
        }

        $matches = array_values(array_filter($rows, static fn (mixed $row): bool => is_array($row) && ($row['customIdentifier'] ?? null) === $clientReference));

        return match (count($matches)) {
            0 => SupplierOrderOutcome::notFound(),
            1 => ReloadlyOutcomeClassifier::fromTransaction($matches[0], $response->status()),
            default => SupplierOrderOutcome::ambiguous('multiple_supplier_transactions'),
        };
    }

    /**
     * GET /orders/transactions/{id}/cards. The body is decoded with every number kept as text
     * (cardNumber is typed as an integer upstream) and is never logged or put in an exception.
     */
    public function fetchDeliverables(string $supplierTransactionId): DeliverablePayload
    {
        try {
            $response = $this->client->send(
                'orders.cards',
                'GET',
                'orders/transactions/'.$this->pathId($supplierTransactionId).'/cards',
                logResponse: false,
                accept: str_replace('-v1+', '-v2+', (string) config('suppliers.reloadly.accept')),
            );
        } catch (ConnectionException|SupplierNotConfigured) {
            throw new DeliverableFetchFailed('Reloadly cards could not be fetched: connection failed.');
        }

        if (! $response->successful()) {
            throw new DeliverableFetchFailed("Reloadly cards could not be fetched: HTTP {$response->status()}.", $response->status());
        }

        $payload = ReloadlyClient::decode($response);

        if ($payload === null) {
            throw new DeliverableFetchFailed('Reloadly cards response was not readable.', $response->status());
        }

        // The shape for more than one card is UNVERIFIED; a single object and a list are accepted.
        $cards = array_is_list($payload) ? $payload : [$payload];
        $items = [];

        foreach ($cards as $card) {
            $code = is_array($card) ? $this->secret($card['cardNumber'] ?? null) : null;

            if ($code === null) {
                throw new DeliverableFetchFailed('Reloadly cards response had no card number.', $response->status());
            }

            $items[] = new DeliverableItem($code, $this->secret($card['pinCode'] ?? null), $this->secret($card['redemptionUrl'] ?? null));
        }

        if ($items === []) {
            throw new DeliverableFetchFailed('Reloadly cards response was empty.', $response->status());
        }

        return new DeliverablePayload($items);
    }

    /**
     * Transaction report for reconciliation. The date filter format is UNVERIFIED; rows are also
     * filtered by their own timestamp where it can be read.
     */
    public function getTransactions(CarbonInterface $from, CarbonInterface $to, int $page): SupplierTransactionPage
    {
        $payload = $this->read('transactions.report', 'reports/transactions', [
            'page' => $page,
            'size' => (int) config('suppliers.reloadly.page_size'),
            'startDate' => $from->format('Y-m-d H:i:s'),
            'endDate' => $to->format('Y-m-d H:i:s'),
        ]);

        [$rows, $isLast] = $this->page($payload, $page);
        $records = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $outcome = ReloadlyOutcomeClassifier::fromTransaction($row);

            if ($outcome->externalTransactionId === null) {
                continue;
            }

            $records[] = new SupplierTransactionRecord(
                $outcome->externalTransactionId,
                is_string($row['customIdentifier'] ?? null) ? $row['customIdentifier'] : null,
                $outcome->kind,
                is_string($row['status'] ?? null) ? $row['status'] : null,
                ReloadlyOutcomeClassifier::cost($row),
                $this->timestamp($row['transactionCreatedTime'] ?? null),
            );
        }

        return new SupplierTransactionPage($records, $isLast);
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<array-key, mixed>
     *
     * @throws SupplierRequestFailed
     */
    private function read(string $operation, string $path, array $query = []): array
    {
        try {
            $response = $this->client->send($operation, 'GET', $path, $query);
        } catch (ConnectionException) {
            throw new SupplierRequestFailed("Reloadly {$operation} failed: connection error.");
        }

        if (! $response->successful()) {
            throw new SupplierRequestFailed("Reloadly {$operation} failed: HTTP {$response->status()}.", $response->status());
        }

        return ReloadlyClient::decode($response) ?? throw new SupplierRequestFailed("Reloadly {$operation} returned an unreadable body.", $response->status());
    }

    /**
     * Paged responses carry `content` and `last` (or `totalPages`); a bare list is one page.
     *
     * @param  array<array-key, mixed>  $payload
     * @return array{0: list<mixed>, 1: bool}
     */
    private function page(array $payload, int $page): array
    {
        if (array_is_list($payload)) {
            return [$payload, true];
        }

        $rows = is_array($payload['content'] ?? null) ? array_values($payload['content']) : [];
        $last = $payload['last'] ?? null;
        $totalPages = ExactJson::decimal($payload['totalPages'] ?? null);

        $isLast = match (true) {
            is_bool($last) => $last,
            $totalPages !== null => $page >= (int) $totalPages,
            default => true,
        };

        return [$rows, $isLast || $rows === []];
    }

    private function pathId(string $id): string
    {
        if (preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id) !== 1) {
            throw new SupplierRequestFailed('Refusing an identifier that is not a plain supplier id.');
        }

        return $id;
    }

    private function secret(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function timestamp(mixed $value): ?CarbonImmutable
    {
        try {
            return is_string($value) && $value !== '' ? CarbonImmutable::parse($value) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
