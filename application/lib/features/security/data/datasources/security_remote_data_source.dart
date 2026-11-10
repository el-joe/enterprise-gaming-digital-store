import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/security/data/models/two_factor_models.dart';
import 'package:application/features/security/domain/entities/two_factor.dart';
import 'package:dio/dio.dart';

/// `/api/v1/mobile/account/security/two-factor` (API_DOCUMENTATION.md 4).
class SecurityRemoteDataSource {
  SecurityRemoteDataSource(this._dio);

  static const path = '/mobile/account/security/two-factor';

  final Dio _dio;

  static final _sensitive = Options(
    extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
  );

  Future<TwoFactorEnrollment> enable(String currentPassword) async {
    final response = await _dio.post<Object?>(
      path,
      data: {'current_password': currentPassword},
      options: _sensitive,
    );
    return decodeEnvelope(
      response.data,
      (data) => TwoFactorModels.enrollment(asJsonObject(data)),
    ).data;
  }

  Future<RecoveryCodes> confirm(String code) async {
    final response = await _dio.post<Object?>(
      '$path/confirm',
      data: {'code': code},
      options: _sensitive,
    );
    return decodeEnvelope(
      response.data,
      (data) => TwoFactorModels.recoveryCodes(asJsonObject(data)),
    ).data;
  }

  Future<void> disable(String currentPassword) async {
    await _dio.delete<Object?>(
      path,
      data: {'current_password': currentPassword},
      options: _sensitive,
    );
  }
}
