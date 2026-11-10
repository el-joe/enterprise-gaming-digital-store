import 'package:application/core/network/interceptors/auth_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';

/// `POST /api/v1/mobile/auth/refresh` (API_DOCUMENTATION.md 1b) for the auth interceptor.
///
/// Runs on its own client without the auth interceptor, so a refresh never triggers another
/// refresh. Returns the new pair, or null when the server refuses the refresh token (401
/// `refresh_token_invalid`: unknown, expired, revoked or reused, which revokes the whole sign-in;
/// any other 4xx is treated the same, the session cannot continue). Transport errors, 429 and 5xx
/// are thrown so the interceptor keeps the session and surfaces the original failure.
final class MobileTokenRefresher {
  MobileTokenRefresher(this._dio);

  static const path = '/mobile/auth/refresh';

  final Dio _dio;

  Future<AuthTokens?> call(String refreshToken) async {
    try {
      final response = await _dio.post<Object?>(
        path,
        data: {'refresh_token': refreshToken},
        options: Options(
          extra: {
            RequestExtras.authenticate: false,
            RequestExtras.noRetry: true,
            RequestExtras.sensitive: true,
          },
        ),
      );
      return parseTokens(response.data);
    } on DioException catch (error) {
      final status = error.response?.statusCode;
      if (status != null && status >= 400 && status < 500 && status != 429) {
        return null;
      }
      rethrow;
    }
  }

  /// Reads `data.tokens` of a success envelope. Throws [FormatException] for any other shape.
  static AuthTokens parseTokens(Object? body) {
    if (body is Map<String, dynamic> && body['success'] == true) {
      final data = body['data'];
      final tokens = data is Map<String, dynamic> ? data['tokens'] : null;
      if (tokens is Map<String, dynamic> &&
          tokens['access_token'] is String &&
          tokens['refresh_token'] is String) {
        return AuthTokens(
          accessToken: tokens['access_token'] as String,
          refreshToken: tokens['refresh_token'] as String,
        );
      }
    }
    throw const FormatException('Refresh response has no token pair.');
  }

  TokenRefresher asRefresher() => call;
}
