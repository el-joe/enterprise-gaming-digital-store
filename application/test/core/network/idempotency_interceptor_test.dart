import 'package:application/core/network/interceptors/idempotency_interceptor.dart';
import 'package:application/core/network/interceptors/retry_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/core/utils/ulid.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';

void main() {
  Dio build(FakeHttpAdapter adapter) {
    final dio = dioWith(adapter);
    dio.interceptors.addAll([
      IdempotencyInterceptor(),
      RetryInterceptor(dio: dio, wait: (_) async {}),
    ]);
    return dio;
  }

  test('place-order gets a ULID key that is reused on retry', () async {
    final adapter = FakeHttpAdapter((options, call) async {
      if (call == 0) {
        throw DioException.connectionError(
          requestOptions: options,
          reason: 'reset',
        );
      }
      return jsonBody(okEnvelope(), status: 201);
    });
    final response = await build(adapter).post<Object?>(
      '/checkout/place-order',
      data: {'expected_total': '10.00', 'currency': 'USD'},
    );

    expect(response.statusCode, 201);
    expect(adapter.requests, hasLength(2));
    final keys = adapter.sentHeaders
        .map((h) => h[IdempotencyInterceptor.header])
        .toList();
    expect(keys[0], isA<String>());
    expect(isUlid(keys[0]! as String), isTrue);
    expect(keys[1], keys[0]);
    expect(adapter.requests[1].data, adapter.requests[0].data);
  });

  test('a key stored with the pending checkout is sent as is', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(okEnvelope(), status: 201),
    );
    await build(adapter).post<Object?>(
      '/checkout/place-order',
      options: Options(
        extra: {RequestExtras.idempotencyKey: '01JSTOREDKEY00000000000000'},
      ),
    );
    expect(
      adapter.sentHeaders.single[IdempotencyInterceptor.header],
      '01JSTOREDKEY00000000000000',
    );
  });

  test('two separate place-order calls get different keys', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(okEnvelope(), status: 201),
    );
    final dio = build(adapter);
    await dio.post<Object?>('/checkout/place-order');
    await dio.post<Object?>('/checkout/place-order');
    final keys = adapter.sentHeaders
        .map((h) => h[IdempotencyInterceptor.header])
        .toSet();
    expect(keys, hasLength(2));
  });

  test('other requests get no key', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(okEnvelope()),
    );
    final dio = build(adapter);
    await dio.post<Object?>('/storefront/cart/items');
    await dio.get<Object?>('/checkout/place-order');
    for (final headers in adapter.sentHeaders) {
      expect(headers.containsKey(IdempotencyInterceptor.header), isFalse);
    }
  });
}
