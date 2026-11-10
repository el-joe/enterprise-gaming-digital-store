import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';

/// Guest cart token (API_DOCUMENTATION.md 1c).
///
/// Sends `X-Cart-Token` from secure storage when one is stored, and captures the token the server
/// issues once, in the `X-Cart-Token` response header of the first guest add-to-cart, into secure
/// storage.
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

  @override
  Future<void> onResponse(
    Response<dynamic> response,
    ResponseInterceptorHandler handler,
  ) async {
    final issued = response.headers.value(header);
    if (issued != null && issued.isNotEmpty) {
      await _store.writeCartToken(issued);
    }
    handler.next(response);
  }
}
