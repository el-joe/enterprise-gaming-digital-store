import 'package:application/core/network/result.dart';
import 'package:application/features/support/data/datasources/support_remote_data_source.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/domain/repositories/support_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

class SupportRepositoryImpl implements SupportRepository {
  SupportRepositoryImpl(this._remote);

  final SupportRemoteDataSource _remote;

  @override
  Future<Result<Paged<SupportTicket>>> tickets({
    int page = 1,
    int perPage = 20,
  }) => guardRequest(() async {
    final envelope = await _remote.tickets(page: page, perPage: perPage);
    return Paged(
      items: [for (final dto in envelope.data) dto.toEntity()],
      page: pageInfoOf(envelope),
    );
  });

  @override
  Future<Result<SupportTicket>> ticket(String publicId) =>
      guardRequest(() async => (await _remote.ticket(publicId)).toEntity());

  @override
  Future<Result<SupportTicket>> openTicket({
    required String category,
    required String subject,
    required String body,
    String? orderPublicId,
  }) => guardRequest(
    () async => (await _remote.openTicket({
      'category': category,
      'subject': subject,
      'body': body,
      'order_public_id': ?orderPublicId,
    })).toEntity(),
  );

  @override
  Future<Result<SupportTicket>> reply(
    String publicId, {
    required String body,
  }) => guardRequest(
    () async => (await _remote.reply(publicId, body)).toEntity(),
  );

  @override
  Future<Result<Paged<RefundRequest>>> refundRequests({
    int page = 1,
    int perPage = 20,
  }) => guardRequest(() async {
    final envelope = await _remote.refundRequests(page: page, perPage: perPage);
    return Paged(
      items: [for (final dto in envelope.data) dto.toEntity()],
      page: pageInfoOf(envelope),
    );
  });

  @override
  Future<Result<RefundRequest>> requestRefund({
    required String orderPublicId,
    required String reason,
    String? supportTicketPublicId,
  }) => guardRequest(
    () async => (await _remote.requestRefund({
      'order_public_id': orderPublicId,
      'reason': reason,
      'support_ticket_public_id': ?supportTicketPublicId,
    })).toEntity(),
  );
}
