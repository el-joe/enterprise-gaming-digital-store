// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'wallet_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_WalletDto _$WalletDtoFromJson(Map<String, dynamic> json) => _WalletDto(
  currency: json['currency'] as String,
  balance: const MoneyConverter().fromJson(json['balance']),
  status: json['status'] as String,
  topUpAvailable: json['top_up_available'] as bool,
);

Map<String, dynamic> _$WalletDtoToJson(_WalletDto instance) =>
    <String, dynamic>{
      'currency': instance.currency,
      'balance': const MoneyConverter().toJson(instance.balance),
      'status': instance.status,
      'top_up_available': instance.topUpAvailable,
    };

_WalletTransactionDto _$WalletTransactionDtoFromJson(
  Map<String, dynamic> json,
) => _WalletTransactionDto(
  id: (json['id'] as num).toInt(),
  type: json['type'] as String,
  amount: json['amount'] as String,
  balanceAfter: json['balance_after'] as String,
  createdAt: DateTime.parse(json['created_at'] as String),
  note: json['note'] as String?,
  order: json['order'] == null
      ? null
      : WalletOrderRefDto.fromJson(json['order'] as Map<String, dynamic>),
);

Map<String, dynamic> _$WalletTransactionDtoToJson(
  _WalletTransactionDto instance,
) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'amount': instance.amount,
  'balance_after': instance.balanceAfter,
  'created_at': instance.createdAt.toIso8601String(),
  'note': instance.note,
  'order': instance.order?.toJson(),
};

_WalletOrderRefDto _$WalletOrderRefDtoFromJson(Map<String, dynamic> json) =>
    _WalletOrderRefDto(
      publicId: json['public_id'] as String,
      orderNumber: json['order_number'] as String,
    );

Map<String, dynamic> _$WalletOrderRefDtoToJson(_WalletOrderRefDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'order_number': instance.orderNumber,
    };
