import 'package:application/core/network/dio_client.dart';
import 'package:application/features/catalog/data/datasources/catalog_remote_data_source.dart';
import 'package:application/features/catalog/data/repositories/catalog_repository_impl.dart';
import 'package:application/features/catalog/domain/repositories/catalog_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'catalog_providers.g.dart';

@Riverpod(keepAlive: true)
CatalogRemoteDataSource catalogRemoteDataSource(Ref ref) =>
    CatalogRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
CatalogRepository catalogRepository(Ref ref) =>
    CatalogRepositoryImpl(ref.watch(catalogRemoteDataSourceProvider));
