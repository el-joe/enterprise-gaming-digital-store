import 'package:application/features/account/domain/entities/account.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'account_models.freezed.dart';
part 'account_models.g.dart';

@freezed
abstract class ProfileDto with _$ProfileDto {
  const factory ProfileDto({
    required String publicId,
    required String name,
    required String email,
    required bool emailVerified,
    required bool twoFactorEnabled,
    required DateTime createdAt,
    String? phone,
    String? locale,
    String? displayCurrency,
    String? countryCode,
  }) = _ProfileDto;

  const ProfileDto._();

  factory ProfileDto.fromJson(Map<String, dynamic> json) =>
      _$ProfileDtoFromJson(json);

  Profile toEntity() => Profile(
    publicId: publicId,
    name: name,
    email: email,
    emailVerified: emailVerified,
    twoFactorEnabled: twoFactorEnabled,
    createdAt: createdAt,
    phone: phone,
    locale: locale,
    displayCurrency: displayCurrency,
    countryCode: countryCode,
  );
}

@freezed
abstract class SignedOutDto with _$SignedOutDto {
  const factory SignedOutDto({
    required int mobileFamilies,
    required int webSessions,
  }) = _SignedOutDto;

  factory SignedOutDto.fromJson(Map<String, dynamic> json) =>
      _$SignedOutDtoFromJson(json);
}

@freezed
abstract class DeviceDto with _$DeviceDto {
  const factory DeviceDto({
    required String id,
    required String type,
    required bool current,
    String? deviceName,
    String? userAgent,
    String? ip,
    DateTime? createdAt,
    DateTime? lastUsedAt,
    DateTime? expiresAt,
  }) = _DeviceDto;

  const DeviceDto._();

  factory DeviceDto.fromJson(Map<String, dynamic> json) =>
      _$DeviceDtoFromJson(json);

  Device toEntity() => Device(
    id: id,
    type: type,
    current: current,
    deviceName: deviceName,
    userAgent: userAgent,
    ip: ip,
    createdAt: createdAt,
    lastUsedAt: lastUsedAt,
    expiresAt: expiresAt,
  );
}
