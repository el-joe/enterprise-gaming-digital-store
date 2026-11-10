import 'package:application/core/config/app_env.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/interceptors/auth_interceptor.dart';
import 'package:application/core/network/interceptors/cart_token_interceptor.dart';
import 'package:application/core/network/interceptors/currency_interceptor.dart';
import 'package:application/core/network/interceptors/idempotency_interceptor.dart';
import 'package:application/core/network/interceptors/locale_interceptor.dart';
import 'package:application/core/network/interceptors/redacting_log_interceptor.dart';
import 'package:application/core/network/interceptors/request_id_interceptor.dart';
import 'package:application/core/network/interceptors/retry_interceptor.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'dio_client.g.dart';

/// Base options shared by every API call.
BaseOptions apiBaseOptions(AppEnv env) => BaseOptions(
  baseUrl: env.apiV1BaseUrl,
  connectTimeout: const Duration(seconds: 10),
  sendTimeout: const Duration(seconds: 20),
  receiveTimeout: const Duration(seconds: 20),
  headers: const {'Accept': 'application/json'},
  contentType: Headers.jsonContentType,
);

/// Builds the API client with the interceptors in their required order: request id, locale,
/// currency and cart headers, idempotency key, auth (refresh and replay), retry, then the
/// debug-only redacting log.
Dio createApiClient({
  required AppEnv env,
  required SecureStore store,
  required String Function() languageCode,
  required String? Function() currency,
  required TokenRefresher refresh,
  required void Function() onSessionExpired,
  bool enableLogging = kDebugMode,
}) {
  final dio = Dio(apiBaseOptions(env));
  dio.interceptors.addAll([
    RequestIdInterceptor(),
    LocaleInterceptor(languageCode),
    CurrencyInterceptor(currency),
    CartTokenInterceptor(store),
    IdempotencyInterceptor(),
    AuthInterceptor(
      dio: dio,
      store: store,
      refresh: refresh,
      onSessionExpired: onSessionExpired,
    ),
    RetryInterceptor(dio: dio),
    if (enableLogging) RedactingLogInterceptor(),
  ]);
  return dio;
}

/// Refresh-token exchange. The mobile refresh endpoint does not exist yet (gap G1, M-A2), so the
/// foundation refuses every refresh: a 401 signs the user out. M-A7 overrides this provider with
/// the data source call to `POST /api/v1/mobile/auth/refresh`.
@Riverpod(keepAlive: true)
TokenRefresher tokenRefresher(Ref ref) =>
    (refreshToken) async => null;

@Riverpod(keepAlive: true)
Dio apiClient(Ref ref) {
  final dio = createApiClient(
    env: ref.watch(appEnvProvider),
    store: ref.watch(secureStoreProvider),
    languageCode: () => ref.read(appLocaleProvider).languageCode,
    currency: () => ref.read(displayCurrencyProvider),
    refresh: ref.watch(tokenRefresherProvider),
    onSessionExpired: () => ref.read(sessionProvider.notifier).signOut(),
  );
  ref.onDispose(dio.close);
  return dio;
}
