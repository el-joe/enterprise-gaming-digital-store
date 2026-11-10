import 'package:application/core/network/result.dart';
import 'package:application/features/app_config/data/datasources/app_config_remote_data_source.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/app_config/domain/repositories/app_config_repository.dart';

class AppConfigRepositoryImpl implements AppConfigRepository {
  AppConfigRepositoryImpl(this._remote);

  final AppConfigRemoteDataSource _remote;

  @override
  Future<Result<AppConfig>> fetch({String? platform, String? version}) =>
      guardRequest(() async {
        final dto = await _remote.fetch(platform: platform, version: version);
        return dto.toEntity();
      });
}
