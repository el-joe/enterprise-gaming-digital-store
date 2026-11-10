<?php

namespace App\Domains\Suppliers\Adapters\Reloadly;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\DTOs\SupplierOrderOutcome;
use App\Domains\Suppliers\Services\ExactJson;
use App\Domains\Suppliers\Services\HttpFailureClassifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;

/**
 * INTEGRATIONS.md 1.5, default-deny. A response is Rejected only when it matches the explicit
 * lists below; every other status, error code or shape is Ambiguous and goes to verification.
 * The Rejected list is a judgment from the documented descriptions and is UNVERIFIED until it is
 * checked in the sandbox and with Reloadly support.
 */
final class ReloadlyOutcomeClassifier
{
    /** Documented refusals that mean the order was not purchased. */
    public const array REJECTED_ERROR_CODES = [
        'INSUFFICIENT_BALANCE', 'PRODUCT_NOT_FOUND', 'PRICE_IS_NOT_PERMITTED', 'WRONG_PRODUCT_PRICE',
        'PRE_ORDER_IS_NOT_ALLOWED', 'DIFFERENT_CURRENCIES', 'ACTION_NOT_ALLOWED', 'TEMPORARY_OUT_OF_STOCK', 'RESERVED',
    ];

    /** Documented codes whose effect on funds is unclear: never treated as a refusal. */
    public const array AMBIGUOUS_ERROR_CODES = [
        'PENDING_OR_IN_PROGRESS', 'SET_ORDER_PROCESS_FACED_A_PROBLEM', 'COST_CALCULATION_PROBLEM', 'ORDER_IS_NOT_READY',
        'CARD_IS_NOT_READY', 'DIGITAL_ROLLBACK', 'DUPLICATE_CARD', 'IMPORT_CARD_ERROR', 'NO_ACCESS_TO_API',
    ];

    public static function fromTransport(ConnectionException $exception): SupplierOrderOutcome
    {
        $label = HttpFailureClassifier::label($exception);

        return HttpFailureClassifier::wasNotSent($exception)
            ? SupplierOrderOutcome::rejected('not_sent_'.$label, notSent: true)
            : SupplierOrderOutcome::ambiguous($label);
    }

    /**
     * Classify the response to POST /orders or a transaction read.
     */
    public static function fromOrderResponse(Response $response): SupplierOrderOutcome
    {
        $status = $response->status();
        $payload = ReloadlyClient::decode($response);

        if ($status === 401 || $status === 403) {
            return SupplierOrderOutcome::rejected('auth_refused', httpStatus: $status);
        }

        if ($status === 429 || $status >= 500) {
            return SupplierOrderOutcome::ambiguous($status === 429 ? 'rate_limited' : 'server_error', httpStatus: $status);
        }

        if ($payload === null) {
            return SupplierOrderOutcome::ambiguous('malformed_body', httpStatus: $status);
        }

        if ($status >= 200 && $status < 300) {
            return self::fromTransaction($payload, $status);
        }

        $errorCode = $payload['errorCode'] ?? null;

        if ($status === 400 && array_key_exists('errorCode', $payload) && $errorCode === null) {
            return SupplierOrderOutcome::rejected('invalid_request', httpStatus: $status);
        }

        if (! is_string($errorCode)) {
            return SupplierOrderOutcome::ambiguous('unclassified_error', httpStatus: $status);
        }

        $errorCode = strtoupper(mb_substr($errorCode, 0, 64));

        return in_array($errorCode, self::REJECTED_ERROR_CODES, true)
            ? SupplierOrderOutcome::rejected('supplier_refused', $errorCode, httpStatus: $status)
            : SupplierOrderOutcome::ambiguous(in_array($errorCode, self::AMBIGUOUS_ERROR_CODES, true) ? 'supplier_uncertain' : 'unknown_error_code', $errorCode, httpStatus: $status);
    }

    /**
     * A transaction object, from the order response or the transaction report.
     *
     * @param  array<array-key, mixed>  $transaction
     */
    public static function fromTransaction(array $transaction, ?int $httpStatus = null): SupplierOrderOutcome
    {
        $externalId = ExactJson::identifier($transaction['transactionId'] ?? null);
        $state = is_string($transaction['status'] ?? null) ? strtoupper($transaction['status']) : null;

        return match (true) {
            $externalId === null => SupplierOrderOutcome::ambiguous('missing_transaction_id', httpStatus: $httpStatus),
            $state === 'SUCCESSFUL' => SupplierOrderOutcome::fulfilled('supplier_successful', $externalId, self::cost($transaction), httpStatus: $httpStatus),
            $state === 'PENDING', $state === 'PROCESSING' => SupplierOrderOutcome::inProgress('supplier_'.strtolower($state), $externalId, self::cost($transaction), $httpStatus),
            $state === 'REFUNDED' => SupplierOrderOutcome::rejected('supplier_refunded', externalId: $externalId, httpStatus: $httpStatus),
            $state === 'FAILED' => SupplierOrderOutcome::rejected('supplier_failed', externalId: $externalId, costReversalPending: true, httpStatus: $httpStatus),
            default => SupplierOrderOutcome::ambiguous('unknown_transaction_status', externalId: $externalId, httpStatus: $httpStatus),
        };
    }

    /**
     * Cost basis for profit is `balanceInfo.cost` (INTEGRATIONS.md 1.3).
     *
     * @param  array<array-key, mixed>  $transaction
     */
    public static function cost(array $transaction): ?Money
    {
        $info = $transaction['balanceInfo'] ?? null;

        if (! is_array($info)) {
            return null;
        }

        $amount = ExactJson::decimal($info['cost'] ?? null);
        $currency = $info['currencyCode'] ?? null;

        if ($amount === null || ! is_string($currency) || preg_match('/^[A-Za-z]{3}$/', $currency) !== 1) {
            return null;
        }

        try {
            return Money::of($amount, $currency);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
