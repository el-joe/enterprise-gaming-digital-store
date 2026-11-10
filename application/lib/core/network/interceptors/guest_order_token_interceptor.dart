import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';

/// Attaches the per-order guest token (`X-Guest-Order-Token`, API_DOCUMENTATION.md 1c) to the
/// order-scoped checkout routes: `/checkout/orders/{order}/...` (status, verify-payment,
/// paypal/capture, retry-payment), `/checkout/guest/orders/{order}` (detail, access link, reveal)
/// and `/checkout/guest/otp/verify` (the order id is in the body).
///
/// The token is looked up per order in secure storage; a request that already carries the header
/// (for example the OTP verify that presents the link token explicitly) is left alone.
class GuestOrderTokenInterceptor extends Interceptor {
  GuestOrderTokenInterceptor(this._store);

  static const header = 'X-Guest-Order-Token';

  static final _orderPath = RegExp(
    r'/checkout/(?:guest/)?orders/([0-9A-Za-z]{26})(?:/|$)',
  );

  final SecureStore _store;

  /// The order public id an order-scoped request refers to, or null.
  static String? orderIdOf(RequestOptions options) {
    final match = _orderPath.firstMatch(options.uri.path);
    if (match != null) {
      return match.group(1);
    }
    if (options.uri.path.endsWith('/checkout/guest/otp/verify')) {
      final data = options.data;
      if (data is Map && data['order_public_id'] is String) {
        return data['order_public_id'] as String;
      }
    }
    return null;
  }

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final hasHeader = options.headers.keys.any(
      (key) => key.toLowerCase() == header.toLowerCase(),
    );
    final orderId = hasHeader ? null : orderIdOf(options);
    if (orderId != null) {
      final token = await _store.readGuestOrderToken(orderId);
      if (token != null) {
        options.headers[header] = token;
      }
    }
    handler.next(options);
  }
}
