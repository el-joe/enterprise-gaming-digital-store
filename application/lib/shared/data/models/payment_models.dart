import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'payment_models.freezed.dart';
part 'payment_models.g.dart';

/// `next` of place-order and retry-payment; fields depend on [type].
@freezed
abstract class PaymentNextDto with _$PaymentNextDto {
  const factory PaymentNextDto({
    required String type,
    required String gateway,
    String? paymentPublicId,
    String? clientSecret,
    String? returnUrl,
    String? providerOrderId,
    String? approveUrl,
    String? redirectUrl,
  }) = _PaymentNextDto;

  const PaymentNextDto._();

  factory PaymentNextDto.fromJson(Map<String, dynamic> json) =>
      _$PaymentNextDtoFromJson(json);

  PaymentNext toEntity() {
    String need(String? value, String key) =>
        value ?? (throw FormatException('next.$key is missing for $type.'));
    return switch (type) {
      'stripe' => PaymentNext.stripe(
        paymentPublicId: need(paymentPublicId, 'payment_public_id'),
        gateway: gateway,
        clientSecret: need(clientSecret, 'client_secret'),
        returnUrl: returnUrl == null ? null : Uri.parse(returnUrl!),
      ),
      'paypal' => PaymentNext.paypal(
        paymentPublicId: need(paymentPublicId, 'payment_public_id'),
        gateway: gateway,
        providerOrderId: need(providerOrderId, 'provider_order_id'),
        approveUrl: Uri.parse(need(approveUrl, 'approve_url')),
      ),
      'redirect' => PaymentNext.redirect(
        paymentPublicId: need(paymentPublicId, 'payment_public_id'),
        gateway: gateway,
        redirectUrl: Uri.parse(need(redirectUrl, 'redirect_url')),
      ),
      'wallet' => PaymentNext.wallet(
        paymentPublicId: need(paymentPublicId, 'payment_public_id'),
        gateway: gateway,
      ),
      _ => PaymentNext.unknown(type: type, gateway: gateway),
    };
  }

  @override
  String toString() => 'PaymentNextDto($type, $gateway)';
}

@freezed
abstract class OrderPaymentStatusDto with _$OrderPaymentStatusDto {
  const factory OrderPaymentStatusDto({
    required String publicId,
    required String status,
    required String paymentStatus,
    required bool isFinal,
  }) = _OrderPaymentStatusDto;

  const OrderPaymentStatusDto._();

  factory OrderPaymentStatusDto.fromJson(Map<String, dynamic> json) =>
      _$OrderPaymentStatusDtoFromJson(json);

  OrderPaymentStatus toEntity() => OrderPaymentStatus(
    publicId: publicId,
    status: status,
    paymentStatus: paymentStatus,
    isFinal: isFinal,
  );
}

@freezed
abstract class GuestOrderTokenDto with _$GuestOrderTokenDto {
  const factory GuestOrderTokenDto({
    required String token,
    required String orderPublicId,
    required DateTime expiresAt,
  }) = _GuestOrderTokenDto;

  const GuestOrderTokenDto._();

  factory GuestOrderTokenDto.fromJson(Map<String, dynamic> json) =>
      _$GuestOrderTokenDtoFromJson(json);

  GuestOrderToken toEntity() => GuestOrderToken(
    token: token,
    orderPublicId: orderPublicId,
    expiresAt: expiresAt,
  );

  @override
  String toString() => 'GuestOrderTokenDto($orderPublicId, <redacted>)';
}
