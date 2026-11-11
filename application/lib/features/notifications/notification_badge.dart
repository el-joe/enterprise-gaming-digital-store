import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/features/notifications/notifications_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'notification_badge.g.dart';

/// The notifications feature's public entry for the home header and the Account hub: the
/// number of unread inbox notifications (`GET /notifications/unread-count`), read when the
/// customer is signed in and kept current by the inbox (its `meta.unread_count`, mark read and
/// mark all read). Signed out it is 0. A failed read keeps the last count. Push (M-A18) is not
/// part of it: new notifications show after the next read.
@Riverpod(keepAlive: true)
class UnreadNotifications extends _$UnreadNotifications {
  int _generation = 0;

  @override
  int build() {
    if (ref.watch(sessionProvider) != SessionStatus.signedIn) {
      return 0;
    }
    unawaited(refresh());
    return 0;
  }

  /// Reads the count again (home pull to refresh, the inbox).
  Future<void> refresh() async {
    if (ref.read(sessionProvider) != SessionStatus.signedIn) {
      return;
    }
    final generation = ++_generation;
    final result = await ref
        .read(notificationsRepositoryProvider)
        .unreadCount();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    if (result case Ok(:final value)) {
      state = value < 0 ? 0 : value;
    }
  }

  /// The count the server just sent with a list or an action.
  void set(int count) {
    _generation++;
    state = count < 0 ? 0 : count;
  }

  /// One notification was marked read.
  void markedOne() => set(state - 1);
}
