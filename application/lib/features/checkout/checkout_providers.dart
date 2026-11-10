import 'package:application/core/network/dio_client.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/core/utils/ulid.dart';
import 'package:application/features/checkout/data/datasources/checkout_remote_data_source.dart';
import 'package:application/features/checkout/data/repositories/checkout_repository_impl.dart';
import 'package:application/features/checkout/domain/repositories/checkout_repository.dart';
import 'package:application/features/checkout/domain/usecases/place_order.dart';
import 'package:application/features/payments/payments_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'checkout_providers.g.dart';

@Riverpod(keepAlive: true)
CheckoutRemoteDataSource checkoutRemoteDataSource(Ref ref) =>
    CheckoutRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
CheckoutRepository checkoutRepository(Ref ref) => CheckoutRepositoryImpl(
  ref.watch(checkoutRemoteDataSourceProvider),
  ref.watch(secureStoreProvider),
);

@Riverpod(keepAlive: true)
PendingCheckoutStore pendingCheckoutStore(Ref ref) =>
    SecurePendingCheckoutStore(ref.watch(secureStoreProvider));

@Riverpod(keepAlive: true)
PlaceOrder placeOrder(Ref ref) => PlaceOrder(
  checkout: ref.watch(checkoutRepositoryProvider),
  payments: ref.watch(paymentsRepositoryProvider),
  pending: ref.watch(pendingCheckoutStoreProvider),
  newKey: newUlid,
);
