import 'package:application/core/network/api_decoding.dart';
import 'package:application/features/app_config/data/models/app_config_models.dart';
import 'package:dio/dio.dart';

/// `GET /api/v1/mobile/app/config` (API_DOCUMENTATION.md 1d).
class AppConfigRemoteDataSource {
  AppConfigRemoteDataSource(this._dio);

  static const path = '/mobile/app/config';

  final Dio _dio;

  Future<AppConfigDto> fetch({String? platform, String? version}) async {
    final response = await _dio.get<Object?>(
      path,
      queryParameters: {
        if (platform != null && version != null) ...{
          'platform': platform,
          'version': version,
        },
      },
    );
    return decodeEnvelope(
      response.data,
      (data) => AppConfigDto.fromJson(objectAt(data, 'config')),
    ).data;
  }
}
