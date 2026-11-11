import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_link_listener.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/domain/push_routing.dart';
import 'package:application/features/push/push_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'push_controllers.g.dart';

/// State of [PushNotifications]: whether push runs in this build ([started]) and the system
/// permission.
@immutable
final class PushState {
  const PushState({
    this.started = false,
    this.permission = PushPermission.unavailable,
  });

  final bool started;
  final PushPermission permission;

  bool get granted => started && permission == PushPermission.granted;

  @override
  bool operator ==(Object other) =>
      other is PushState &&
      other.started == started &&
      other.permission == permission;

  @override
  int get hashCode => Object.hash(started, permission);
}

/// The platform name sent with the token.
@Riverpod(keepAlive: true)
String pushPlatform(Ref ref) =>
    defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android';

/// Push notifications (M-A18, owner decision 9, API_DOCUMENTATION.md 1h). Kept alive by the app.
///
/// - Starts the push service after launch. It never asks for permission on launch: the system
///   prompt follows the explainer after the first paid order ([PushPrompt]) or the "Allow" button
///   in the notification settings.
/// - Signed in with permission, registers this device's token, and again when FCM rotates it.
///   Sign-out removes it on the server first, then locally ([unregister]).
/// - A message in the foreground refreshes the unread badge and the inbox and is shown in the app
///   ([ForegroundPush]); a tapped notification (background or cold start) opens its order or
///   ticket through the app-link queue ([AppLinkListener.open]).
@Riverpod(keepAlive: true)
class PushNotifications extends _$PushNotifications {
  final _subscriptions = <StreamSubscription<Object?>>[];
  Future<void>? _starting;
  String? _registeredToken;

  @override
  PushState build() {
    ref
      ..onDispose(() {
        for (final subscription in _subscriptions) {
          unawaited(subscription.cancel());
        }
      })
      ..listen(sessionProvider, (previous, next) {
        if (next == SessionStatus.signedIn) {
          unawaited(syncToken());
        } else {
          _registeredToken = null;
        }
      });
    unawaited(Future<void>(start));
    return const PushState();
  }

  /// Starts the push service once. Safe to call again.
  Future<void> start() => _starting ??= _start();

  Future<void> _start() async {
    if (!ref.mounted) {
      return;
    }
    final messaging = ref.read(pushMessagingProvider);
    if (!await messaging.start() || !ref.mounted) {
      return;
    }
    final permission = await messaging.permission();
    if (!ref.mounted) {
      return;
    }
    state = PushState(started: true, permission: permission);
    _subscriptions
      ..add(
        messaging.tokenRefreshes.listen((token) => unawaited(_register(token))),
      )
      ..add(messaging.foregroundMessages.listen(_onForeground))
      ..add(messaging.openedMessages.listen(_onOpened));
    final initial = await messaging.initialMessage();
    if (initial != null && ref.mounted) {
      _onOpened(initial);
    }
    await syncToken();
  }

  /// Registers this device's token when signed in and allowed.
  Future<void> syncToken() async {
    if (!state.granted || ref.read(sessionProvider) != SessionStatus.signedIn) {
      return;
    }
    final token = await ref.read(pushMessagingProvider).token();
    if (token != null) {
      await _register(token);
    }
  }

  Future<void> _register(String token) async {
    if (!ref.mounted ||
        !state.granted ||
        ref.read(sessionProvider) != SessionStatus.signedIn ||
        token == _registeredToken) {
      return;
    }
    final result = await ref
        .read(pushTokenRepositoryProvider)
        .register(token: token, platform: ref.read(pushPlatformProvider));
    if (result is Ok<void> && ref.mounted) {
      _registeredToken = token;
    }
  }

  /// Shows the system prompt (after the explainer, or from the settings) and registers on yes.
  Future<PushPermission> requestPermission() async {
    await start();
    if (!ref.mounted || !state.started) {
      return PushPermission.unavailable;
    }
    final permission = await ref
        .read(pushMessagingProvider)
        .requestPermission();
    if (!ref.mounted) {
      return permission;
    }
    state = PushState(started: true, permission: permission);
    await syncToken();
    return permission;
  }

  /// Reads the system permission again (the customer may have changed it in the system settings).
  Future<void> refreshPermission() async {
    await start();
    if (!ref.mounted || !state.started) {
      return;
    }
    final permission = await ref.read(pushMessagingProvider).permission();
    if (!ref.mounted) {
      return;
    }
    state = PushState(started: true, permission: permission);
    await syncToken();
  }

  /// Sign-out: removes this device's token on the server while the sign-in still works, then
  /// forgets it on the device. Failures are ignored (the server drops it with the sign-in).
  Future<void> unregister() async {
    _registeredToken = null;
    // A sign-out right after launch may come before the deferred start: start (once) first, so
    // the state says whether this build runs push at all and a registered token is removed.
    await start();
    if (!ref.mounted || !state.started) {
      return;
    }
    if (ref.read(sessionProvider) == SessionStatus.signedIn) {
      await ref.read(pushTokenRepositoryProvider).unregister();
    }
    if (ref.mounted) {
      await ref.read(pushMessagingProvider).deleteToken();
    }
  }

  void _onForeground(PushPayload payload) {
    if (!ref.mounted) {
      return;
    }
    unawaited(ref.read(unreadNotificationsProvider.notifier).refresh());
    ref
      ..invalidate(notificationsInboxControllerProvider)
      ..read(foregroundPushProvider.notifier).show(payload);
  }

  void _onOpened(PushPayload payload) {
    if (!ref.mounted) {
      return;
    }
    unawaited(ref.read(unreadNotificationsProvider.notifier).refresh());
    final link = deepLinkForPush(payload);
    if (link != null) {
      ref.read(appLinkListenerProvider.notifier).open(link);
    }
  }
}

/// A push received in the foreground, for the in-app banner (`PushMessageHost`).
@immutable
final class ForegroundPushEvent {
  const ForegroundPushEvent(this.sequence, this.payload);

  /// Increases with every message, so two equal messages still show twice.
  final int sequence;
  final PushPayload payload;

  @override
  bool operator ==(Object other) =>
      other is ForegroundPushEvent &&
      other.sequence == sequence &&
      other.payload == payload;

  @override
  int get hashCode => Object.hash(sequence, payload);
}

@Riverpod(keepAlive: true)
class ForegroundPush extends _$ForegroundPush {
  var _sequence = 0;

  @override
  ForegroundPushEvent? build() => null;

  void show(PushPayload payload) =>
      state = ForegroundPushEvent(++_sequence, payload);
}

/// The push explainer after the first paid order (M-A18): offered once per installation, only when
/// push runs in this build and the system has not been asked yet. Never on launch.
@Riverpod(keepAlive: true)
class PushPrompt extends _$PushPrompt {
  @override
  void build() {}

  /// Whether to show the explainer now. Marks it offered when it answers yes (or when the system
  /// question was already answered), so it is never shown twice.
  Future<bool> dueAfterPurchase() async {
    final prefs = ref.read(preferencesStoreProvider);
    if (prefs.pushPromptShown) {
      return false;
    }
    final push = ref.read(pushNotificationsProvider.notifier);
    await push.start();
    if (!ref.mounted) {
      return false;
    }
    final state = ref.read(pushNotificationsProvider);
    if (!state.started) {
      return false;
    }
    await prefs.setPushPromptShown();
    return state.permission == PushPermission.notDetermined;
  }

  /// "Turn on" in the explainer: the system prompt, then the token registration.
  Future<PushPermission> accept() =>
      ref.read(pushNotificationsProvider.notifier).requestPermission();
}
