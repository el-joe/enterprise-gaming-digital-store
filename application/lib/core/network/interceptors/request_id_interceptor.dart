import 'package:application/core/utils/ulid.dart';
import 'package:dio/dio.dart';

/// Adds `X-Request-Id` (ULID). A retried request keeps its id so server logs correlate attempts.
class RequestIdInterceptor extends Interceptor {
  RequestIdInterceptor({String Function()? newId}) : _newId = newId ?? newUlid;

  static const header = 'X-Request-Id';

  final String Function() _newId;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    if (!_hasHeader(options, header)) {
      options.headers[header] = _newId();
    }
    handler.next(options);
  }
}

bool _hasHeader(RequestOptions options, String name) {
  final lower = name.toLowerCase();
  return options.headers.keys.any((key) => key.toLowerCase() == lower);
}
