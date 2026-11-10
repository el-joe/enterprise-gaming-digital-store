<?php

/**
 * Reloadly Gift Cards adapter. Mocked with Http::fake only; awaiting sandbox verification.
 */

use App\Domains\Suppliers\Adapters\Reloadly\ReloadlyGiftCardAdapter;
use App\Domains\Suppliers\DTOs\ProductQuery;
use App\Domains\Suppliers\DTOs\SupplierOrderRequest;
use App\Domains\Suppliers\Enums\CapabilityLevel;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierOutcomeKind as Kind;
use App\Domains\Suppliers\Exceptions\CapabilityNotSupported;
use App\Domains\Suppliers\Exceptions\DeliverableFetchFailed;
use App\Domains\Suppliers\Models\SupplierApiLog;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function reloadlyAdapter(): ReloadlyGiftCardAdapter
{
    $adapter = app(SupplierAdapterRegistry::class)->for(reloadlySupplier());
    assert($adapter instanceof ReloadlyGiftCardAdapter);

    return $adapter;
}

function orderRequest(string $reference = 'uTESTs1'): SupplierOrderRequest
{
    return new SupplierOrderRequest($reference, '1234', '25.00', 'USD', 1);
}

describe('classification table (INTEGRATIONS.md 1.5)', function (): void {
    it('classifies each order response', function (mixed $response, Kind $kind, ?bool $notSent = null, bool $costReversal = false): void {
        fakeReloadly($response);

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->kind)->toBe($kind)
            ->and($outcome->costReversalPending)->toBe($costReversal);

        if ($notSent !== null) {
            expect($outcome->notSent)->toBe($notSent);
        }
    })->with([
        'SUCCESSFUL' => [fn () => Http::response(reloadlyTransaction('SUCCESSFUL')), Kind::Fulfilled],
        'PENDING' => [fn () => Http::response(reloadlyTransaction('PENDING')), Kind::InProgress],
        'PROCESSING' => [fn () => Http::response(reloadlyTransaction('PROCESSING')), Kind::InProgress],
        'REFUNDED' => [fn () => Http::response(reloadlyTransaction('REFUNDED')), Kind::Rejected, false],
        'FAILED sets cost reversal pending' => [fn () => Http::response(reloadlyTransaction('FAILED')), Kind::Rejected, false, true],
        'connection refused' => [fn () => Http::failedConnection('cURL error 7: Failed to connect to giftcards-sandbox.reloadly.com port 443: Connection refused'), Kind::Rejected, true],
        'DNS failure' => [fn () => Http::failedConnection('cURL error 6: Could not resolve host: giftcards-sandbox.reloadly.com'), Kind::Rejected, true],
        'TLS handshake failure' => [fn () => Http::failedConnection('cURL error 35: OpenSSL SSL_connect: SSL_ERROR_SYSCALL'), Kind::Rejected, true],
        'connect timeout' => [fn () => Http::failedConnection('cURL error 28: Connection timed out after 5001 milliseconds'), Kind::Rejected, true],
        '400 with errorCode null' => [fn () => Http::response(['errorCode' => null, 'message' => 'Bad body'], 400), Kind::Rejected, false],
        'read timeout' => [fn () => Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received'), Kind::Ambiguous],
        'connection reset' => [fn () => Http::failedConnection('cURL error 56: Recv failure: Connection reset by peer'), Kind::Ambiguous],
        'unknown transport failure' => [fn () => Http::failedConnection('Something odd happened'), Kind::Ambiguous],
        '500' => [fn () => Http::response(['message' => 'oops'], 500), Kind::Ambiguous],
        '503' => [fn () => Http::response('', 503), Kind::Ambiguous],
        '429' => [fn () => Http::response(['errorCode' => 'INSUFFICIENT_BALANCE'], 429), Kind::Ambiguous],
        'malformed body' => [fn () => Http::response('<html>not json</html>', 200), Kind::Ambiguous],
        '200 without a transaction id' => [fn () => Http::response(['status' => 'SUCCESSFUL']), Kind::Ambiguous],
        'unknown status' => [fn () => Http::response(reloadlyTransaction('QUEUED_SOMEWHERE')), Kind::Ambiguous],
        'unknown error code' => [fn () => Http::response(['errorCode' => 'BRAND_NEW_CODE'], 400), Kind::Ambiguous],
        '4xx without error code' => [fn () => Http::response(['message' => 'nope'], 404), Kind::Ambiguous],
    ]);

    it('rejects every documented refusal code', function (string $code): void {
        fakeReloadly(Http::response(['errorCode' => $code, 'message' => 'refused'], 400));

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->kind)->toBe(Kind::Rejected)->and($outcome->supplierErrorCode)->toBe($code);
    })->with(['INSUFFICIENT_BALANCE', 'PRODUCT_NOT_FOUND', 'PRICE_IS_NOT_PERMITTED', 'WRONG_PRODUCT_PRICE', 'PRE_ORDER_IS_NOT_ALLOWED', 'DIFFERENT_CURRENCIES', 'ACTION_NOT_ALLOWED', 'TEMPORARY_OUT_OF_STOCK', 'RESERVED']);

    it('treats every documented uncertain code as ambiguous', function (string $code): void {
        fakeReloadly(Http::response(['errorCode' => $code], 400));

        expect(reloadlyAdapter()->createOrder(orderRequest())->kind)->toBe(Kind::Ambiguous);
    })->with(['PENDING_OR_IN_PROGRESS', 'SET_ORDER_PROCESS_FACED_A_PROBLEM', 'COST_CALCULATION_PROBLEM', 'ORDER_IS_NOT_READY', 'CARD_IS_NOT_READY', 'DIGITAL_ROLLBACK', 'DUPLICATE_CARD', 'IMPORT_CARD_ERROR', 'NO_ACCESS_TO_API']);

    it('never sends the order a second time after a read timeout', function (): void {
        fakeReloadly(Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received'));

        reloadlyAdapter()->createOrder(orderRequest());

        expect(reloadlyOrderPosts())->toHaveCount(1);
    });
});

