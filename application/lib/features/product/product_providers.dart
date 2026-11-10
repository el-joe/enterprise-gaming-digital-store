import 'package:application/core/network/dio_client.dart';
import 'package:application/features/product/data/datasources/product_remote_data_source.dart';
import 'package:application/features/product/data/repositories/product_repository_impl.dart';
import 'package:application/features/product/domain/repositories/product_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'product_providers.g.dart';

@Riverpod(keepAlive: true)
ProductRemoteDataSource productRemoteDataSource(Ref ref) =>
    ProductRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
ProductRepository productRepository(Ref ref) =>
    ProductRepositoryImpl(ref.watch(productRemoteDataSourceProvider));
