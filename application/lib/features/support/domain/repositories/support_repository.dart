import 'package:application/core/network/result.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

abstract interface class SupportRepository {
  Future<Result<Paged<SupportTicket>>> tickets({
    int page = 1,
    int perPage = 20,
  });

  Future<Result<SupportTicket>> ticket(String publicId);

  Future<Result<SupportTicket>> openTicket({
    required String category,
    required String subject,
    required String body,
    String? orderPublicId,
  });

  /// 409 `ticket_closed`.
  Future<Result<SupportTicket>> reply(String publicId, {required String body});

  Future<Result<Paged<RefundRequest>>> refundRequests({
    int page = 1,
    int perPage = 20,
  });

  /// 409 `refund_request_open` (one open request per order), `refund_not_allowed`.
  Future<Result<RefundRequest>> requestRefund({
    required String orderPublicId,
    required String reason,
    String? supportTicketPublicId,
  });
}
