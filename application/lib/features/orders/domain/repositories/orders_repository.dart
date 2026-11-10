import 'package:application/core/network/result.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

abstract interface class OrdersRepository {
  Future<Result<Paged<OrderSummary>>> list(OrdersQuery query);

  /// 404 for another customer's order, a guest order or an unknown id.
  Future<Result<OrderDetail>> detail(String orderPublicId);

  /// The guest detail with the stored guest order token (placement or OTP proof).
  Future<Result<GuestOrderDetail>> guestDetail(String orderPublicId);
}
