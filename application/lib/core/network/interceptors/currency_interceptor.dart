import 'package:dio/dio.dart';

/// Sends `X-Currency` with the display currency the customer picked (gap G2). Nothing is sent
/// until a currency is chosen, so the server default applies.
class CurrencyInterceptor extends Interceptor {
  CurrencyInterceptor(this._currency);

  static const header = 'X-Currency';

  final String? Function() _currency;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    final currency = _currency();
    if (currency != null && currency.isNotEmpty) {
      options.headers[header] = currency;
    }
    handler.next(options);
  }
}
