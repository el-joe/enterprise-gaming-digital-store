import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/shared/data/models/payment_models.dart';
import 'package:dio/dio.dart';

/// `/api/v1/mobile/checkout/orders/{order}/...` payment routes. Writes are never retried
/// automatically; the guest order token is attached by the interceptor.
class PaymentsRemoteDataSource {
  PaymentsRemoteDataSource(this._dio);

  final Dio _dio;

  static String _order(String orderPublicId) =>
      '/mobile/checkout/orders/$orderPublicId';

  static final _write = Options(
    extra: {RequestExtras.noRetry: true, RequestExtras.sensitive: true},
  );

  Future<PaymentNextDto> retryPayment(
    String orderPublicId, {
    String? gateway,
    String? method,
  }) async {
    final response = await _dio.post<Object?>(
      '${_order(orderPublicId)}/retry-payment',
      data: {'gateway': ?gateway, 'method': ?method, 'client': 'app'},
      options: _write,
    );
    return decodeEnvelope(
      response.data,
      (data) => PaymentNextDto.fromJson(objectAt(data, 'next')),
    ).data;
  }

  Future<OrderPaymentStatusDto> _status(String method, String path) async {
    final response = method == 'GET'
        ? await _dio.get<Object?>(path)
        : await _dio.post<Object?>(path, options: _write);
    return decodeEnvelope(
      response.data,
      (data) => OrderPaymentStatusDto.fromJson(asJsonObject(data)),
    ).data;
  }

  Future<OrderPaymentStatusDto> verifyPayment(String orderPublicId) =>
      _status('POST', '${_order(orderPublicId)}/verify-payment');

  Future<OrderPaymentStatusDto> capturePayPal(String orderPublicId) =>
      _status('POST', '${_order(orderPublicId)}/paypal/capture');

  Future<OrderPaymentStatusDto> status(String orderPublicId) =>
      _status('GET', '${_order(orderPublicId)}/status');
}
