import 'package:application/core/network/interceptors/idempotency_interceptor.dart';
import 'package:application/core/network/interceptors/retry_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';

void main() {
  late List<Duration> waits;

  Dio build(FakeHttpAdapter adapter, {int maxRetries = 2}) {
    final dio = dioWith(adapter);
    dio.interceptors.addAll([
      IdempotencyInterceptor(),
      RetryInterceptor(
        dio: dio,
        maxRetries: maxRetries,
        wait: (delay) async => waits.add(delay),
      ),
    ]);
    return dio;
  }

  FakeHttpAdapter failingOnce() => FakeHttpAdapter((options, call) async {
    if (call == 0) {
      throw DioException.connectionError(
        requestOptions: options,
        reason: 'reset',
      );
    }
    return jsonBody(okEnvelope());
  });

  setUp(() => waits = []);

  test('retries a GET after a connection error', () async {
    final adapter = failingOnce();
    final response = await build(adapter)
        .get<Object?>('/storefront/catalog/products');
    expect(response.statusCode, 200);
    expect(adapter.requests, hasLength(2));
    expect(waits, [const Duration(milliseconds: 500)]);
  });

  test('never retries a non-idempotent POST', () async {
    for (final path in [
      '/storefront/cart/items',
      '/account/orders/ord_1/units/unit_1/reveal',
      '/checkout/orders/ord_1/verify-payment',
    ]) {
      final adapter = failingOnce();
      await expectLater(
        build(adapter).post<Object?>(path, data: {'a': 1}),
        throwsA(isA<DioException>()),
      );
      expect(adapter.requests, hasLength(1), reason: path);
    }
    expect(waits, isEmpty);
  });

  test('never retries PATCH or DELETE without a key', () async {
    final patch = failingOnce();
    await expectLater(
      build(patch).patch<Object?>('/storefront/cart/items/1'),
      throwsA(isA<DioException>()),
    );
    expect(patch.requests, hasLength(1));
    final delete = failingOnce();
    await expectLater(
      build(delete).delete<Object?>('/storefront/cart/items/1'),
      throwsA(isA<DioException>()),
    );
    expect(delete.requests, hasLength(1));
  });

  test('retries place-order because it carries an Idempotency-Key', () async {
    final adapter = failingOnce();
    await build(adapter).post<Object?>('/checkout/place-order');
    expect(adapter.requests, hasLength(2));
  });

  test('honours Retry-After on 429 for GET', () async {
    final adapter = FakeHttpAdapter((options, call) async {
      if (call == 0) {
        return jsonBody(
          errorEnvelope('rate_limited'),
          status: 429,
          headers: {
            'retry-after': ['3'],
          },
        );
      }
      return jsonBody(okEnvelope());
    });
    await build(adapter).get<Object?>('/storefront/search/suggest');
    expect(waits, [const Duration(seconds: 3)]);
  });

  test('gives up when Retry-After is longer than the cap', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(
        errorEnvelope('rate_limited'),
        status: 429,
        headers: {
          'retry-after': ['3600'],
        },
      ),
    );
    await expectLater(
      build(adapter).get<Object?>('/x'),
      throwsA(isA<DioException>()),
    );
    expect(adapter.requests, hasLength(1));
  });

  test('does not retry maintenance or client errors', () async {
    for (final body in [
      jsonBody(errorEnvelope('maintenance'), status: 503),
      jsonBody(errorEnvelope('not_found'), status: 404),
      jsonBody(errorEnvelope('validation_failed'), status: 422),
    ]) {
      final adapter = FakeHttpAdapter((options, _) async => body);
      await expectLater(
        build(adapter).get<Object?>('/x'),
        throwsA(isA<DioException>()),
      );
      expect(adapter.requests, hasLength(1));
    }
  });

  test('stops after maxRetries', () async {
    final adapter = FakeHttpAdapter((options, _) async {
      throw DioException.connectionError(
        requestOptions: options,
        reason: 'down',
      );
    });
    await expectLater(
      build(adapter).get<Object?>('/x'),
      throwsA(isA<DioException>()),
    );
    expect(adapter.requests, hasLength(3));
    expect(waits, [
      const Duration(milliseconds: 500),
      const Duration(seconds: 2),
    ]);
  });

  test('noRetry opts a GET out', () async {
    final adapter = failingOnce();
    await expectLater(
      build(adapter).get<Object?>(
        '/x',
        options: Options(extra: {RequestExtras.noRetry: true}),
      ),
      throwsA(isA<DioException>()),
    );
    expect(adapter.requests, hasLength(1));
  });

  test('isReplayable rules', () {
    expect(RetryInterceptor.isReplayable(RequestOptions(path: '/a')), isTrue);
    expect(
      RetryInterceptor.isReplayable(RequestOptions(path: '/a', method: 'POST')),
      isFalse,
    );
    expect(
      RetryInterceptor.isReplayable(
        RequestOptions(
          path: '/a',
          method: 'POST',
          headers: {IdempotencyInterceptor.header: 'k'},
        ),
      ),
      isTrue,
    );
  });
}
