import 'package:application/core/network/interceptors/cart_token_interceptor.dart';
import 'package:application/core/network/interceptors/currency_interceptor.dart';
import 'package:application/core/network/interceptors/guest_order_token_interceptor.dart';
import 'package:application/core/network/interceptors/locale_interceptor.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/repo_harness.dart';

const orderA = '01jf0000000000000000000001';
const orderB = '01JF00000000000000000000B2';

void main() {
  group('X-Cart-Token', () {
    test('is captured from the response header and sent back', () async {
      final h = Harness();
      final issued = ApiFixture.load('cart/add_item_created');
      final cart = ApiFixture.load('cart/cart_ok');
      h.dio.httpClientAdapter = h.adapter = FakeHttpAdapter(
        (options, index) async =>
            index == 0 ? issued.response() : cart.response(),
      );

      await h.dio.post<Object?>(
        '/mobile/storefront/cart/items',
        data: {'variant_id': 'v', 'quantity': 1},
      );
      await h.dio.get<Object?>('/mobile/storefront/cart');
      await h.dio.post<Object?>(
        '/mobile/checkout/validate',
        data: {'country': 'US'},
      );

      expect(
        h.adapter.sentHeaders[0].containsKey(CartTokenInterceptor.header),
        isFalse,
      );
      expect(h.stored[SecureStore.cartTokenKey], 'fixture-cart-token');
      expect(
        h.adapter.sentHeaders[1][CartTokenInterceptor.header],
        'fixture-cart-token',
      );
      expect(
        h.adapter.sentHeaders[2][CartTokenInterceptor.header],
        'fixture-cart-token',
      );
    });

    test('a response without the header keeps the stored token', () async {
      final h = Harness(stored: {SecureStore.cartTokenKey: 'kept'})
        ..serveFixture('cart/cart_ok');

      await h.dio.get<Object?>('/mobile/storefront/cart');

      expect(h.stored[SecureStore.cartTokenKey], 'kept');
    });
  });

  group('X-Guest-Order-Token', () {
    late Harness h;

    setUp(() {
      h = Harness(
        stored: {
          '${SecureStore.guestOrderTokenPrefix}$orderA': 'token-a',
          '${SecureStore.guestOrderTokenPrefix}$orderB': 'token-b',
        },
      );
      h.dio.httpClientAdapter = h.adapter = FakeHttpAdapter(
        (options, _) async => jsonBody(okEnvelope()),
      );
    });

    String? sent(int index) =>
        h.adapter.sentHeaders[index][GuestOrderTokenInterceptor.header]
            as String?;

    test('is attached per order on every order-scoped route', () async {
      await h.dio.get<Object?>('/mobile/checkout/orders/$orderA/status');
      await h.dio.post<Object?>(
        '/mobile/checkout/orders/$orderB/verify-payment',
      );
      await h.dio.post<Object?>(
        '/mobile/checkout/orders/$orderA/paypal/capture',
      );
      await h.dio.post<Object?>(
        '/mobile/checkout/orders/$orderB/retry-payment',
      );
      await h.dio.get<Object?>('/mobile/checkout/guest/orders/$orderA');
      await h.dio.post<Object?>(
        '/mobile/checkout/guest/orders/$orderB/units/01JF0000000000000000000002/reveal',
      );
      await h.dio.post<Object?>(
        '/mobile/checkout/guest/otp/verify',
        data: {'order_public_id': orderA, 'code': '123456'},
      );

      expect(
        [for (var i = 0; i < 7; i++) sent(i)],
        [
          'token-a',
          'token-b',
          'token-a',
          'token-b',
          'token-a',
          'token-b',
          'token-a',
        ],
      );
    });

    test('is not sent for other orders or other routes', () async {
      await h.dio.get<Object?>(
        '/mobile/checkout/orders/01JF00000000000000000000C3/status',
      );
      await h.dio.get<Object?>('/mobile/storefront/cart');
      await h.dio.post<Object?>(
        '/mobile/checkout/place-order',
        data: {'country': 'US'},
      );
      await h.dio.get<Object?>('/mobile/account/orders/$orderA');

      expect([for (var i = 0; i < 4; i++) sent(i)], [null, null, null, null]);
    });
  });

  test('X-Currency and Accept-Language follow the app settings', () async {
    final h = Harness(languageCode: 'ar', currency: 'EGP')
      ..serveFixture('catalog/products_filtered_egp');

    await h.dio.get<Object?>(
      '/mobile/storefront/catalog/products',
      queryParameters: {'platform': 'orbit', 'sort': 'price_asc'},
    );

    expect(h.adapter.sentHeaders.single[CurrencyInterceptor.header], 'EGP');
    expect(h.adapter.sentHeaders.single[LocaleInterceptor.header], 'ar');
  });
}
