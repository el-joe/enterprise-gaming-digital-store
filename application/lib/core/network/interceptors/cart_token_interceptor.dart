import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';

/// Sends the guest `X-Cart-Token` (gap G2) from secure storage when one is stored.
class CartTokenInterceptor extends Interceptor {
  CartTokenInterceptor(this._store);

  static const header = 'X-Cart-Token';

  final SecureStore _store;

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _store.readCartToken();
    if (token != null) {
      options.headers[header] = token;
    }
    handler.next(options);
  }
}
