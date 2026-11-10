// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'support_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_OrderRefDto _$OrderRefDtoFromJson(Map<String, dynamic> json) => _OrderRefDto(
  publicId: json['public_id'] as String?,
  orderNumber: json['order_number'] as String?,
);

Map<String, dynamic> _$OrderRefDtoToJson(_OrderRefDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'order_number': instance.orderNumber,
    };

_TicketMessageDto _$TicketMessageDtoFromJson(Map<String, dynamic> json) =>
    _TicketMessageDto(
      id: (json['id'] as num).toInt(),
      from: json['from'] as String,
      body: json['body'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
    );

Map<String, dynamic> _$TicketMessageDtoToJson(_TicketMessageDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'from': instance.from,
      'body': instance.body,
      'created_at': instance.createdAt.toIso8601String(),
    };

_SupportTicketDto _$SupportTicketDtoFromJson(Map<String, dynamic> json) =>
    _SupportTicketDto(
      publicId: json['public_id'] as String,
      ticketNumber: json['ticket_number'] as String,
      category: json['category'] as String,
      subject: json['subject'] as String,
      status: json['status'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      lastActivityAt: json['last_activity_at'] == null
          ? null
          : DateTime.parse(json['last_activity_at'] as String),
      order: json['order'] == null
          ? null
          : OrderRefDto.fromJson(json['order'] as Map<String, dynamic>),
      messages:
          (json['messages'] as List<dynamic>?)
              ?.map((e) => TicketMessageDto.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <TicketMessageDto>[],
    );

Map<String, dynamic> _$SupportTicketDtoToJson(_SupportTicketDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'ticket_number': instance.ticketNumber,
      'category': instance.category,
      'subject': instance.subject,
      'status': instance.status,
      'created_at': instance.createdAt.toIso8601String(),
      'last_activity_at': instance.lastActivityAt?.toIso8601String(),
      'order': instance.order?.toJson(),
      'messages': instance.messages.map((e) => e.toJson()).toList(),
    };

_RefundRefDto _$RefundRefDtoFromJson(Map<String, dynamic> json) =>
    _RefundRefDto(
      publicId: json['public_id'] as String,
      status: json['status'] as String,
    );

Map<String, dynamic> _$RefundRefDtoToJson(_RefundRefDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'status': instance.status,
    };

_RefundRequestDto _$RefundRequestDtoFromJson(Map<String, dynamic> json) =>
    _RefundRequestDto(
      id: (json['id'] as num).toInt(),
      order: OrderRefDto.fromJson(json['order'] as Map<String, dynamic>),
      reason: json['reason'] as String,
      status: json['status'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      refund: json['refund'] == null
          ? null
          : RefundRefDto.fromJson(json['refund'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$RefundRequestDtoToJson(_RefundRequestDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'order': instance.order.toJson(),
      'reason': instance.reason,
      'status': instance.status,
      'created_at': instance.createdAt.toIso8601String(),
      'refund': instance.refund?.toJson(),
    };
