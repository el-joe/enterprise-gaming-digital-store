// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'checkout_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_PaymentMethodDto _$PaymentMethodDtoFromJson(Map<String, dynamic> json) =>
    _PaymentMethodDto(
      code: json['code'] as String,
      label: json['label'] as String,
    );

Map<String, dynamic> _$PaymentMethodDtoToJson(_PaymentMethodDto instance) =>
    <String, dynamic>{'code': instance.code, 'label': instance.label};

_PaymentOptionDto _$PaymentOptionDtoFromJson(Map<String, dynamic> json) =>
    _PaymentOptionDto(
      gateway: json['gateway'] as String,
      label: json['label'] as String,
      methods: (json['methods'] as List<dynamic>)
          .map((e) => PaymentMethodDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      available: json['available'] as bool,
      paymentCurrency: json['payment_currency'] as String,
      paymentAmount: const MoneyConverter().fromJson(json['payment_amount']),
      displayCurrency: json['display_currency'] as String,
      displayAmount: const MoneyConverter().fromJson(json['display_amount']),
      notice: json['notice'] as String?,
    );

Map<String, dynamic> _$PaymentOptionDtoToJson(_PaymentOptionDto instance) =>
    <String, dynamic>{
      'gateway': instance.gateway,
      'label': instance.label,
      'methods': instance.methods.map((e) => e.toJson()).toList(),
      'available': instance.available,
      'payment_currency': instance.paymentCurrency,
      'payment_amount': const MoneyConverter().toJson(instance.paymentAmount),
      'display_currency': instance.displayCurrency,
      'display_amount': const MoneyConverter().toJson(instance.displayAmount),
      'notice': instance.notice,
    };

_PlacedOrderDto _$PlacedOrderDtoFromJson(
  Map<String, dynamic> json,
) => _PlacedOrderDto(
  publicId: json['public_id'] as String,
  status: json['status'] as String,
  orderNumber: json['order_number'] as String?,
  displayTotal: const NullableMoneyConverter().fromJson(json['display_total']),
  paymentTotal: const NullableMoneyConverter().fromJson(json['payment_total']),
  expiresAt: json['expires_at'] == null
      ? null
      : DateTime.parse(json['expires_at'] as String),
);

Map<String, dynamic> _$PlacedOrderDtoToJson(
  _PlacedOrderDto instance,
) => <String, dynamic>{
  'public_id': instance.publicId,
  'status': instance.status,
  'order_number': instance.orderNumber,
  'display_total': const NullableMoneyConverter().toJson(instance.displayTotal),
  'payment_total': const NullableMoneyConverter().toJson(instance.paymentTotal),
  'expires_at': instance.expiresAt?.toIso8601String(),
};

_PlaceOrderDataDto _$PlaceOrderDataDtoFromJson(Map<String, dynamic> json) =>
    _PlaceOrderDataDto(
      order: PlacedOrderDto.fromJson(json['order'] as Map<String, dynamic>),
      next: json['next'] == null
          ? null
          : PaymentNextDto.fromJson(json['next'] as Map<String, dynamic>),
      guestOrderToken: json['guest_order_token'] == null
          ? null
          : GuestOrderTokenDto.fromJson(
              json['guest_order_token'] as Map<String, dynamic>,
            ),
    );

Map<String, dynamic> _$PlaceOrderDataDtoToJson(_PlaceOrderDataDto instance) =>
    <String, dynamic>{
      'order': instance.order.toJson(),
      'next': instance.next?.toJson(),
      'guest_order_token': instance.guestOrderToken?.toJson(),
    };

_GuestAccessDto _$GuestAccessDtoFromJson(Map<String, dynamic> json) =>
    _GuestAccessDto(
      orderPublicId: json['order_public_id'] as String,
      otpRequired: json['otp_required'] as bool,
      guestOrderToken: json['guest_order_token'] == null
          ? null
          : GuestOrderTokenDto.fromJson(
              json['guest_order_token'] as Map<String, dynamic>,
            ),
    );

Map<String, dynamic> _$GuestAccessDtoToJson(_GuestAccessDto instance) =>
    <String, dynamic>{
      'order_public_id': instance.orderPublicId,
      'otp_required': instance.otpRequired,
      'guest_order_token': instance.guestOrderToken?.toJson(),
    };

_OtpVerifyDto _$OtpVerifyDtoFromJson(Map<String, dynamic> json) =>
    _OtpVerifyDto(
      orderPublicId: json['order_public_id'] as String,
      emailVerified: json['email_verified'] as bool,
      guestOrderToken: GuestOrderTokenDto.fromJson(
        json['guest_order_token'] as Map<String, dynamic>,
      ),
    );

Map<String, dynamic> _$OtpVerifyDtoToJson(_OtpVerifyDto instance) =>
    <String, dynamic>{
      'order_public_id': instance.orderPublicId,
      'email_verified': instance.emailVerified,
      'guest_order_token': instance.guestOrderToken.toJson(),
    };
