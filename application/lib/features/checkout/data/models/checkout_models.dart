import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/shared/data/models/payment_models.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'checkout_models.freezed.dart';
part 'checkout_models.g.dart';

@freezed
abstract class PaymentMethodDto with _$PaymentMethodDto {
  const factory PaymentMethodDto({
    required String code,
    required String label,
  }) = _PaymentMethodDto;

  factory PaymentMethodDto.fromJson(Map<String, dynamic> json) =>
      _$PaymentMethodDtoFromJson(json);
}

@freezed
abstract class PaymentOptionDto with _$PaymentOptionDto {
  const factory PaymentOptionDto({
    required String gateway,
    required String label,
    required List<PaymentMethodDto> methods,
    required bool available,
    required String paymentCurrency,
    @MoneyConverter() required Money paymentAmount,
    required String displayCurrency,
    @MoneyConverter() required Money displayAmount,
    String? notice,
  }) = _PaymentOptionDto;

  const PaymentOptionDto._();

  factory PaymentOptionDto.fromJson(Map<String, dynamic> json) =>
      _$PaymentOptionDtoFromJson(json);

  PaymentOption toEntity() => PaymentOption(
    gateway: gateway,
    label: label,
    methods: [
      for (final method in methods)
        PaymentMethodOption(code: method.code, label: method.label),
    ],
    available: available,
    paymentCurrency: paymentCurrency,
    paymentAmount: paymentAmount,
    displayCurrency: displayCurrency,
    displayAmount: displayAmount,
    notice: notice,
  );
}

@freezed
abstract class PlacedOrderDto with _$PlacedOrderDto {
  const factory PlacedOrderDto({
    required String publicId,
    required String status,
    String? orderNumber,
    @NullableMoneyConverter() Money? displayTotal,
    @NullableMoneyConverter() Money? paymentTotal,
    DateTime? expiresAt,
  }) = _PlacedOrderDto;

  const PlacedOrderDto._();

  factory PlacedOrderDto.fromJson(Map<String, dynamic> json) =>
      _$PlacedOrderDtoFromJson(json);

  PlacedOrder toEntity() => PlacedOrder(
    publicId: publicId,
    status: status,
    orderNumber: orderNumber,
    displayTotal: displayTotal,
    paymentTotal: paymentTotal,
    expiresAt: expiresAt,
  );
}

/// `data` of place-order: `{ order, next, guest_order_token? }`.
@freezed
abstract class PlaceOrderDataDto with _$PlaceOrderDataDto {
  const factory PlaceOrderDataDto({
    required PlacedOrderDto order,
    PaymentNextDto? next,
    GuestOrderTokenDto? guestOrderToken,
  }) = _PlaceOrderDataDto;

  factory PlaceOrderDataDto.fromJson(Map<String, dynamic> json) =>
      _$PlaceOrderDataDtoFromJson(json);
}

/// `data` of the signed link: `{ order_public_id, otp_required, guest_order_token }`.
@freezed
abstract class GuestAccessDto with _$GuestAccessDto {
  const factory GuestAccessDto({
    required String orderPublicId,
    required bool otpRequired,
    GuestOrderTokenDto? guestOrderToken,
  }) = _GuestAccessDto;

  factory GuestAccessDto.fromJson(Map<String, dynamic> json) =>
      _$GuestAccessDtoFromJson(json);
}

/// `data` of OTP verify: `{ order_public_id, email_verified, guest_order_token }`.
@freezed
abstract class OtpVerifyDto with _$OtpVerifyDto {
  const factory OtpVerifyDto({
    required String orderPublicId,
    required bool emailVerified,
    required GuestOrderTokenDto guestOrderToken,
  }) = _OtpVerifyDto;

  factory OtpVerifyDto.fromJson(Map<String, dynamic> json) =>
      _$OtpVerifyDtoFromJson(json);
}
