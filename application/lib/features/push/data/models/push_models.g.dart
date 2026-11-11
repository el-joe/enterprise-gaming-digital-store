// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'push_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_PushTokenDto _$PushTokenDtoFromJson(Map<String, dynamic> json) =>
    _PushTokenDto(
      platform: json['platform'] as String,
      registeredAt: DateTime.parse(json['registered_at'] as String),
    );

Map<String, dynamic> _$PushTokenDtoToJson(_PushTokenDto instance) =>
    <String, dynamic>{
      'platform': instance.platform,
      'registered_at': instance.registeredAt.toIso8601String(),
    };
