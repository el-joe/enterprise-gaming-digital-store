// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'order_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_OrderTotalsDto _$OrderTotalsDtoFromJson(Map<String, dynamic> json) =>
    _OrderTotalsDto(
      display: const MoneyConverter().fromJson(json['display']),
      payment: const NullableMoneyConverter().fromJson(json['payment']),
    );

Map<String, dynamic> _$OrderTotalsDtoToJson(_OrderTotalsDto instance) =>
    <String, dynamic>{
      'display': const MoneyConverter().toJson(instance.display),
      'payment': const NullableMoneyConverter().toJson(instance.payment),
    };

_OrderSummaryDto _$OrderSummaryDtoFromJson(Map<String, dynamic> json) =>
    _OrderSummaryDto(
      publicId: json['public_id'] as String,
      number: json['number'] as String,
      status: json['status'] as String,
      paymentStatus: json['payment_status'] as String,
      isFinal: json['is_final'] as bool,
      totals: OrderTotalsDto.fromJson(json['totals'] as Map<String, dynamic>),
      itemCount: (json['item_count'] as num).toInt(),
      createdAt: DateTime.parse(json['created_at'] as String),
      thumbnailUrl: json['thumbnail_url'] as String?,
      paidAt: json['paid_at'] == null
          ? null
          : DateTime.parse(json['paid_at'] as String),
    );

Map<String, dynamic> _$OrderSummaryDtoToJson(_OrderSummaryDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'number': instance.number,
      'status': instance.status,
      'payment_status': instance.paymentStatus,
      'is_final': instance.isFinal,
      'totals': instance.totals.toJson(),
      'item_count': instance.itemCount,
      'created_at': instance.createdAt.toIso8601String(),
      'thumbnail_url': instance.thumbnailUrl,
      'paid_at': instance.paidAt?.toIso8601String(),
    };

_OrderCurrencyDto _$OrderCurrencyDtoFromJson(Map<String, dynamic> json) =>
    _OrderCurrencyDto(
      display: json['display'] as String,
      payment: json['payment'] as String?,
    );

Map<String, dynamic> _$OrderCurrencyDtoToJson(_OrderCurrencyDto instance) =>
    <String, dynamic>{'display': instance.display, 'payment': instance.payment};

_OrderProductRefDto _$OrderProductRefDtoFromJson(Map<String, dynamic> json) =>
    _OrderProductRefDto(slug: json['slug'] as String?);

Map<String, dynamic> _$OrderProductRefDtoToJson(_OrderProductRefDto instance) =>
    <String, dynamic>{'slug': instance.slug};

_OrderUnitDto _$OrderUnitDtoFromJson(Map<String, dynamic> json) =>
    _OrderUnitDto(
      publicId: json['public_id'] as String,
      status: json['status'] as String,
      revealable: json['revealable'] as bool,
      deliveryType: json['delivery_type'] as String?,
      maskedCode: json['masked_code'] as String?,
      deliveredAt: json['delivered_at'] == null
          ? null
          : DateTime.parse(json['delivered_at'] as String),
      firstRevealedAt: json['first_revealed_at'] == null
          ? null
          : DateTime.parse(json['first_revealed_at'] as String),
      refundedAt: json['refunded_at'] == null
          ? null
          : DateTime.parse(json['refunded_at'] as String),
    );

Map<String, dynamic> _$OrderUnitDtoToJson(_OrderUnitDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'status': instance.status,
      'revealable': instance.revealable,
      'delivery_type': instance.deliveryType,
      'masked_code': instance.maskedCode,
      'delivered_at': instance.deliveredAt?.toIso8601String(),
      'first_revealed_at': instance.firstRevealedAt?.toIso8601String(),
      'refunded_at': instance.refundedAt?.toIso8601String(),
    };

