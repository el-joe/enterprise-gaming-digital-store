import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'notification_models.freezed.dart';
part 'notification_models.g.dart';

@freezed
abstract class InboxNotificationDto with _$InboxNotificationDto {
  const factory InboxNotificationDto({
    required String id,
    required String type,
    required String category,
    required String title,
    required String body,
    required DateTime createdAt,
    LinkTargetDto? link,
    DateTime? readAt,
  }) = _InboxNotificationDto;

  const InboxNotificationDto._();

  factory InboxNotificationDto.fromJson(Map<String, dynamic> json) =>
      _$InboxNotificationDtoFromJson(json);

  InboxNotification toEntity() => InboxNotification(
    id: id,
    type: type,
    category: category,
    title: title,
    body: body,
    createdAt: createdAt,
    link: link?.toEntity(),
    readAt: readAt,
  );
}

@freezed
abstract class NotificationPreferenceDto with _$NotificationPreferenceDto {
  const factory NotificationPreferenceDto({
    required String category,
    required String channel,
    required bool enabled,
    required bool mandatory,
  }) = _NotificationPreferenceDto;

  const NotificationPreferenceDto._();

  factory NotificationPreferenceDto.fromJson(Map<String, dynamic> json) =>
      _$NotificationPreferenceDtoFromJson(json);

  NotificationPreference toEntity() => NotificationPreference(
    category: category,
    channel: channel,
    enabled: enabled,
    mandatory: mandatory,
  );
}
