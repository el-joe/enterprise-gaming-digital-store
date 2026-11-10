import 'package:application/core/network/request_extras.dart';
import 'package:application/core/utils/ulid.dart';
import 'package:dio/dio.dart';

/// Adds `Idempotency-Key` to place-order.
///
/// The caller should pass the key stored with the pending checkout in
/// `extra[RequestExtras.idempotencyKey]` so an app restart re-sends the same key. Without one,
/// a key is generated and written back into `extra` and the headers, so every retry of the same
/// [RequestOptions] reuses it.
class IdempotencyInterceptor extends Interceptor {
  IdempotencyInterceptor({String Function()? newKey})
    : _newKey = newKey ?? newUlid;

  static const header = 'Idempotency-Key';

  /// Paths (relative to `/api/v1`) that require a key.
  static const requiredPaths = {'/checkout/place-order'};

  final String Function() _newKey;

  static bool requiresKey(RequestOptions options) {
    if (options.method.toUpperCase() != 'POST') {
      return false;
    }
    final path = options.uri.path;
    return requiredPaths.any(path.endsWith);
  }

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    final existing = options.headers[header];
    final provided = options.extra[RequestExtras.idempotencyKey];
    if (existing is String && existing.isNotEmpty) {
      options.extra[RequestExtras.idempotencyKey] = existing;
    } else if (provided is String && provided.isNotEmpty) {
      options.headers[header] = provided;
    } else if (requiresKey(options)) {
      final key = _newKey();
      options.extra[RequestExtras.idempotencyKey] = key;
      options.headers[header] = key;
    }
    handler.next(options);
  }
}