_OrderItemDto _$OrderItemDtoFromJson(Map<String, dynamic> json) =>
    _OrderItemDto(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      quantity: (json['quantity'] as num).toInt(),
      units: (json['units'] as List<dynamic>)
          .map((e) => OrderUnitDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      product: json['product'] == null
          ? null
          : OrderProductRefDto.fromJson(
              json['product'] as Map<String, dynamic>,
            ),
      deliveryType: json['delivery_type'] as String?,
      unitPrice: const NullableMoneyConverter().fromJson(json['unit_price']),
      lineTotal: const NullableMoneyConverter().fromJson(json['line_total']),
    );

Map<String, dynamic> _$OrderItemDtoToJson(_OrderItemDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'quantity': instance.quantity,
      'units': instance.units.map((e) => e.toJson()).toList(),
      'product': instance.product?.toJson(),
      'delivery_type': instance.deliveryType,
      'unit_price': const NullableMoneyConverter().toJson(instance.unitPrice),
      'line_total': const NullableMoneyConverter().toJson(instance.lineTotal),
    };

_OrderPaymentDto _$OrderPaymentDtoFromJson(Map<String, dynamic> json) =>
    _OrderPaymentDto(
      publicId: json['public_id'] as String,
      attempt: (json['attempt'] as num).toInt(),
      status: json['status'] as String,
      amount: const MoneyConverter().fromJson(json['amount']),
      createdAt: DateTime.parse(json['created_at'] as String),
      provider: json['provider'] as String?,
      method: json['method'] as String?,
      failureCode: json['failure_code'] as String?,
    );

Map<String, dynamic> _$OrderPaymentDtoToJson(_OrderPaymentDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'attempt': instance.attempt,
      'status': instance.status,
      'amount': const MoneyConverter().toJson(instance.amount),
      'created_at': instance.createdAt.toIso8601String(),
      'provider': instance.provider,
      'method': instance.method,
      'failure_code': instance.failureCode,
    };

_OrderRefundDto _$OrderRefundDtoFromJson(Map<String, dynamic> json) =>
    _OrderRefundDto(
      publicId: json['public_id'] as String,
      status: json['status'] as String,
      destination: json['destination'] as String,
      amount: const MoneyConverter().fromJson(json['amount']),
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      completedAt: json['completed_at'] == null
          ? null
          : DateTime.parse(json['completed_at'] as String),
    );

Map<String, dynamic> _$OrderRefundDtoToJson(_OrderRefundDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'status': instance.status,
      'destination': instance.destination,
      'amount': const MoneyConverter().toJson(instance.amount),
      'created_at': instance.createdAt?.toIso8601String(),
      'completed_at': instance.completedAt?.toIso8601String(),
    };

_OrderTimelineDto _$OrderTimelineDtoFromJson(Map<String, dynamic> json) =>
    _OrderTimelineDto(
      status: json['status'] as String,
      at: json['at'] == null ? null : DateTime.parse(json['at'] as String),
    );

Map<String, dynamic> _$OrderTimelineDtoToJson(_OrderTimelineDto instance) =>
    <String, dynamic>{
      'status': instance.status,
      'at': instance.at?.toIso8601String(),
    };

_OrderDetailDto _$OrderDetailDtoFromJson(Map<String, dynamic> json) =>
    _OrderDetailDto(
      publicId: json['public_id'] as String,
      number: json['number'] as String,
      status: json['status'] as String,
      paymentStatus: json['payment_status'] as String,
      isFinal: json['is_final'] as bool,
      totals: OrderTotalsDto.fromJson(json['totals'] as Map<String, dynamic>),
      itemCount: (json['item_count'] as num).toInt(),
      createdAt: DateTime.parse(json['created_at'] as String),
      currency: OrderCurrencyDto.fromJson(
        json['currency'] as Map<String, dynamic>,
      ),
      items: (json['items'] as List<dynamic>)
          .map((e) => OrderItemDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      payments: (json['payments'] as List<dynamic>)
          .map((e) => OrderPaymentDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      refunds: (json['refunds'] as List<dynamic>)
          .map((e) => OrderRefundDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      refundState: json['refund_state'] as String,
      timeline: (json['timeline'] as List<dynamic>)
          .map((e) => OrderTimelineDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      thumbnailUrl: json['thumbnail_url'] as String?,
      paidAt: json['paid_at'] == null
          ? null
          : DateTime.parse(json['paid_at'] as String),
      completedAt: json['completed_at'] == null
          ? null
          : DateTime.parse(json['completed_at'] as String),
    );

Map<String, dynamic> _$OrderDetailDtoToJson(_OrderDetailDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'number': instance.number,
      'status': instance.status,
      'payment_status': instance.paymentStatus,
      'is_final': instance.isFinal,
      'totals': instance.totals.toJson(),
      'item_count': instance.itemCount,
      'created_at': instance.createdAt.toIso8601String(),
      'currency': instance.currency.toJson(),
      'items': instance.items.map((e) => e.toJson()).toList(),
      'payments': instance.payments.map((e) => e.toJson()).toList(),
      'refunds': instance.refunds.map((e) => e.toJson()).toList(),
      'refund_state': instance.refundState,
      'timeline': instance.timeline.map((e) => e.toJson()).toList(),
      'thumbnail_url': instance.thumbnailUrl,
      'paid_at': instance.paidAt?.toIso8601String(),
      'completed_at': instance.completedAt?.toIso8601String(),
    };
