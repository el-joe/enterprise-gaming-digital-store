import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/auth/data/models/auth_models.dart';
import 'package:dio/dio.dart';

/// `/api/v1/mobile/auth/*` (API_DOCUMENTATION.md 1b). Every call is kept out of debug logs.
class AuthRemoteDataSource {
  AuthRemoteDataSource(this._dio);

  final Dio _dio;

  static final _anonymous = Options(
    extra: {
      RequestExtras.authenticate: false,
      RequestExtras.sensitive: true,
      RequestExtras.noRetry: true,
    },
  );

  static final _bearer = Options(
    extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
  );

  Future<AuthSessionDto> register(Map<String, Object?> body) async {
    final response = await _dio.post<Object?>(
      '/mobile/auth/register',
      data: body,
      options: _anonymous,
    );
    return decodeEnvelope(
      response.data,
      (data) => AuthSessionDto.fromJson(asJsonObject(data)),
    ).data;
  }

  Future<LoginResponseDto> login({
    required String email,
    required String password,
    required String deviceName,
  }) async {
    final response = await _dio.post<Object?>(
      '/mobile/auth/login',
      data: {'email': email, 'password': password, 'device_name': deviceName},
      options: _anonymous,
    );
    return decodeEnvelope(
      response.data,
      (data) => LoginResponseDto.fromJson(asJsonObject(data)),
    ).data;
  }

  Future<AuthSessionDto> twoFactorChallenge({
    required String challengeToken,
    String? code,
    String? recoveryCode,
  }) async {
    final response = await _dio.post<Object?>(
      '/mobile/auth/two-factor-challenge',
      data: {
        'challenge_token': challengeToken,
        'code': ?code,
        'recovery_code': ?recoveryCode,
      },
      options: _anonymous,
    );
    return decodeEnvelope(
      response.data,
      (data) => AuthSessionDto.fromJson(asJsonObject(data)),
    ).data;
  }

  Future<UserDto> me() async {
    final response = await _dio.get<Object?>('/mobile/auth/me');
    return decodeEnvelope(
      response.data,
      (data) => UserDto.fromJson(objectAt(data, 'user')),
    ).data;
  }

  Future<void> logout() async {
    await _dio.post<Object?>('/mobile/auth/logout', options: _bearer);
  }

  Future<void> logoutAll() async {
    await _dio.post<Object?>('/mobile/auth/logout-all', options: _bearer);
  }
}
