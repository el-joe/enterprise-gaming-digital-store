import 'package:application/core/network/dio_client.dart';
import 'package:application/features/home/data/datasources/home_remote_data_source.dart';
import 'package:application/features/home/data/repositories/home_repository_impl.dart';
import 'package:application/features/home/domain/repositories/home_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'home_providers.g.dart';

@Riverpod(keepAlive: true)
HomeRemoteDataSource homeRemoteDataSource(Ref ref) =>
    HomeRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
HomeRepository homeRepository(Ref ref) =>
    HomeRepositoryImpl(ref.watch(homeRemoteDataSourceProvider));
