import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/checkout/data/models/checkout_models.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:application/shared/data/models/payment_models.dart';
import 'package:dio/dio.dart';

/// A place-order response with the replay header.
final class PlaceOrderEnvelope {
  const PlaceOrderEnvelope(this.envelope, {required this.replayed});

  final ApiEnvelope<PlaceOrderDataDto> envelope;
  final bool replayed;

  bool get duplicate => envelope.meta['duplicate'] == true;
}

/// `/api/v1/mobile/checkout` (API_DOCUMENTATION.md 1c, 3a, 3b, 4a).
class CheckoutRemoteDataSource {
  CheckoutRemoteDataSource(this._dio);

  static const base = '/mobile/checkout';

  final Dio _dio;

  Future<CartDto> validate({required String country, String? email}) async {
    final response = await _dio.post<Object?>(
      '$base/validate',
      data: {'country': country, 'email': ?email},
    );
    return decodeEnvelope(
      response.data,
      (data) => CartDto.fromJson(objectAt(data, 'cart')),
    ).data;
  }

  Future<List<PaymentOptionDto>> paymentOptions({
    required String country,
    String? email,
  }) async {
    final response = await _dio.get<Object?>(
      '$base/payment-options',
      queryParameters: {'country': country, 'email': ?email},
    );
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'options', PaymentOptionDto.fromJson),
    ).data;
  }

  /// The key goes into `extra`, so the idempotency interceptor sends it and every automatic
  /// retry of this request re-sends the same key and body.
  Future<PlaceOrderEnvelope> placeOrder(
    Map<String, Object?> body, {
    required String idempotencyKey,
  }) async {
    final response = await _dio.post<Object?>(
      '$base/place-order',
      data: body,
      options: Options(extra: {RequestExtras.idempotencyKey: idempotencyKey}),
    );
    return PlaceOrderEnvelope(
      decodeEnvelope(
        response.data,
        (data) => PlaceOrderDataDto.fromJson(asJsonObject(data)),
      ),
      replayed: response.headers.value('idempotency-replayed') == 'true',
    );
  }

  Future<OrderPaymentStatusDto> status(String orderPublicId) async {
    final response = await _dio.get<Object?>(
      '$base/orders/$orderPublicId/status',
    );
    return decodeEnvelope(
      response.data,
      (data) => OrderPaymentStatusDto.fromJson(asJsonObject(data)),
    ).data;
  }

  Future<void> sendGuestOtp({
    required String orderPublicId,
    required String email,
  }) async {
    await _dio.post<Object?>(
      '$base/guest/otp/send',
      data: {'order_public_id': orderPublicId, 'email': email},
      options: Options(extra: {RequestExtras.noRetry: true}),
    );
  }

  Future<OtpVerifyDto> verifyGuestOtp({
    required String orderPublicId,
    required String code,
  }) async {
    final response = await _dio.post<Object?>(
      '$base/guest/otp/verify',
      data: {'order_public_id': orderPublicId, 'code': code},
      options: Options(extra: {RequestExtras.sensitive: true}),
    );
    return decodeEnvelope(
      response.data,
      (data) => OtpVerifyDto.fromJson(asJsonObject(data)),
    ).data;
  }

  /// The order id and the signed query (`expires`, `signature`) of the delivery link.
  Future<GuestAccessDto> openGuestLink(
    String orderPublicId,
    Map<String, String> query,
  ) async {
    final response = await _dio.get<Object?>(
      '$base/guest/orders/$orderPublicId/access',
      queryParameters: query,
      options: Options(extra: {RequestExtras.sensitive: true}),
    );
    return decodeEnvelope(
      response.data,
      (data) => GuestAccessDto.fromJson(asJsonObject(data)),
    ).data;
  }
}
