import 'package:dio/dio.dart';

/// Sends `Accept-Language` with the app's language (`en` or `ar`).
class LocaleInterceptor extends Interceptor {
  LocaleInterceptor(this._languageCode);

  static const header = 'Accept-Language';

  final String Function() _languageCode;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers[header] = _languageCode();
    handler.next(options);
  }
}
