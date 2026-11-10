import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'notifications.freezed.dart';

/// An inbox entry (API_DOCUMENTATION.md 4c). Identifiers and text only, never a code.
@freezed
abstract class InboxNotification with _$InboxNotification {
  const factory InboxNotification({
    /// UUID.
    required String id,

    /// `order_delivered`, `order_partially_delivered`, `order_fulfilment_failed`,
    /// `support_ticket_replied`.
    required String type,
    required String category,
    required String title,
    required String body,
    required DateTime createdAt,
    LinkTarget? link,
    DateTime? readAt,
  }) = _InboxNotification;

  const InboxNotification._();

  bool get isRead => readAt != null;
}

@freezed
abstract class InboxPage with _$InboxPage {
  const factory InboxPage({
    required List<InboxNotification> notifications,
    required PageInfo page,
    required int unreadCount,
  }) = _InboxPage;
}

/// One cell of the 4 x 3 matrix (categories `orders`, `support`, `marketing`, `security` x
/// channels `mail`, `database`, `sms`). [mandatory] cells cannot be switched off.
@freezed
abstract class NotificationPreference with _$NotificationPreference {
  const factory NotificationPreference({
    required String category,
    required String channel,
    required bool enabled,
    required bool mandatory,
  }) = _NotificationPreference;
}

/// A change sent to `PATCH /notification-preferences`.
@freezed
abstract class PreferenceChange with _$PreferenceChange {
  const factory PreferenceChange({
    required String category,
    required String channel,
    required bool enabled,
  }) = _PreferenceChange;
}
