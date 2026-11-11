import 'dart:async';

import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/domain/push_messaging.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

/// [PushMessaging] on `firebase_core` and `firebase_messaging` (README: package choice).
///
/// Firebase reads the flavor's `google-services.json` (Android, applied by the Gradle plugin only
/// when the file exists) or `GoogleService-Info.plist` (iOS). Without them `Firebase.initializeApp`
/// fails, [start] returns false and the app runs with push off. No options are compiled in, so no
/// Firebase identifier lives in the repository.
///
/// Tokens: the server sends to FCM registration tokens (HTTP v1 `message.token`, ADR-042), so this
/// uses `getToken`, `onTokenRefresh` and `deleteToken`. firebase_messaging 17 deprecates them in
/// favour of the new Firebase Installation ID registration (`register`, `onRegistered`), whose
/// server-side send target is not part of the stable HTTP v1 API the backend uses; moving to it is
/// an open item (README, M-A18).
final class FirebasePushMessaging implements PushMessaging {
  bool _started = false;

  FirebaseMessaging get _messaging => FirebaseMessaging.instance;

  @override
  Future<bool> start() async {
    if (_started) {
      return true;
    }
    if (kIsWeb ||
        (defaultTargetPlatform != TargetPlatform.android &&
            defaultTargetPlatform != TargetPlatform.iOS)) {
      return false;
    }
    try {
      if (Firebase.apps.isEmpty) {
        await Firebase.initializeApp();
      }
      if (!await _messaging.isSupported()) {
        return false;
      }
      _started = true;
      return true;
    } on Object catch (error) {
      if (kDebugMode) {
        debugPrint('Push is off: Firebase did not start ($error).');
      }
      return false;
    }
  }

  @override
  Future<PushPermission> permission() async {
    if (!_started) {
      return PushPermission.unavailable;
    }
    try {
      return _map(
        (await _messaging.getNotificationSettings()).authorizationStatus,
      );
    } on Object {
      return PushPermission.unavailable;
    }
  }

  @override
  Future<PushPermission> requestPermission() async {
    if (!_started) {
      return PushPermission.unavailable;
    }
    try {
      return _map((await _messaging.requestPermission()).authorizationStatus);
    } on Object {
      return PushPermission.unavailable;
    }
  }

  @override
  Future<String?> token() async {
    if (!_started) {
      return null;
    }
    try {
      // ignore: deprecated_member_use, registration tokens are the server's send target (above).
      return await _messaging.getToken();
    } on Object {
      return null;
    }
  }

  @override
  Stream<String> get tokenRefreshes => _started
      // ignore: deprecated_member_use, registration tokens are the server's send target (above).
      ? _messaging.onTokenRefresh
      : const Stream.empty();

  @override
  Stream<PushPayload> get foregroundMessages => _started
      ? FirebaseMessaging.onMessage.map(_payload)
      : const Stream.empty();

  @override
  Stream<PushPayload> get openedMessages => _started
      ? FirebaseMessaging.onMessageOpenedApp.map(_payload)
      : const Stream.empty();

  @override
  Future<PushPayload?> initialMessage() async {
    if (!_started) {
      return null;
    }
    try {
      final message = await _messaging.getInitialMessage();
      return message == null ? null : _payload(message);
    } on Object {
      return null;
    }
  }

  @override
  Future<void> deleteToken() async {
    if (!_started) {
      return;
    }
    try {
      // ignore: deprecated_member_use, registration tokens are the server's send target (above).
      await _messaging.deleteToken();
    } on Object {
      // Best effort: the server drops the token with the sign-in anyway.
    }
  }

  static PushPayload _payload(RemoteMessage message) => PushPayload(
    title: message.notification?.title,
    body: message.notification?.body,
    data: {
      for (final entry in message.data.entries)
        if (entry.value != null) entry.key: entry.value.toString(),
    },
  );

  static PushPermission _map(AuthorizationStatus status) => switch (status) {
    AuthorizationStatus.authorized ||
    AuthorizationStatus.provisional => PushPermission.granted,
    AuthorizationStatus.denied ||
    AuthorizationStatus.deniedPermanently => PushPermission.denied,
    AuthorizationStatus.notDetermined => PushPermission.notDetermined,
  };
}
