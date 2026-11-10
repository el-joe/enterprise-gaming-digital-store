import 'package:application/core/network/dio_client.dart';
import 'package:application/features/app_config/data/datasources/app_config_remote_data_source.dart';
import 'package:application/features/app_config/data/repositories/app_config_repository_impl.dart';
import 'package:application/features/app_config/domain/repositories/app_config_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_config_providers.g.dart';

@Riverpod(keepAlive: true)
AppConfigRemoteDataSource appConfigRemoteDataSource(Ref ref) =>
    AppConfigRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
AppConfigRepository appConfigRepository(Ref ref) =>
    AppConfigRepositoryImpl(ref.watch(appConfigRemoteDataSourceProvider));