describe('authentication', function (): void {
    it('caches the token across calls', function (): void {
        fakeReloadly();
        $adapter = reloadlyAdapter();

        $adapter->createOrder(orderRequest('uAs1'));
        $adapter->createOrder(orderRequest('uBs1'));

        Http::assertSentCount(3);
        expect(Http::recorded(static fn (Request $request): bool => str_contains($request->url(), 'oauth/token')))->toHaveCount(1);
    });

    it('refreshes an expired token once and repeats the 401 call, which did not run', function (): void {
        fakeReloadly(Http::sequence()->push(['errorCode' => 'INVALID_TOKEN'], 401)->push(reloadlyTransaction('SUCCESSFUL')));

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->kind)->toBe(Kind::Fulfilled)
            ->and(reloadlyOrderPosts())->toHaveCount(2)
            ->and(Http::recorded(static fn (Request $request): bool => str_contains($request->url(), 'oauth/token')))->toHaveCount(2);
    });

    it('rejects after a second 401 without a third attempt', function (): void {
        fakeReloadly(Http::response(['errorCode' => 'INVALID_TOKEN'], 401));

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->kind)->toBe(Kind::Rejected)->and($outcome->reason)->toBe('auth_refused')
            ->and(reloadlyOrderPosts())->toHaveCount(2);
    });

    it('does not send the order when the token cannot be obtained', function (): void {
        Http::fake(['auth.reloadly.com/*' => Http::response(['error' => 'invalid_client'], 401), '*' => Http::response([], 500)]);

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->kind)->toBe(Kind::Rejected)->and($outcome->notSent)->toBeTrue()
            ->and(reloadlyOrderPosts())->toBeEmpty();
    });

    it('sends the documented token request and keeps the secret and token out of the logs', function (): void {
        fakeReloadly();

        reloadlyAdapter()->createOrder(orderRequest());

        Http::assertSent(static fn (Request $request): bool => str_contains($request->url(), 'oauth/token')
            && $request['grant_type'] === 'client_credentials'
            && $request['audience'] === RELOADLY_API);

        $logs = SupplierApiLog::query()->get()->toJson();
        expect($logs)->not->toContain('test-client-secret-value')->not->toContain('test-access-token');
    });
});

describe('order request', function (): void {
    it('buys one unit with our reference, an exact unit price and no recipient email', function (): void {
        fakeReloadly();

        reloadlyAdapter()->createOrder(new SupplierOrderRequest('uREFs7', '1234', '25.000000', 'USD', 1));

        $post = reloadlyOrderPosts()[0];
        expect($post->body())->toContain('"unitPrice":25,')
            ->and($post->data())->toMatchArray(['productId' => 1234, 'quantity' => 1, 'customIdentifier' => 'uREFs7', 'preOrder' => false])
            ->and($post->data())->not->toHaveKey('recipientEmail')
            ->and($post->header('Accept')[0])->toBe('application/com.reloadly.giftcards-v1+json');
    });

    it('records the supplier cost exactly from balanceInfo', function (): void {
        fakeReloadly(Http::response('{"transactionId":77,"status":"SUCCESSFUL","balanceInfo":{"cost":60553.3575,"currencyCode":"USD"}}', 200));

        $outcome = reloadlyAdapter()->createOrder(orderRequest());

        expect($outcome->actualCost->amount)->toBe('60553.357500')->and($outcome->externalTransactionId)->toBe('77');
    });
});

