// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'payment_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_PaymentNextDto _$PaymentNextDtoFromJson(Map<String, dynamic> json) =>
    _PaymentNextDto(
      type: json['type'] as String,
      gateway: json['gateway'] as String,
      paymentPublicId: json['payment_public_id'] as String?,
      clientSecret: json['client_secret'] as String?,
      returnUrl: json['return_url'] as String?,
      providerOrderId: json['provider_order_id'] as String?,
      approveUrl: json['approve_url'] as String?,
      redirectUrl: json['redirect_url'] as String?,
    );

Map<String, dynamic> _$PaymentNextDtoToJson(_PaymentNextDto instance) =>
    <String, dynamic>{
      'type': instance.type,
      'gateway': instance.gateway,
      'payment_public_id': instance.paymentPublicId,
      'client_secret': instance.clientSecret,
      'return_url': instance.returnUrl,
      'provider_order_id': instance.providerOrderId,
      'approve_url': instance.approveUrl,
      'redirect_url': instance.redirectUrl,
    };

_OrderPaymentStatusDto _$OrderPaymentStatusDtoFromJson(
  Map<String, dynamic> json,
) => _OrderPaymentStatusDto(
  publicId: json['public_id'] as String,
  status: json['status'] as String,
  paymentStatus: json['payment_status'] as String,
  isFinal: json['is_final'] as bool,
);

Map<String, dynamic> _$OrderPaymentStatusDtoToJson(
  _OrderPaymentStatusDto instance,
) => <String, dynamic>{
  'public_id': instance.publicId,
  'status': instance.status,
  'payment_status': instance.paymentStatus,
  'is_final': instance.isFinal,
};

_GuestOrderTokenDto _$GuestOrderTokenDtoFromJson(Map<String, dynamic> json) =>
    _GuestOrderTokenDto(
      token: json['token'] as String,
      orderPublicId: json['order_public_id'] as String,
      expiresAt: DateTime.parse(json['expires_at'] as String),
    );

Map<String, dynamic> _$GuestOrderTokenDtoToJson(_GuestOrderTokenDto instance) =>
    <String, dynamic>{
      'token': instance.token,
      'order_public_id': instance.orderPublicId,
      'expires_at': instance.expiresAt.toIso8601String(),
    };
