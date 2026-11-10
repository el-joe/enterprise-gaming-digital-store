import 'package:application/features/support/domain/entities/support.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'support_models.freezed.dart';
part 'support_models.g.dart';

@freezed
abstract class OrderRefDto with _$OrderRefDto {
  const factory OrderRefDto({String? publicId, String? orderNumber}) =
      _OrderRefDto;

  const OrderRefDto._();

  factory OrderRefDto.fromJson(Map<String, dynamic> json) =>
      _$OrderRefDtoFromJson(json);

  OrderRef toEntity() => OrderRef(publicId: publicId, orderNumber: orderNumber);
}

@freezed
abstract class TicketMessageDto with _$TicketMessageDto {
  const factory TicketMessageDto({
    required int id,
    required String from,
    required String body,
    required DateTime createdAt,
  }) = _TicketMessageDto;

  factory TicketMessageDto.fromJson(Map<String, dynamic> json) =>
      _$TicketMessageDtoFromJson(json);
}

@freezed
abstract class SupportTicketDto with _$SupportTicketDto {
  const factory SupportTicketDto({
    required String publicId,
    required String ticketNumber,
    required String category,
    required String subject,
    required String status,
    required DateTime createdAt,
    DateTime? lastActivityAt,
    OrderRefDto? order,
    @Default(<TicketMessageDto>[]) List<TicketMessageDto> messages,
  }) = _SupportTicketDto;

  const SupportTicketDto._();

  factory SupportTicketDto.fromJson(Map<String, dynamic> json) =>
      _$SupportTicketDtoFromJson(json);

  SupportTicket toEntity() => SupportTicket(
    publicId: publicId,
    ticketNumber: ticketNumber,
    category: category,
    subject: subject,
    status: status,
    createdAt: createdAt,
    lastActivityAt: lastActivityAt,
    order: order?.toEntity(),
    messages: [
      for (final message in messages)
        TicketMessage(
          id: message.id,
          from: message.from,
          body: message.body,
          createdAt: message.createdAt,
        ),
    ],
  );
}

@freezed
abstract class RefundRefDto with _$RefundRefDto {
  const factory RefundRefDto({
    required String publicId,
    required String status,
  }) = _RefundRefDto;

  factory RefundRefDto.fromJson(Map<String, dynamic> json) =>
      _$RefundRefDtoFromJson(json);
}

@freezed
abstract class RefundRequestDto with _$RefundRequestDto {
  const factory RefundRequestDto({
    required int id,
    required OrderRefDto order,
    required String reason,
    required String status,
    required DateTime createdAt,
    RefundRefDto? refund,
  }) = _RefundRequestDto;

  const RefundRequestDto._();

  factory RefundRequestDto.fromJson(Map<String, dynamic> json) =>
      _$RefundRequestDtoFromJson(json);

  RefundRequest toEntity() => RefundRequest(
    id: id,
    order: order.toEntity(),
    reason: reason,
    status: status,
    createdAt: createdAt,
    refund: refund == null
        ? null
        : RefundRef(publicId: refund!.publicId, status: refund!.status),
  );
}
