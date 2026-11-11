import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/account/data/models/account_models.dart';
import 'package:dio/dio.dart';

/// Profile, password, devices and the verification email under `/api/v1/mobile/account`
/// (API_DOCUMENTATION.md 4c).
class AccountRemoteDataSource {
  AccountRemoteDataSource(this._dio);

  static const base = '/mobile/account';

  final Dio _dio;

  static ProfileDto _profile(Object? data) =>
      ProfileDto.fromJson(objectAt(data, 'profile'));

  Future<ProfileDto> profile() async {
    final response = await _dio.get<Object?>('$base/profile');
    return decodeEnvelope(response.data, _profile).data;
  }

  Future<ProfileDto> updateProfile(Map<String, Object?> body) async {
    final response = await _dio.patch<Object?>('$base/profile', data: body);
    return decodeEnvelope(response.data, _profile).data;
  }

  Future<SignedOutDto> changePassword(Map<String, Object?> body) async {
    final response = await _dio.put<Object?>(
      '$base/password',
      data: body,
      options: Options(
        extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
      ),
    );
    return decodeEnvelope(
      response.data,
      (data) => SignedOutDto.fromJson(objectAt(data, 'signed_out')),
    ).data;
  }

  Future<ApiEnvelope<List<DeviceDto>>> devices() async {
    final response = await _dio.get<Object?>('$base/devices');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'devices', DeviceDto.fromJson),
    );
  }

  Future<void> revokeDevice(String id) async {
    await _dio.delete<Object?>('$base/devices/${Uri.encodeComponent(id)}');
  }

  /// `POST /devices/sign-out-others` (4d): every other sign-in ends; this one stays.
  Future<SignedOutDto> signOutOthers(String currentPassword) async {
    final response = await _dio.post<Object?>(
      '$base/devices/sign-out-others',
      data: {'current_password': currentPassword},
      options: Options(
        extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
      ),
    );
    return decodeEnvelope(
      response.data,
      (data) => SignedOutDto.fromJson(objectAt(data, 'signed_out')),
    ).data;
  }

  Future<void> resendVerificationEmail() async {
    await _dio.post<Object?>(
      '$base/email/verification-notification',
      options: Options(extra: {RequestExtras.noRetry: true}),
    );
  }
}
