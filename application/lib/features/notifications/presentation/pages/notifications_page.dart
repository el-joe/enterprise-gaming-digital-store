import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/link_navigation.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
import 'package:application/features/notifications/presentation/widgets/notification_widgets.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/paged_list_view.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Notifications (16.1, 16.2): the inbox, newest first and paged, with the unread count, "Mark
/// all read" and the settings. A notification opens its order or ticket through the shared
/// link targets (`LinkTargetResolver` shape) and is marked read.
class NotificationsPage extends ConsumerWidget {
  const NotificationsPage({super.key});

  Future<void> _markAll(BuildContext context, WidgetRef ref) async {
    final l10n = context.l10n;
    final failure = await ref.read(markAllReadProvider.notifier).run();
    if (failure != null && context.mounted) {
      showAppSnackbar(
        context,
        message: failureDetail(l10n, failure),
        tone: SnackbarTone.danger,
      );
    }
  }

  void _open(BuildContext context, WidgetRef ref, InboxNotification item) {
    final destination = ref
        .read(notificationsInboxControllerProvider.notifier)
        .open(item);
    switch (destination) {
      case InAppDestination(:final location):
        openLocation(context, location);
      case ExternalDestination() || null:
        // Inbox links are in-app only (order, ticket); a link the app does not know stays put.
        break;
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final state = ref.watch(notificationsInboxControllerProvider);
    final controller = ref.read(notificationsInboxControllerProvider.notifier);
    final unread = ref.watch(unreadNotificationsProvider);
    final markingAll = ref.watch(markAllReadProvider);
    final now = ref.watch(clockProvider)();
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.accountNotifications,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.home.path),
        actions: [
          SizedBox.square(
            dimension: 48,
            child: IconButton(
              key: const ValueKey('notifications-settings'),
              tooltip: l10n.notificationSettingsTitle,
              icon: const Icon(Icons.settings_outlined),
              onPressed: () =>
                  unawaited(context.push(Routes.notificationSettings.path)),
            ),
          ),
        ],
      ),
      body: SafeArea(
        top: false,
        child: PagedListView<InboxNotification>(
          state: state,
          skeletonHeight: 80,
          header: [
            if (state.content is Loaded)
              // The count at the start, "Mark all as read" at the end; with large text the
              // button wraps under the count instead of overflowing.
              Wrap(
                alignment: WrapAlignment.spaceBetween,
                crossAxisAlignment: WrapCrossAlignment.center,
                spacing: Spacing.s2,
                children: [
                  Semantics(
                    liveRegion: true,
                    child: Text(
                      unread > 0
                          ? l10n.notificationsUnreadCount(unread)
                          : l10n.notificationsAllRead,
                      style: context.type.bodySm.copyWith(
                        color: colors.contentSecondary,
                      ),
                    ),
                  ),
                  TextButton(
                    key: const ValueKey('notifications-mark-all'),
                    style: TextButton.styleFrom(
                      minimumSize: const Size(48, 48),
                      foregroundColor: colors.accentText,
                    ),
                    onPressed: unread > 0 && !markingAll
                        ? () => unawaited(_markAll(context, ref))
                        : null,
                    child: Text(l10n.notificationsMarkAllRead),
                  ),
                ],
              ),
            const SizedBox(height: Spacing.s1),
          ],
          errorTitle: l10n.notificationsLoadError,
          moreFailedTitle: l10n.listMoreFailed,
          onRetry: () => unawaited(controller.retry()),
          onRefresh: controller.refresh,
          onLoadMore: () => unawaited(controller.loadMore()),
          empty: EmptyState(
            icon: Icons.notifications_paused_outlined,
            title: l10n.notificationsEmptyTitle,
            body: l10n.notificationsEmptyBody,
          ),
          itemBuilder: (context, item) => NotificationTile(
            key: ValueKey('notification-${item.id}'),
            notification: item,
            now: now,
            onTap: () => _open(context, ref, item),
          ),
        ),
      ),
    );
  }
}
