import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/state/paged_list.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/notifications/notifications_providers.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'notifications_controllers.g.dart';

/// The inbox (16.1, 16.2): `GET /notifications`, newest first, 20 per page; every page's
/// `meta.unread_count` updates the bell badge ([UnreadNotifications]). Opening an unread
/// notification marks it read (`POST /notifications/{id}/read`, idempotent).
@riverpod
class NotificationsInboxController extends _$NotificationsInboxController {
  late final PagedLoader<InboxNotification> _loader = PagedLoader(
    fetch: _fetch,
    read: () => state,
    write: (value) => state = value,
    mounted: () => ref.mounted,
  );

  Future<Result<Paged<InboxNotification>>> _fetch(int page) async {
    final result = await ref
        .read(notificationsRepositoryProvider)
        .inbox(page: page);
    switch (result) {
      case Ok(:final value):
        if (ref.mounted) {
          ref.read(unreadNotificationsProvider.notifier).set(value.unreadCount);
        }
        return Ok(Paged(items: value.notifications, page: value.page));
      case Err(:final failure):
        return Err(failure);
    }
  }

  @override
  PagedListState<InboxNotification> build() {
    if (ref.watch(sessionProvider) != SessionStatus.signedIn) {
      return const PagedListState(content: Loadable.loaded([]));
    }
    unawaited(_loader.loadFirst());
    return const PagedListState();
  }

  Future<void> retry() => _loader.retry();

  Future<void> refresh() => _loader.refresh();

  Future<void> loadMore() => _loader.loadMore();

  /// Opens [notification]: marks it read when it is unread (the badge goes down once the server
  /// agreed) and returns where its link leads (`order` detail, `ticket` thread), or null for a
  /// notification without a usable link. The page navigates without waiting for the read.
  LinkDestination? open(InboxNotification notification) {
    if (!notification.isRead) {
      unawaited(_markRead(notification.id));
    }
    final link = notification.link;
    return link == null ? null : destinationFor(link);
  }

  Future<void> _markRead(String id) async {
    final result = await ref.read(notificationsRepositoryProvider).markRead(id);
    if (!ref.mounted) {
      return;
    }
    if (result case Ok(:final value)) {
      final items = [...state.items];
      final index = items.indexWhere((item) => item.id == id && !item.isRead);
      if (index < 0) {
        return;
      }
      items[index] = items[index].copyWith(
        readAt: value.readAt ?? ref.read(clockProvider)(),
      );
      state = state.withItems(items);
      ref.read(unreadNotificationsProvider.notifier).markedOne();
    }
  }

  /// Every loaded notification shows as read (after "Mark all read").
  void markedAllRead() {
    final now = ref.read(clockProvider)();
    state = state.withItems([
      for (final item in state.items)
        item.isRead ? item : item.copyWith(readAt: now),
    ]);
  }
}

/// "Mark all read" (16.1): `POST /notifications/read-all`. True while it runs; a failure is
/// returned to the page for a snackbar.
@riverpod
class MarkAllRead extends _$MarkAllRead {
  @override
  bool build() => false;

  Future<Failure?> run() async {
    if (state) {
      return null;
    }
    state = true;
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(notificationsRepositoryProvider)
          .markAllRead();
      switch (result) {
        case Ok():
          ref.read(unreadNotificationsProvider.notifier).set(0);
          ref
              .read(notificationsInboxControllerProvider.notifier)
              .markedAllRead();
          return null;
        case Err(:final failure):
          return failure;
      }
    } finally {
      if (ref.mounted) {
        state = false;
      }
      link.close();
    }
  }
}

/// Order of the preference sections and rows (the API's categories and channels).
const preferenceCategories = ['orders', 'support', 'marketing', 'security'];

/// `push` (M-A18) exists for orders and support only; a category without a cell skips it.
const preferenceChannels = ['database', 'push', 'mail', 'sms'];

@immutable
final class PreferencesState {
  const PreferencesState({
    this.preferences = const Loadable.loading(),
    this.saving = const {},
  });

  /// The 4 x 3 matrix.
  final Loadable<List<NotificationPreference>> preferences;

  /// Cells being saved (`category.channel`).
  final Set<String> saving;

