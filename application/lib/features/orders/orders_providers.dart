import 'package:application/core/network/dio_client.dart';
import 'package:application/features/orders/data/datasources/orders_remote_data_source.dart';
import 'package:application/features/orders/data/repositories/orders_repository_impl.dart';
import 'package:application/features/orders/domain/repositories/orders_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'orders_providers.g.dart';

@Riverpod(keepAlive: true)
OrdersRemoteDataSource ordersRemoteDataSource(Ref ref) =>
    OrdersRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
OrdersRepository ordersRepository(Ref ref) =>
    OrdersRepositoryImpl(ref.watch(ordersRemoteDataSourceProvider));
