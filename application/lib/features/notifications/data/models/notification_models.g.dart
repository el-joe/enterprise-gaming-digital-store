// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'notification_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_InboxNotificationDto _$InboxNotificationDtoFromJson(
  Map<String, dynamic> json,
) => _InboxNotificationDto(
  id: json['id'] as String,
  type: json['type'] as String,
  category: json['category'] as String,
  title: json['title'] as String,
  body: json['body'] as String,
  createdAt: DateTime.parse(json['created_at'] as String),
  link: json['link'] == null
      ? null
      : LinkTargetDto.fromJson(json['link'] as Map<String, dynamic>),
  readAt: json['read_at'] == null
      ? null
      : DateTime.parse(json['read_at'] as String),
);

Map<String, dynamic> _$InboxNotificationDtoToJson(
  _InboxNotificationDto instance,
) => <String, dynamic>{
  'id': instance.id,
  'type': instance.type,
  'category': instance.category,
  'title': instance.title,
  'body': instance.body,
  'created_at': instance.createdAt.toIso8601String(),
  'link': instance.link?.toJson(),
  'read_at': instance.readAt?.toIso8601String(),
};

_NotificationPreferenceDto _$NotificationPreferenceDtoFromJson(
  Map<String, dynamic> json,
) => _NotificationPreferenceDto(
  category: json['category'] as String,
  channel: json['channel'] as String,
  enabled: json['enabled'] as bool,
  mandatory: json['mandatory'] as bool,
);

Map<String, dynamic> _$NotificationPreferenceDtoToJson(
  _NotificationPreferenceDto instance,
) => <String, dynamic>{
  'category': instance.category,
  'channel': instance.channel,
  'enabled': instance.enabled,
  'mandatory': instance.mandatory,
};
