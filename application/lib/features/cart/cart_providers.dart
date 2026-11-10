import 'package:application/core/network/dio_client.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/data/datasources/cart_remote_data_source.dart';
import 'package:application/features/cart/data/repositories/cart_repository_impl.dart';
import 'package:application/features/cart/domain/repositories/cart_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'cart_providers.g.dart';

@Riverpod(keepAlive: true)
CartRemoteDataSource cartRemoteDataSource(Ref ref) =>
    CartRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
CartRepository cartRepository(Ref ref) => CartRepositoryImpl(
  ref.watch(cartRemoteDataSourceProvider),
  ref.watch(secureStoreProvider),
);
