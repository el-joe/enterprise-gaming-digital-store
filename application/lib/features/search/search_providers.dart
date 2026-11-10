import 'package:application/core/network/dio_client.dart';
import 'package:application/features/search/data/datasources/search_remote_data_source.dart';
import 'package:application/features/search/data/repositories/search_repository_impl.dart';
import 'package:application/features/search/domain/repositories/search_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'search_providers.g.dart';

@Riverpod(keepAlive: true)
SearchRemoteDataSource searchRemoteDataSource(Ref ref) =>
    SearchRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
SearchRepository searchRepository(Ref ref) =>
    SearchRepositoryImpl(ref.watch(searchRemoteDataSourceProvider));
