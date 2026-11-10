import 'package:application/core/network/result.dart';
import 'package:application/features/orders/data/datasources/orders_remote_data_source.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/domain/repositories/orders_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

class OrdersRepositoryImpl implements OrdersRepository {
  OrdersRepositoryImpl(this._remote);

  final OrdersRemoteDataSource _remote;

  @override
  Future<Result<Paged<OrderSummary>>> list(OrdersQuery query) =>
      guardRequest(() async {
        final envelope = await _remote.list(query);
        return Paged(
          items: [for (final dto in envelope.data) dto.toEntity()],
          page: pageInfoOf(envelope),
        );
      });

  @override
  Future<Result<OrderDetail>> detail(String orderPublicId) => guardRequest(
    () async => (await _remote.detail(orderPublicId)).toEntity(),
  );

  @override
  Future<Result<GuestOrderDetail>> guestDetail(String orderPublicId) =>
      guardRequest(() async {
        final envelope = await _remote.guestDetail(orderPublicId);
        final access = envelope.meta['guest_access'];
        if (access is! Map<String, dynamic>) {
          throw const FormatException('meta.guest_access is missing.');
        }
        return GuestOrderDetail(
          order: envelope.data.toEntity(),
          linkVerified: access['link_verified'] == true,
          otpVerified: access['otp_verified'] == true,
        );
      });
}
