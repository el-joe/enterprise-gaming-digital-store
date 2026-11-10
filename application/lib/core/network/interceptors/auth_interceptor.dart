import 'dart:async';

import 'package:application/core/network/request_extras.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';

/// Exchanges a refresh token for new tokens. Returns null when the server refuses (expired,
/// revoked or reused refresh token); throws on transport errors.
typedef TokenRefresher = Future<AuthTokens?> Function(String refreshToken);

/// Adds the bearer token and, on 401, refreshes once for every request that failed at the same
/// time (single flight), then retries each of them once with the new token. When refresh fails,
/// the tokens are cleared and `onSessionExpired` runs so the router sends the user to login.
class AuthInterceptor extends Interceptor {
  AuthInterceptor({
    required this._dio,
    required this._store,
    required this._refresh,
    required this._onSessionExpired,
  });

  final Dio _dio;
  final SecureStore _store;
  final TokenRefresher _refresh;
  final void Function() _onSessionExpired;

  Future<AuthTokens?>? _inFlight;

  static bool _authenticates(RequestOptions options) =>
      options.extra[RequestExtras.authenticate] != false;

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    if (_authenticates(options) &&
        options.extra[RequestExtras.authRetried] != true) {
      final token = await _store.readAccessToken();
      if (token != null) {
        options.headers['Authorization'] = 'Bearer $token';
      }
    }
    handler.next(options);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    final options = err.requestOptions;
    if (err.response?.statusCode != 401 ||
        !_authenticates(options) ||
        options.extra[RequestExtras.authRetried] == true) {
      handler.next(err);
      return;
    }

    final sentHeader = options.headers['Authorization'];
    final AuthTokens? tokens;
    try {
      tokens = await _tokensAfter401(sentHeader is String ? sentHeader : null);
    } on Object {
      // Refresh could not reach the server: keep the session and surface the original 401.
      handler.next(err);
      return;
    }
    if (tokens == null) {
      handler.next(err);
      return;
    }

    options
      ..headers['Authorization'] = 'Bearer ${tokens.accessToken}'
      ..extra[RequestExtras.authRetried] = true;
    try {
      handler.resolve(await _dio.fetch<dynamic>(options));
    } on DioException catch (retryError) {
      handler.next(retryError);
    }
  }

  /// Returns usable tokens after a 401, refreshing at most once for concurrent failures.
  Future<AuthTokens?> _tokensAfter401(String? sentAuthorization) async {
    final current = await _store.readTokens();
    if (current == null) {
      return null;
    }
    // Another request already refreshed while this one was in flight.
    if (sentAuthorization != null &&
        sentAuthorization != 'Bearer ${current.accessToken}') {
      return current;
    }
    return _inFlight ??= _runRefresh(current.refreshToken).whenComplete(() {
      _inFlight = null;
    });
  }

  Future<AuthTokens?> _runRefresh(String refreshToken) async {
    final tokens = await _refresh(refreshToken);
    if (tokens == null) {
      await _store.clearTokens();
      _onSessionExpired();
      return null;
    }
    await _store.writeTokens(tokens);
    return tokens;
  }
}