describe('deliverables', function (): void {
    it('keeps a 20-digit card number intact and never logs the body', function (): void {
        fakeReloadly(cardNumber: '98765432109876543210');

        $payload = reloadlyAdapter()->fetchDeliverables('9001');

        expect($payload->items[0]->code)->toBe('98765432109876543210')
            ->and($payload->items[0]->pin)->toBe('4321');

        $log = SupplierApiLog::query()->where('operation', 'orders.cards')->sole();
        expect($log->response_redacted)->toBeNull()
            ->and(SupplierApiLog::query()->get()->toJson())->not->toContain('98765432109876543210');
    });

    it('accepts a list of cards', function (): void {
        fakeReloadly(overrides: [RELOADLY_API.'/orders/transactions/*/cards' => Http::response('[{"cardNumber":1111222233334444,"pinCode":null},{"cardNumber":5555666677778888}]')]);

        expect(reloadlyAdapter()->fetchDeliverables('9001')->items)->toHaveCount(2);
    });

    it('fails without exposing the body', function (): void {
        fakeReloadly(overrides: [RELOADLY_API.'/orders/transactions/*/cards' => Http::response('{"cardNumber":424242424242,"oops":true', 500)]);

        try {
            reloadlyAdapter()->fetchDeliverables('9001');
            $this->fail('Expected a failure.');
        } catch (DeliverableFetchFailed $exception) {
            expect($exception->getMessage())->not->toContain('424242424242');
        }
    });
});

describe('lookups and reads', function (): void {
    it('finds an order by our reference', function (): void {
        fakeReloadly(overrides: [RELOADLY_API.'/reports/transactions*' => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '55', 'uXs1')], 'last' => true])]);

        $outcome = reloadlyAdapter()->findOrderByReference('uXs1', now());

        expect($outcome->kind)->toBe(Kind::Fulfilled)->and($outcome->externalTransactionId)->toBe('55');
        Http::assertSent(static fn (Request $request): bool => str_contains($request->url(), 'customIdentifier=uXs1'));
    });

    it('treats a lookup the supplier cannot answer clearly as ambiguous, not as not found', function (mixed $response, Kind $kind): void {
        fakeReloadly(overrides: [RELOADLY_API.'/reports/transactions*' => $response]);

        expect(reloadlyAdapter()->findOrderByReference('uXs1', now())->kind)->toBe($kind);
    })->with([
        'empty page is not found' => [fn () => Http::response(['content' => [], 'last' => true]), Kind::NotFound],
        'other references only' => [fn () => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '1', 'uOTHERs1')]]), Kind::NotFound],
        'two transactions with our reference' => [fn () => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '1', 'uXs1'), reloadlyTransaction('SUCCESSFUL', '2', 'uXs1')]]), Kind::Ambiguous],
        'server error' => [fn () => Http::response([], 502), Kind::Ambiguous],
        'unexpected shape' => [fn () => Http::response(['unexpected' => true]), Kind::Ambiguous],
        'timeout' => [fn () => Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received'), Kind::Ambiguous],
    ]);

    it('parses products, prices and the balance as exact decimals', function (): void {
        Http::fake([
            'auth.reloadly.com/*' => Http::response(['access_token' => 't', 'expires_in' => 3600]),
            RELOADLY_API.'/products*' => Http::response('{"content":[{"productId":1234,"productName":"Steam 25","denominationType":"FIXED","recipientCurrencyCode":"USD","senderCurrencyCode":"USD","senderFee":0.5,"senderFeePercentage":1.25,"discountPercentage":2,"fixedRecipientDenominations":[25.0],"fixedRecipientToSenderDenominationsMap":{"25.0":24.12345},"status":"ACTIVE","brand":{"brandName":"Steam"},"country":{"isoName":"US"}}],"last":true}'),
            RELOADLY_API.'/accounts/balance' => Http::response('{"balance":60582.23641,"currencyCode":"USD"}'),
        ]);
        $adapter = reloadlyAdapter();

        $product = $adapter->getProducts(new ProductQuery)->products[0];

        expect($product->costMap)->toBe(['25' => '24.12345'])
            ->and($product->feePct)->toBe('1.25')
            ->and($product->countryCode)->toBe('US')
            ->and($adapter->getBalance()->balance->amount)->toBe('60582.236410');
    });
});

describe('capabilities', function (): void {
    it('declares cancel and refund unsupported and refuses to call them', function (): void {
        $adapter = reloadlyAdapter();

        expect($adapter->capabilities()->level(SupplierCapability::CancelOrder))->toBe(CapabilityLevel::None)
            ->and($adapter->capabilities()->level(SupplierCapability::RefundOrder))->toBe(CapabilityLevel::None)
            ->and($adapter->capabilities()->level(SupplierCapability::ClientIdempotency))->toBe(CapabilityLevel::Unverified)
            ->and($adapter->capabilities()->level(SupplierCapability::Webhooks))->toBe(CapabilityLevel::Unverified)
            ->and(fn () => $adapter->cancelOrder('1'))->toThrow(CapabilityNotSupported::class)
            ->and(fn () => $adapter->refundOrder('1', null))->toThrow(CapabilityNotSupported::class);

        Http::assertNothingSent();
    });

    it('declares the documented USD 100 per-request cap', function (): void {
        expect(reloadlyAdapter()->capabilities()->defaultPerRequestCap->jsonSerialize())->toBe(['amount' => '100.000000', 'currency' => 'USD']);
    });
});
