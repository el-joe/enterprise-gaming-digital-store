import 'dart:async';

import 'package:application/core/network/failure.dart' show NetworkFailure;
import 'package:application/core/network/result.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/domain/push_messaging.dart';
import 'package:application/features/push/domain/repositories/push_token_repository.dart';

/// The device's push service in tests (M-A18). By default push is not available in the build
/// ([available] false), as in a build without a Firebase configuration file, so tests that do not
/// arrange push never see it.
class FakePushMessaging implements PushMessaging {
  bool available = false;
  PushPermission permissionValue = PushPermission.notDetermined;

  /// What the system prompt answers.
  PushPermission answer = PushPermission.granted;
  String? tokenValue = 'fcm-token-1';
  PushPayload? launchMessage;

  var startCalls = 0;
  var permissionRequests = 0;
  var deleteTokenCalls = 0;

  final refreshes = StreamController<String>.broadcast();
  final foreground = StreamController<PushPayload>.broadcast();
  final opened = StreamController<PushPayload>.broadcast();

  @override
  Future<bool> start() async {
    startCalls++;
    return available;
  }

  @override
  Future<PushPermission> permission() async =>
      available ? permissionValue : PushPermission.unavailable;

  @override
  Future<PushPermission> requestPermission() async {
    permissionRequests++;
    if (!available) {
      return PushPermission.unavailable;
    }
    if (permissionValue == PushPermission.notDetermined) {
      permissionValue = answer;
    }
    return permissionValue;
  }

  @override
  Future<String?> token() async => available ? tokenValue : null;

  @override
  Stream<String> get tokenRefreshes => refreshes.stream;

  @override
  Stream<PushPayload> get foregroundMessages => foreground.stream;

  @override
  Stream<PushPayload> get openedMessages => opened.stream;

  @override
  Future<PushPayload?> initialMessage() async {
    final message = launchMessage;
    launchMessage = null;
    return message;
  }

  @override
  Future<void> deleteToken() async => deleteTokenCalls++;

  Future<void> close() async {
    await refreshes.close();
    await foreground.close();
    await opened.close();
  }
}

/// `POST` and `DELETE /mobile/push-token` in tests.
class FakePushTokenRepository implements PushTokenRepository {
  final registered = <({String token, String platform})>[];
  var unregisterCalls = 0;
  Result<void> registerResult = const Ok(null);

  @override
  Future<Result<void>> register({
    required String token,
    required String platform,
  }) async {
    registered.add((token: token, platform: platform));
    return registerResult;
  }

  @override
  Future<Result<void>> unregister() async {
    unregisterCalls++;
    return const Ok(null);
  }
}

/// A push as FCM delivers it for an order (`order_delivered`).
PushPayload orderPush(
  String orderPublicId, {
  String type = 'order_delivered',
}) => PushPayload(
  title: 'Order BX-1001 is ready',
  body: 'Your order has been delivered. Open it to view your codes.',
  data: {
    'type': type,
    'category': 'orders',
    'link_type': 'order',
    'link_value': orderPublicId,
    'order_id': orderPublicId,
  },
);

/// A push for a support reply.
PushPayload ticketPush(String ticketPublicId) => PushPayload(
  title: 'Update on your support request T-1001',
  body: 'Our support team has replied to your request.',
  data: {
    'type': 'support_ticket_replied',
    'category': 'support',
    'link_type': 'ticket',
    'link_value': ticketPublicId,
    'ticket_id': ticketPublicId,
  },
);

/// A registration that failed offline.
const pushRegistrationOffline = Err<void>(NetworkFailure());
