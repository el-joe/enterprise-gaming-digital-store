import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:dio/dio.dart';

/// `/mobile/auth/biometric-*` (API_DOCUMENTATION.md 1g) and `confirm-password` (1c). Every call is
/// sensitive (kept out of debug logs) and never retried: challenges are single use.
class BiometricRemoteDataSource {
  BiometricRemoteDataSource(this._dio);

  final Dio _dio;

  static final _sensitive = Options(
    extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
    headers: {'Cache-Control': 'no-store'},
  );

  Future<void> registerKey(String publicKey) async {
    await _dio.post<Object?>(
      '/mobile/auth/biometric-key',
      data: {'public_key': publicKey, 'algorithm': 'ES256'},
      options: _sensitive,
    );
  }

  Future<void> deleteKey() async {
    await _dio.delete<Object?>(
      '/mobile/auth/biometric-key',
      options: _sensitive,
    );
  }

  Future<String> challenge() async {
    final response = await _dio.post<Object?>(
      '/mobile/auth/biometric-challenge',
      options: _sensitive,
    );
    return decodeEnvelope(response.data, (data) {
      final challenge = objectAt(data, 'challenge')['challenge'];
      if (challenge is! String || challenge.isEmpty) {
        throw const FormatException('challenge.challenge is not a string');
      }
      return challenge;
    }).data;
  }

  Future<void> confirm({
    required String challenge,
    required String signature,
  }) async {
    await _dio.post<Object?>(
      '/mobile/auth/confirm-biometric',
      data: {'challenge': challenge, 'signature': signature},
      options: _sensitive,
    );
  }

  Future<void> confirmPassword(String password) async {
    await _dio.post<Object?>(
      '/mobile/account/confirm-password',
      data: {'password': password},
      options: _sensitive,
    );
  }
}
