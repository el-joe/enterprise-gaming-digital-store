import 'package:flutter/foundation.dart';

/// Whether the system lets this app show notifications (Android 13+ `POST_NOTIFICATIONS`, iOS
/// authorization). [unavailable] means push is off in this build: no Firebase configuration for
/// the flavor, or the platform has no messaging service.
enum PushPermission { granted, denied, notDetermined, unavailable }

/// A push as the app reads it (M-A18, API_DOCUMENTATION.md 1h): the inbox title and body and the
/// string data the server sends (`type`, `category`, `link_type`, `link_value`, `order_id` or
/// `ticket_id`). Never a code or an amount.
@immutable
final class PushPayload {
  const PushPayload({this.title, this.body, this.data = const {}});

  final String? title;
  final String? body;
  final Map<String, String> data;

  /// The inbox kind, for example `order_delivered`.
  String? get type => data['type'];

  @override
  bool operator ==(Object other) =>
      other is PushPayload &&
      other.title == title &&
      other.body == body &&
      mapEquals(other.data, data);

  @override
  int get hashCode => Object.hash(
    title,
    body,
    Object.hashAllUnordered(
      data.entries.map((entry) => Object.hash(entry.key, entry.value)),
    ),
  );
}
