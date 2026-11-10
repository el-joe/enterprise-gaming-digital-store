import 'package:application/core/config/app_env.dart';
import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/interceptors/cart_token_interceptor.dart';
import 'package:application/core/network/interceptors/currency_interceptor.dart';
import 'package:application/core/network/interceptors/locale_interceptor.dart';
import 'package:application/core/network/interceptors/request_id_interceptor.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/core/utils/ulid.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/memory_stores.dart';

void main() {
  final env = AppEnv(
    flavor: Flavor.staging,
    apiBaseUrl: Uri.parse('https://staging.boxcards.test/'),
    appLinkHost: 'staging.boxcards.test',
  );

  test('sends the standard headers on every request', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(okEnvelope()),
    );
    final dio = createApiClient(
      env: env,
      store: SecureStore(
        storage: memorySecureStorage({SecureStore.cartTokenKey: 'cart-1'}),
      ),
      languageCode: () => 'ar',
      currency: () => 'EGP',
      refresh: (_) async => null,
      onSessionExpired: () {},
      enableLogging: false,
    )..httpClientAdapter = adapter;

    await dio.get<Object?>('/storefront/catalog/products');

    final request = adapter.requests.single;
    expect(
      request.uri.toString(),
      'https://staging.boxcards.test/api/v1/storefront/catalog/products',
    );
    final headers = adapter.sentHeaders.single;
    expect(headers['Accept'], 'application/json');
    expect(headers[LocaleInterceptor.header], 'ar');
    expect(headers[CurrencyInterceptor.header], 'EGP');
    expect(headers[CartTokenInterceptor.header], 'cart-1');
    expect(isUlid(headers[RequestIdInterceptor.header]! as String), isTrue);
    expect(headers.containsKey('Authorization'), isFalse);
  });

  test('omits X-Currency until a currency is chosen', () async {
    final adapter = FakeHttpAdapter(
      (options, _) async => jsonBody(okEnvelope()),
    );
    final dio = createApiClient(
      env: env,
      store: SecureStore(storage: memorySecureStorage()),
      languageCode: () => 'en',
      currency: () => null,
      refresh: (_) async => null,
      onSessionExpired: () {},
      enableLogging: false,
    )..httpClientAdapter = adapter;
    await dio.get<Object?>('/x');
    expect(
      adapter.sentHeaders.single.containsKey(CurrencyInterceptor.header),
      isFalse,
    );
    expect(
      adapter.sentHeaders.single.containsKey(CartTokenInterceptor.header),
      isFalse,
    );
  });
}
