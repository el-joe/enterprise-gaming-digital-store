import 'package:application/core/network/result.dart';
import 'package:application/features/notifications/data/datasources/notifications_remote_data_source.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';

class NotificationsRepositoryImpl implements NotificationsRepository {
  NotificationsRepositoryImpl(this._remote);

  final NotificationsRemoteDataSource _remote;

  @override
  Future<Result<InboxPage>> inbox({
    bool unreadOnly = false,
    int page = 1,
    int perPage = 20,
  }) => guardRequest(() async {
    final envelope = await _remote.inbox(
      unreadOnly: unreadOnly,
      page: page,
      perPage: perPage,
    );
    final unread = envelope.meta['unread_count'];
    if (unread is! int) {
      throw const FormatException('meta.unread_count is missing.');
    }
    return InboxPage(
      notifications: [for (final dto in envelope.data) dto.toEntity()],
      page: pageInfoOf(envelope),
      unreadCount: unread,
    );
  });

  @override
  Future<Result<int>> unreadCount() => guardRequest(_remote.unreadCount);

  @override
  Future<Result<InboxNotification>> markRead(String id) =>
      guardRequest(() async => (await _remote.markRead(id)).toEntity());

  @override
  Future<Result<int>> markAllRead() => guardRequest(_remote.markAllRead);

  @override
  Future<Result<List<NotificationPreference>>> preferences() => guardRequest(
    () async => [for (final dto in await _remote.preferences()) dto.toEntity()],
  );

  @override
  Future<Result<List<NotificationPreference>>> updatePreferences(
    List<PreferenceChange> changes,
  ) => guardRequest(
    () async => [
      for (final dto in await _remote.updatePreferences([
        for (final change in changes)
          {
            'category': change.category,
            'channel': change.channel,
            'enabled': change.enabled,
          },
      ]))
        dto.toEntity(),
    ],
  );
}