  NotificationPreference? cell(String category, String channel) {
    for (final preference
        in preferences.valueOrNull ?? const <NotificationPreference>[]) {
      if (preference.category == category && preference.channel == channel) {
        return preference;
      }
    }
    return null;
  }

  PreferencesState copyWith({
    Loadable<List<NotificationPreference>>? preferences,
    Set<String>? saving,
  }) => PreferencesState(
    preferences: preferences ?? this.preferences,
    saving: saving ?? this.saving,
  );
}

/// Notification preferences (16.3): `GET /notification-preferences`, and every switch is saved
/// at once (`PATCH` with the one cell; the server answers with the full matrix). Mandatory
/// cells (`orders.mail`, `security.mail`) are on and cannot be switched off; a refused save
/// (422 `notification_preference_mandatory` or any failure) puts the switch back.
@riverpod
class NotificationPreferencesController
    extends _$NotificationPreferencesController {
  @override
  PreferencesState build() {
    unawaited(_load());
    return const PreferencesState();
  }

  Future<void> _load() async {
    final result = await ref
        .read(notificationsRepositoryProvider)
        .preferences();
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      preferences: switch (result) {
        Ok(:final value) => Loadable.loaded(value),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  Future<void> retry() async {
    state = const PreferencesState();
    await _load();
  }

  List<NotificationPreference> _with(
    String category,
    String channel,
    bool enabled,
  ) => [
    for (final p
        in state.preferences.valueOrNull ?? const <NotificationPreference>[])
      p.category == category && p.channel == channel
          ? p.copyWith(enabled: enabled)
          : p,
  ];

  /// Switches one cell. Returns the failure of a refused save (the switch is back as it was).
  Future<Failure?> toggle(String category, String channel, bool enabled) async {
    final cell = state.cell(category, channel);
    final key = '$category.$channel';
    if (cell == null ||
        cell.enabled == enabled ||
        (cell.mandatory && !enabled) ||
        state.saving.contains(key)) {
      return null;
    }
    state = state.copyWith(
      preferences: Loadable.loaded(_with(category, channel, enabled)),
      saving: {...state.saving, key},
    );
    final result = await ref
        .read(notificationsRepositoryProvider)
        .updatePreferences([
          PreferenceChange(
            category: category,
            channel: channel,
            enabled: enabled,
          ),
        ]);
    if (!ref.mounted) {
      return result.failureOrNull;
    }
    final saving = {...state.saving}..remove(key);
    switch (result) {
      case Ok(:final value):
        // Other cells may be saving: keep their pending values.
        final pending = {
          for (final p
              in state.preferences.valueOrNull ??
                  const <NotificationPreference>[])
            if (saving.contains('${p.category}.${p.channel}'))
              '${p.category}.${p.channel}': p.enabled,
        };
        state = state.copyWith(
          preferences: Loadable.loaded([
            for (final p in value)
              pending.containsKey('${p.category}.${p.channel}')
                  ? p.copyWith(enabled: pending['${p.category}.${p.channel}']!)
                  : p,
          ]),
          saving: saving,
        );
        return null;
      case Err(:final failure):
        state = state.copyWith(
          preferences: Loadable.loaded(_with(category, channel, cell.enabled)),
          saving: saving,
        );
        return failure;
    }
  }
}

/// How long ago a notification arrived, for "Just now", "5 min ago", "2 h ago" or the date.
sealed class NotificationAge {
  const NotificationAge();
}

final class JustNow extends NotificationAge {
  const JustNow();
}

final class MinutesAgo extends NotificationAge {
  const MinutesAgo(this.minutes);

  final int minutes;
}

final class HoursAgo extends NotificationAge {
  const HoursAgo(this.hours);

  final int hours;
}

/// A day old or older: the date is shown.
final class OnDate extends NotificationAge {
  const OnDate(this.at);

  final DateTime at;
}

NotificationAge notificationAge(DateTime now, DateTime at) {
  final elapsed = now.difference(at);
  if (elapsed >= const Duration(days: 1)) {
    return OnDate(at);
  }
  // A time slightly ahead of the device clock is "just now".
  if (elapsed < const Duration(minutes: 1)) {
    return const JustNow();
  }
  if (elapsed < const Duration(hours: 1)) {
    return MinutesAgo(elapsed.inMinutes);
  }
  return HoursAgo(elapsed.inHours);
}
