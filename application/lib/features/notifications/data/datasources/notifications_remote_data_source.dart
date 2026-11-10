import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/notifications/data/models/notification_models.dart';
import 'package:dio/dio.dart';

/// Inbox and preferences under `/api/v1/mobile/account` (API_DOCUMENTATION.md 4c).
class NotificationsRemoteDataSource {
  NotificationsRemoteDataSource(this._dio);

  static const base = '/mobile/account';

  final Dio _dio;

  Future<ApiEnvelope<List<InboxNotificationDto>>> inbox({
    required bool unreadOnly,
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '$base/notifications',
      queryParameters: {
        'page': page,
        'per_page': perPage,
        if (unreadOnly) 'unread': 1,
      },
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, InboxNotificationDto.fromJson),
    );
  }

  Future<int> unreadCount() async {
    final response = await _dio.get<Object?>(
      '$base/notifications/unread-count',
    );
    return decodeEnvelope(
      response.data,
      (data) => asJsonObject(data)['unread_count']! as int,
    ).data;
  }

  Future<InboxNotificationDto> markRead(String id) async {
    final response = await _dio.post<Object?>(
      '$base/notifications/${Uri.encodeComponent(id)}/read',
    );
    return decodeEnvelope(
      response.data,
      (data) => InboxNotificationDto.fromJson(objectAt(data, 'notification')),
    ).data;
  }

  Future<int> markAllRead() async {
    final response = await _dio.post<Object?>('$base/notifications/read-all');
    return decodeEnvelope(
      response.data,
      (data) => asJsonObject(data)['marked_read']! as int,
    ).data;
  }

  Future<List<NotificationPreferenceDto>> preferences() async {
    final response = await _dio.get<Object?>('$base/notification-preferences');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'preferences', NotificationPreferenceDto.fromJson),
    ).data;
  }

  Future<List<NotificationPreferenceDto>> updatePreferences(
    List<Map<String, Object?>> changes,
  ) async {
    final response = await _dio.patch<Object?>(
      '$base/notification-preferences',
      data: {'preferences': changes},
    );
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'preferences', NotificationPreferenceDto.fromJson),
    ).data;
  }
}
