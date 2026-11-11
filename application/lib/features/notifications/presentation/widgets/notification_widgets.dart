import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
import 'package:flutter/material.dart';

/// "2 min ago", "1 h ago", "Just now" or the date.
String notificationAgeText(BuildContext context, DateTime now, DateTime at) {
  final l10n = context.l10n;
  return switch (notificationAge(now, at)) {
    JustNow() => l10n.notificationJustNow,
    MinutesAgo(:final minutes) => l10n.notificationMinutesAgo(minutes),
    HoursAgo(:final hours) => l10n.notificationHoursAgo(hours),
    OnDate(:final at) => AppDates.day(context, at),
  };
}

IconData _iconFor(String type) => switch (type) {
  'order_delivered' ||
  'order_partially_delivered' => Icons.receipt_long_outlined,
  'order_fulfilment_failed' => Icons.warning_amber_rounded,
  'support_ticket_replied' => Icons.help_outline,
  _ => Icons.notifications_none,
};

/// One inbox entry (16.1): unread on a raised card with the volt dot and a volt-tinted icon,
/// read as a plain row. The whole row opens the linked order or ticket.
class NotificationTile extends StatelessWidget {
  const NotificationTile({
    required this.notification,
    required this.now,
    required this.onTap,
    super.key,
  });

  final InboxNotification notification;
  final DateTime now;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final unread = !notification.isRead;
    final age = notificationAgeText(context, now, notification.createdAt);
    return Semantics(
      button: onTap != null,
      label: [
        if (unread) l10n.notificationUnread,
        notification.title,
        notification.body,
        age,
      ].join(', '),
      hint: onTap == null ? null : l10n.notificationOpenHint,
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: unread ? colors.surfaceRaised : Colors.transparent,
        borderRadius: BorderRadius.circular(Radii.md),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 64),
            child: Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s3),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: unread
                          ? colors.accentPrimaryTint
                          : colors.surfaceOverlay,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      _iconFor(notification.type),
                      size: 22,
                      color: unread
                          ? colors.accentText
                          : colors.contentSecondary,
                    ),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(notification.title, style: type.headingSm),
                        const SizedBox(height: Spacing.s0_5),
                        Text(
                          notification.body,
                          style: type.bodySm.copyWith(
                            color: colors.contentSecondary,
                          ),
                        ),
                        const SizedBox(height: Spacing.s0_5),
                        Text(
                          age,
                          style: type.bodyCaption.copyWith(
                            color: colors.contentMuted,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (unread) ...[
                    const SizedBox(width: Spacing.s2),
                    Container(
                      key: ValueKey('unread-dot-${notification.id}'),
                      width: 8,
                      height: 8,
                      margin: const EdgeInsetsDirectional.only(top: Spacing.s1),
                      decoration: BoxDecoration(
                        color: colors.accentPrimary,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
