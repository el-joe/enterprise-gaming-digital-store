import 'package:application/core/network/result.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';

abstract interface class NotificationsRepository {
  Future<Result<InboxPage>> inbox({
    bool unreadOnly = false,
    int page = 1,
    int perPage = 20,
  });

  Future<Result<int>> unreadCount();

  /// Idempotent; 404 for another customer's notification.
  Future<Result<InboxNotification>> markRead(String id);

  /// Returns the number of notifications marked read.
  Future<Result<int>> markAllRead();

  Future<Result<List<NotificationPreference>>> preferences();

  /// Returns the full matrix. 422 `notification_preference_mandatory` saves nothing.
  Future<Result<List<NotificationPreference>>> updatePreferences(
    List<PreferenceChange> changes,
  );
}
