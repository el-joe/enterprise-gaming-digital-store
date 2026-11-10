import 'package:freezed_annotation/freezed_annotation.dart';

part 'support.freezed.dart';

/// A support ticket (API_DOCUMENTATION.md 4b). [messages] is empty in lists; internal staff
/// notes are never sent.
@freezed
abstract class SupportTicket with _$SupportTicket {
  const factory SupportTicket({
    required String publicId,
    required String ticketNumber,

    /// `order`, `payment`, `refund`, `account`, `other`.
    required String category,
    required String subject,
    required String status,
    required DateTime createdAt,
    required List<TicketMessage> messages,
    DateTime? lastActivityAt,
    OrderRef? order,
  }) = _SupportTicket;
}

@freezed
abstract class TicketMessage with _$TicketMessage {
  const factory TicketMessage({
    required int id,

    /// `customer` or `staff`.
    required String from,
    required String body,
    required DateTime createdAt,
  }) = _TicketMessage;
}

@freezed
abstract class OrderRef with _$OrderRef {
  const factory OrderRef({String? publicId, String? orderNumber}) = _OrderRef;
}

@freezed
abstract class RefundRequest with _$RefundRequest {
  const factory RefundRequest({
    required int id,
    required OrderRef order,
    required String reason,
    required String status,
    required DateTime createdAt,
    RefundRef? refund,
  }) = _RefundRequest;
}

@freezed
abstract class RefundRef with _$RefundRef {
  const factory RefundRef({required String publicId, required String status}) =
      _RefundRef;
}
