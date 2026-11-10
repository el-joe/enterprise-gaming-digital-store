import 'package:application/core/network/result.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';

abstract interface class AppConfigRepository {
  /// Fetches the configuration; with [platform] (`android` or `ios`) and [version] (SemVer) the
  /// result carries the server's update verdict.
  Future<Result<AppConfig>> fetch({String? platform, String? version});
}
