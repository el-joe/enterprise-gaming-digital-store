import 'package:application/core/network/failure.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/checkout/data/datasources/checkout_remote_data_source.dart';
import 'package:application/features/checkout/data/repositories/checkout_repository_impl.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/domain/usecases/place_order.dart';
import 'package:application/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:application/features/payments/data/repositories/payments_repository_impl.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/repo_harness.dart';

const request = PlaceOrderRequest(
  country: 'US',
  gateway: 'stripe',
  currency: 'USD',
  expectedTotal: '10.00',
  acceptTerms: true,
  email: 'guest@example.com',
);

void main() {
  late Harness h;
  late PlaceOrder placeOrder;
  late List<String> issuedKeys;

  /// Builds the use case on the real repositories; [answers] are served in order.
  void build(List<Object> answers, {Map<String, String>? stored}) {
    h = Harness(
      stored: stored ?? {SecureStore.cartTokenKey: 'fixture-cart-token'},
    );
    var counter = 0;
    issuedKeys = [];
    placeOrder = PlaceOrder(
      checkout: CheckoutRepositoryImpl(
        CheckoutRemoteDataSource(h.dio),
        h.store,
      ),
      payments: PaymentsRepositoryImpl(PaymentsRemoteDataSource(h.dio)),
      pending: SecurePendingCheckoutStore(h.store),
      newKey: () {
        final key = 'key-${++counter}';
        issuedKeys.add(key);
        return key;
      },
    );
    h.adapter = FakeHttpAdapter((options, index) async {
      final answer = answers[index];
      if (answer is ApiFixture) {
        return answer.response();
      }
      if (answer is DioExceptionType) {
        throw DioException(requestOptions: options, type: answer);
      }
      throw StateError('Unexpected answer $answer');
    });
    h.dio.httpClientAdapter = h.adapter;
  }

  String? sentKey(int index) =>
      h.adapter.requests[index].headers['Idempotency-Key'] as String?;

  test('reuses the key on a retry with the same body', () async {
    build([
      DioExceptionType.connectionError,
      DioExceptionType.connectionError,
      DioExceptionType.connectionError,
      ApiFixture.load('checkout/place_order_created'),
    ]);

    final first = await placeOrder(request);
    expect(first.failureOrNull, isA<NetworkFailure>());
    // The interceptor retried twice with the same key; the pending checkout keeps it.
    expect(
      h.adapter.requests.map((r) => r.headers['Idempotency-Key']).toSet(),
      {'key-1'},
    );

    final second = await placeOrder(request);
    expect(okValue(second), isA<PlaceOrderPay>());
    expect(sentKey(3), 'key-1');
    expect(issuedKeys, ['key-1']);
  });

  test('a changed body gets a new key', () async {
    build([
      ApiFixture.load('checkout/place_order_price_changed'),
      ApiFixture.load('checkout/place_order_created'),
    ]);

    final changed = await placeOrder(request.copyWith(expectedTotal: '9.00'));
    expect((changed.failureOrNull! as DomainFailure).code, 'price_changed');

    okValue(await placeOrder(request));
    expect(sentKey(0), 'key-1');
    expect(sentKey(1), 'key-2');
  });

  test('a body change after a network failure also gets a new key', () async {
    build([
      DioExceptionType.receiveTimeout,
      DioExceptionType.receiveTimeout,
      DioExceptionType.receiveTimeout,
      ApiFixture.load('checkout/place_order_created'),
    ]);

    await placeOrder(request);
    okValue(await placeOrder(request.copyWith(marketingConsent: true)));

    expect(sentKey(3), 'key-2');
  });

  test(
    'the same key survives a restart through the pending checkout',
    () async {
      build([
        DioExceptionType.connectionError,
        DioExceptionType.connectionError,
        DioExceptionType.connectionError,
      ]);
      await placeOrder(request);
      final storedAfterCrash = Map<String, String>.of(h.stored);

      build([
        ApiFixture.load('checkout/place_order_replayed'),
        ApiFixture.load('payments/retry_payment_stripe'),
      ], stored: storedAfterCrash);
      final outcome = okValue(await placeOrder(request));

      expect(sentKey(0), 'key-1');
      expect(issuedKeys, isEmpty);
      expect((outcome as PlaceOrderPay).replayed, isTrue);
    },
  );

  test('stores the order on the pending checkout to resume at its status', () async {
    build([ApiFixture.load('checkout/place_order_created')]);

    final outcome = okValue(await placeOrder(request)) as PlaceOrderPay;

    expect(outcome.next, isA<StripeNext>());
    expect(await placeOrder.pendingOrder(), '01jf0000000000000000000001');
    expect(
      h.stored['${SecureStore.guestOrderTokenPrefix}01jf0000000000000000000001'],
      'fixture-token',
    );

    await placeOrder.finish();
    expect(await placeOrder.pendingOrder(), isNull);
  });

  test('meta.duplicate routes to the existing order without paying', () async {
    build([ApiFixture.load('checkout/place_order_duplicate')]);

    final outcome = okValue(await placeOrder(request));

    expect(outcome, isA<PlaceOrderDuplicate>());
    expect(
      (outcome as PlaceOrderDuplicate).order.publicId,
      '01jf0000000000000000000001',
    );
    expect(h.adapter.requests, hasLength(1));
  });

  test('next: null calls retry-payment once', () async {
    build([
      ApiFixture.load('checkout/place_order_next_null'),
      ApiFixture.load('payments/retry_payment_paypal'),
    ]);

    final outcome = okValue(await placeOrder(request)) as PlaceOrderPay;

    expect(outcome.next, isA<PayPalNext>());
    expect(
      h.adapter.requests.last.uri.path,
      endsWith(
        '/mobile/checkout/orders/01jf0000000000000000000001/retry-payment',
      ),
    );
    expect(h.adapter.sentHeaders.last['X-Guest-Order-Token'], 'fixture-token');
  });

  test('a replay with next: null whose retry fails reports the order without a payment', () async {
    build([
      ApiFixture.load('checkout/place_order_replayed'),
      ApiFixture.load('payments/retry_payment_not_payable'),
    ]);

    final outcome = okValue(await placeOrder(request));

    expect(outcome, isA<PlaceOrderPaymentNotStarted>());
    expect(
      ((outcome as PlaceOrderPaymentNotStarted).failure as ConflictFailure)
          .code,
      'order_not_payable',
    );
  });

  test(
    'idempotency_in_progress keeps the key; idempotency_mismatch drops it',
    () async {
      build([
        _conflict('idempotency_in_progress'),
        ApiFixture.load('checkout/place_order_idempotency_mismatch'),
        ApiFixture.load('checkout/place_order_created'),
      ]);

      await placeOrder(request);
      await placeOrder(request);
      okValue(await placeOrder(request));

      expect([sentKey(0), sentKey(1), sentKey(2)], ['key-1', 'key-1', 'key-2']);
    },
  );

  test('the body hash ignores key order and changes with any value', () {
    expect(
      PlaceOrder.bodyHash({
        'a': 1,
        'b': {'c': true, 'd': null},
      }),
      PlaceOrder.bodyHash({
        'b': {'d': null, 'c': true},
        'a': 1,
      }),
    );
    expect(
      PlaceOrder.bodyHash(request.toBody()),
      isNot(PlaceOrder.bodyHash(request.copyWith(gateway: 'paypal').toBody())),
    );
  });
}

ApiFixture _conflict(String code) {
  final mismatch = ApiFixture.load('checkout/place_order_idempotency_mismatch');
  return ApiFixture.fromParts(
    name: code,
    method: mismatch.method,
    path: mismatch.path,
    status: 409,
    headers: mismatch.headers,
    body: {...mismatch.body! as Map<String, dynamic>, 'code': code},
  );
}
