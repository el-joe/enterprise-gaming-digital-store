import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/push/data/models/push_models.dart';
import 'package:dio/dio.dart';

/// This device's push token under `/api/v1/mobile` (API_DOCUMENTATION.md 1h, M-A18). The token
/// is kept out of debug logs.
class PushRemoteDataSource {
  PushRemoteDataSource(this._dio);

  static const path = '/mobile/push-token';

  static final _sensitive = Options(extra: {RequestExtras.sensitive: true});

  final Dio _dio;

  Future<PushTokenDto> register({
    required String token,
    required String platform,
  }) async {
    final response = await _dio.post<Object?>(
      path,
      data: {'token': token, 'platform': platform},
      options: _sensitive,
    );
    return decodeEnvelope(
      response.data,
      (data) => PushTokenDto.fromJson(objectAt(data, 'push_token')),
    ).data;
  }

  Future<void> unregister() async {
    await _dio.delete<Object?>(path, options: _sensitive);
  }
}
