// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'account_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_ProfileDto _$ProfileDtoFromJson(Map<String, dynamic> json) => _ProfileDto(
  publicId: json['public_id'] as String,
  name: json['name'] as String,
  email: json['email'] as String,
  emailVerified: json['email_verified'] as bool,
  twoFactorEnabled: json['two_factor_enabled'] as bool,
  createdAt: DateTime.parse(json['created_at'] as String),
  phone: json['phone'] as String?,
  locale: json['locale'] as String?,
  displayCurrency: json['display_currency'] as String?,
  countryCode: json['country_code'] as String?,
);

Map<String, dynamic> _$ProfileDtoToJson(_ProfileDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'name': instance.name,
      'email': instance.email,
      'email_verified': instance.emailVerified,
      'two_factor_enabled': instance.twoFactorEnabled,
      'created_at': instance.createdAt.toIso8601String(),
      'phone': instance.phone,
      'locale': instance.locale,
      'display_currency': instance.displayCurrency,
      'country_code': instance.countryCode,
    };

_SignedOutDto _$SignedOutDtoFromJson(Map<String, dynamic> json) =>
    _SignedOutDto(
      mobileFamilies: (json['mobile_families'] as num).toInt(),
      webSessions: (json['web_sessions'] as num).toInt(),
    );

Map<String, dynamic> _$SignedOutDtoToJson(_SignedOutDto instance) =>
    <String, dynamic>{
      'mobile_families': instance.mobileFamilies,
      'web_sessions': instance.webSessions,
    };

_DeviceDto _$DeviceDtoFromJson(Map<String, dynamic> json) => _DeviceDto(
  id: json['id'] as String,
  type: json['type'] as String,
  current: json['current'] as bool,
  deviceName: json['device_name'] as String?,
  userAgent: json['user_agent'] as String?,
  ip: json['ip'] as String?,
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
  lastUsedAt: json['last_used_at'] == null
      ? null
      : DateTime.parse(json['last_used_at'] as String),
  expiresAt: json['expires_at'] == null
      ? null
      : DateTime.parse(json['expires_at'] as String),
);

Map<String, dynamic> _$DeviceDtoToJson(_DeviceDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'type': instance.type,
      'current': instance.current,
      'device_name': instance.deviceName,
      'user_agent': instance.userAgent,
      'ip': instance.ip,
      'created_at': instance.createdAt?.toIso8601String(),
      'last_used_at': instance.lastUsedAt?.toIso8601String(),
      'expires_at': instance.expiresAt?.toIso8601String(),
    };
