import 'package:application/core/network/dio_client.dart';
import 'package:application/features/app_config/data/datasources/app_config_remote_data_source.dart';
import 'package:application/features/app_config/data/repositories/app_config_repository_impl.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/app_config/domain/repositories/app_config_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_config_providers.g.dart';

@Riverpod(keepAlive: true)
AppConfigRemoteDataSource appConfigRemoteDataSource(Ref ref) =>
    AppConfigRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
AppConfigRepository appConfigRepository(Ref ref) =>
    AppConfigRepositoryImpl(ref.watch(appConfigRemoteDataSourceProvider));

/// The last configuration the launch check received (null before it). Other features read links
/// and currencies from here (for example the forgot-password page and onboarding).
@Riverpod(keepAlive: true)
class LatestAppConfig extends _$LatestAppConfig {
  @override
  AppConfig? build() => null;

  void set(AppConfig config) => state = config;
}
