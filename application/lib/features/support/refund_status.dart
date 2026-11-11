import 'package:application/core/router/guards.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/support_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'refund_status.g.dart';

/// Statuses of a refund request that is still open (one open request per order: the server
/// answers `refund_request_open` to another one).
const openRefundRequestStatuses = {'requested', 'approved'};

/// The support feature's public entry for the order detail: the customer's latest refund
/// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
/// first 50; the order detail itself carries no request). Null when there is none, signed out,
/// or when the list could not be read (the order detail then shows no request).
@riverpod
Future<RefundRequest?> orderRefundRequest(Ref ref, String orderPublicId) async {
  if (ref.watch(sessionProvider) != SessionStatus.signedIn) {
    return null;
  }
  final result = await ref
      .read(supportRepositoryProvider)
      .refundRequests(perPage: 50);
  for (final request in result.valueOrNull?.items ?? const <RefundRequest>[]) {
    if (request.order.publicId == orderPublicId) {
      return request;
    }
  }
  return null;
}
